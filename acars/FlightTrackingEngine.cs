namespace Promethee;

public enum FlightPhase
{
    Reserved,
    Preparation,
    Boarding,
    AcarsReady,
    Pushback,
    TaxiOut,
    Takeoff,
    Climb,
    Cruise,
    Descent,
    Approach,
    Final,
    Landing,
    TaxiIn,
    In,
    Completed
}

public sealed record FlightEvent(string Type, DateTimeOffset OccurredAt, AircraftSnapshot Snapshot, double? Value = null);
public sealed record TrackingDecision(FlightPhase Phase, IReadOnlyList<FlightEvent> Events);

/// <summary>
/// Deterministic, simulator-independent source of truth for flight phases and
/// observed operational facts. It never applies company penalties.
/// </summary>
public sealed class FlightTrackingEngine
{
    private static readonly TimeSpan TouchdownConfirmation = TimeSpan.FromSeconds(8);
    private static readonly TimeSpan BlockOnConfirmation = TimeSpan.FromSeconds(15);
    private static readonly TimeSpan TouchAndGoConfirmation = TimeSpan.FromSeconds(8);

    private AircraftSnapshot? previous;
    private DateTimeOffset? parkedSince;
    private AircraftSnapshot? touchdownCandidate;
    private DateTimeOffset? touchdownCandidateSince;
    private DateTimeOffset? airborneAfterTouchdownSince;
    private int bounceCount;
    private bool landingConfirmed;

    public FlightPhase Phase { get; private set; } = FlightPhase.Reserved;

    public void Arm(FlightPhase initialPhase = FlightPhase.AcarsReady)
    {
        ResetTransientState();
        Phase = initialPhase;
    }

    /// <summary>
    /// Restores a persisted phase after an Hermès restart without pretending a
    /// new flight started. The first fresh snapshot becomes the new baseline.
    /// </summary>
    public void Restore(FlightPhase phase, bool touchdownAlreadyConfirmed = false)
    {
        ResetTransientState();
        Phase = phase;
        landingConfirmed = touchdownAlreadyConfirmed || phase is FlightPhase.TaxiIn or FlightPhase.In or FlightPhase.Completed;
    }

    public TrackingDecision Process(AircraftSnapshot current)
    {
        var events = new List<FlightEvent>();

        if (previous is null) {
            previous = current;
            if (Phase == FlightPhase.Landing && current.OnGround == true && !landingConfirmed) {
                touchdownCandidate = current;
                touchdownCandidateSince = current.RecordedAt;
            }
            return new(Phase, events);
        }

        DetectSystemChanges(previous, current, events);

        DetectDepartureFromStand(previous, current, events);
        DetectTakeoffOrBounce(previous, current, events);
        DetectAirbornePhases(previous, current, events);
        DetectTouchdown(previous, current, events);
        ConfirmTouchdownIfStable(current, events);
        DetectTaxiIn(current, events);
        DetectBlockOn(current, events);

        previous = current;
        return new(Phase, events);
    }

    public static string ToExternalPhase(FlightPhase phase) => phase switch {
        FlightPhase.Reserved => "RESERVED",
        FlightPhase.Preparation => "PREPARATION",
        FlightPhase.Boarding => "BOARDING",
        FlightPhase.AcarsReady => "ACARS_READY",
        FlightPhase.Pushback => "PUSHBACK",
        FlightPhase.TaxiOut => "TAXI_OUT",
        FlightPhase.Takeoff => "TAKEOFF",
        FlightPhase.Climb => "CLIMB",
        FlightPhase.Cruise => "CRUISE",
        FlightPhase.Descent => "DESCENT",
        FlightPhase.Approach => "APPROACH",
        FlightPhase.Final => "FINAL",
        FlightPhase.Landing => "LANDING",
        FlightPhase.TaxiIn => "TAXI_IN",
        FlightPhase.In => "IN",
        FlightPhase.Completed => "COMPLETED",
        _ => phase.ToString().ToUpperInvariant(),
    };

    /// <summary>Accepts both the new phase names and legacy persisted states.</summary>
    public static FlightPhase ParsePhase(string? phase) => (phase ?? "").Trim().ToUpperInvariant() switch {
        "RESERVED" => FlightPhase.Reserved,
        "PREPARATION" => FlightPhase.Preparation,
        "BOARDING" => FlightPhase.Boarding,
        "ACARS_READY" => FlightPhase.AcarsReady,
        "OUT" or "BLOCK_OFF" or "PUSHBACK" => FlightPhase.Pushback,
        "TAXI_OUT" or "TAXI OUT" => FlightPhase.TaxiOut,
        "TAKEOFF" => FlightPhase.Takeoff,
        "CLIMB" => FlightPhase.Climb,
        "CRUISE" or "ENROUTE" => FlightPhase.Cruise,
        "DESCENT" => FlightPhase.Descent,
        "APPROACH" => FlightPhase.Approach,
        "FINAL" => FlightPhase.Final,
        "LANDING" => FlightPhase.Landing,
        "TAXI_IN" or "TAXI IN" => FlightPhase.TaxiIn,
        "IN" or "BLOCK_ON" => FlightPhase.In,
        "COMPLETED" => FlightPhase.Completed,
        _ => FlightPhase.AcarsReady,
    };

    private void DetectDepartureFromStand(AircraftSnapshot before, AircraftSnapshot current, List<FlightEvent> events)
    {
        if (current.OnGround != true) return;
        var gs = current.GroundSpeedKnots ?? 0;

        if (Phase is FlightPhase.Boarding or FlightPhase.AcarsReady
            && current.ParkingBrake == false
            && gs > .5)
        {
            events.Add(new("OUT", current.RecordedAt, current));
            Transition(FlightPhase.Pushback, "PUSHBACK", current, events);
        }

        if (Phase is FlightPhase.Boarding or FlightPhase.AcarsReady or FlightPhase.Pushback
            && gs >= 5)
        {
            if (Phase is FlightPhase.Boarding or FlightPhase.AcarsReady)
                events.Add(new("OUT", current.RecordedAt, current));
            Transition(FlightPhase.TaxiOut, "TAXI_OUT", current, events);
        }

        // Distinguish an actual takeoff roll from ordinary taxi before the
        // aircraft becomes airborne. This prevents runway acceleration from
        // being misclassified as a taxi-speed exceedance.
        if (Phase == FlightPhase.TaxiOut
            && before.OnGround == true
            && before.GroundSpeedKnots is { } previousGs)
        {
            var dt = (current.RecordedAt - before.RecordedAt).TotalSeconds;
            var acceleration = dt is > 0 and <= 10 ? (gs - previousGs) / dt : 0;
            if (gs >= 30 && acceleration >= 1.5)
                Transition(FlightPhase.Takeoff, "TAKEOFF", current, events);
        }
    }

    private void DetectTakeoffOrBounce(AircraftSnapshot before, AircraftSnapshot current, List<FlightEvent> events)
    {
        if (before.OnGround != true || current.OnGround != false) return;

        if (touchdownCandidate is not null || Phase == FlightPhase.Landing) {
            bounceCount++;
            airborneAfterTouchdownSince = current.RecordedAt;
            landingConfirmed = false;
            Phase = FlightPhase.Landing;
            events.Add(new("BOUNCE", current.RecordedAt, current, bounceCount));
            return;
        }

        if (Phase == FlightPhase.Takeoff) {
            events.Add(new("OFF", current.RecordedAt, current));
            return;
        }

        if (Phase is FlightPhase.TaxiOut or FlightPhase.Pushback or FlightPhase.Boarding or FlightPhase.AcarsReady) {
            events.Add(new("OFF", current.RecordedAt, current));
            Transition(FlightPhase.Takeoff, "TAKEOFF", current, events);
        }
    }

    private void DetectAirbornePhases(AircraftSnapshot before, AircraftSnapshot current, List<FlightEvent> events)
    {
        if (current.OnGround != false) return;

        var agl = current.AltitudeAglFeet ?? 0;
        var vs = current.VerticalSpeedFeetPerMinute ?? 0;

        // A missed approach without runway contact.
        if (Phase is FlightPhase.Approach or FlightPhase.Final
            && vs >= 500
            && (before.VerticalSpeedFeetPerMinute ?? 0) <= 100
            && agl >= 200)
        {
            events.Add(new("GO_AROUND", current.RecordedAt, current));
            Transition(FlightPhase.Climb, "CLIMB", current, events);
            return;
        }

        // A runway contact followed by sustained flight is a touch-and-go,
        // not a completed landing. No ON event is produced.
        if (Phase == FlightPhase.Landing && touchdownCandidate is not null && airborneAfterTouchdownSince is not null
            && (agl >= 200
                || (current.RecordedAt - airborneAfterTouchdownSince >= TouchAndGoConfirmation && vs > 300)))
        {
            var touches = bounceCount;
            events.Add(new("TOUCH_AND_GO", current.RecordedAt, current, touches));
            touchdownCandidate = null;
            touchdownCandidateSince = null;
            airborneAfterTouchdownSince = null;
            bounceCount = 0;
            landingConfirmed = false;
            Transition(FlightPhase.Climb, "CLIMB", current, events);
            return;
        }

        if (Phase == FlightPhase.Takeoff && (agl >= 500 || (current.GearDown == false && agl >= 100)))
            Transition(FlightPhase.Climb, "CLIMB", current, events);

        if (Phase == FlightPhase.Climb && agl > 10000 && Math.Abs(vs) < 300)
            Transition(FlightPhase.Cruise, "CRUISE", current, events);

        if (Phase is FlightPhase.Climb or FlightPhase.Cruise && vs < -300)
            Transition(FlightPhase.Descent, "DESCENT", current, events);

        if (Phase == FlightPhase.Descent && agl < 10000)
            Transition(FlightPhase.Approach, "APPROACH", current, events);

        if (Phase == FlightPhase.Approach && agl < 3000
            && current.GearDown == true
            && (current.FlapsPercent ?? 0) > 0)
            Transition(FlightPhase.Final, "FINAL", current, events);
    }

    private void DetectTouchdown(AircraftSnapshot before, AircraftSnapshot current, List<FlightEvent> events)
    {
        if (before.OnGround != false || current.OnGround != true) return;
        if (Phase is FlightPhase.Boarding or FlightPhase.AcarsReady or FlightPhase.Pushback or FlightPhase.TaxiOut)
            return;

        var observedRate = current.TouchdownVerticalSpeedFeetPerMinute
            ?? current.VerticalSpeedFeetPerMinute
            ?? before.VerticalSpeedFeetPerMinute;
        var rate = observedRate is null ? null : -Math.Abs(observedRate.Value);

        Phase = FlightPhase.Landing;
        events.Add(new("LANDING", current.RecordedAt, current, rate));
        touchdownCandidate = current;
        touchdownCandidateSince = current.RecordedAt;
        airborneAfterTouchdownSince = null;
        landingConfirmed = false;
        events.Add(new(bounceCount == 0 ? "TOUCHDOWN_FIRST" : "TOUCHDOWN_BOUNCE", current.RecordedAt, current, rate));
    }

    private void ConfirmTouchdownIfStable(AircraftSnapshot current, List<FlightEvent> events)
    {
        if (landingConfirmed || touchdownCandidate is null || touchdownCandidateSince is null || current.OnGround != true)
            return;
        if (current.RecordedAt - touchdownCandidateSince < TouchdownConfirmation)
            return;

        var rate = touchdownCandidate.TouchdownVerticalSpeedFeetPerMinute
            ?? touchdownCandidate.VerticalSpeedFeetPerMinute;

        events.Add(new("TOUCHDOWN", touchdownCandidate.RecordedAt, touchdownCandidate, rate));
        events.Add(new("ON", touchdownCandidate.RecordedAt, touchdownCandidate, rate));
        if (bounceCount > 0)
            events.Add(new("BOUNCE_COUNT", current.RecordedAt, current, bounceCount));

        landingConfirmed = true;
        touchdownCandidate = null;
        touchdownCandidateSince = null;
        airborneAfterTouchdownSince = null;
        bounceCount = 0;
    }

    private void DetectTaxiIn(AircraftSnapshot current, List<FlightEvent> events)
    {
        if (Phase == FlightPhase.Landing
            && landingConfirmed
            && current.OnGround == true
            && (current.GroundSpeedKnots ?? 0) < 30)
            Transition(FlightPhase.TaxiIn, "TAXI_IN", current, events);
    }

    private void DetectBlockOn(AircraftSnapshot current, List<FlightEvent> events)
    {
        if (Phase == FlightPhase.TaxiIn
            && current.OnGround == true
            && current.ParkingBrake == true
            && (current.GroundSpeedKnots ?? 0) < 2)
        {
            parkedSince ??= current.RecordedAt;
            if (current.RecordedAt - parkedSince >= BlockOnConfirmation)
                Transition(FlightPhase.In, "IN", current, events);
        }
        else if (Phase != FlightPhase.In)
        {
            parkedSince = null;
        }
    }

    private static void DetectSystemChanges(AircraftSnapshot before, AircraftSnapshot after, List<FlightEvent> events)
    {
        Change(before.BeaconLight, after.BeaconLight, "BEACON", after, events);
        Change(before.ParkingBrake, after.ParkingBrake, "PARKING_BRAKE", after, events);
        Change(before.GearDown, after.GearDown, "GEAR", after, events);
        Change(before.LandingLight, after.LandingLight, "LANDING_LIGHTS", after, events);
        Change(before.NavigationLight, after.NavigationLight, "NAV_LIGHTS", after, events);
        Change(before.StrobeLight, after.StrobeLight, "STROBE_LIGHTS", after, events);

        if (before.SlewActive == false && after.SlewActive == true)
            events.Add(new("SLEW_ACTIVE", after.RecordedAt, after));
        if (before.SimulationRate is not null && after.SimulationRate is > 1 && after.SimulationRate > before.SimulationRate)
            events.Add(new("SIM_RATE_INCREASED", after.RecordedAt, after, after.SimulationRate));
        if (before.FuelWeight is not null && after.FuelWeight is not null && after.FuelWeight > before.FuelWeight + 250)
            events.Add(new("FUEL_INCREASED", after.RecordedAt, after, after.FuelWeight - before.FuelWeight));

        if (before.EnginesRunning is not null && after.EnginesRunning is not null)
            for (var i = 0; i < Math.Min(before.EnginesRunning.Count, after.EnginesRunning.Count); i++)
                if (before.EnginesRunning[i] != after.EnginesRunning[i])
                    events.Add(new(after.EnginesRunning[i] ? "ENGINE_STARTED" : "ENGINE_STOPPED", after.RecordedAt, after, i + 1));
    }

    private static void Change(bool? before, bool? after, string type, AircraftSnapshot snapshot, List<FlightEvent> events)
    {
        if (before is not null && after is not null && before != after)
            events.Add(new(type + (after == true ? "_ON" : "_OFF"), snapshot.RecordedAt, snapshot));
    }

    private void Transition(FlightPhase phase, string type, AircraftSnapshot snapshot, List<FlightEvent> events, double? value = null)
    {
        if (Phase == phase) return;
        Phase = phase;
        events.Add(new(type, snapshot.RecordedAt, snapshot, value));
    }

    private void ResetTransientState()
    {
        previous = null;
        parkedSince = null;
        touchdownCandidate = null;
        touchdownCandidateSince = null;
        airborneAfterTouchdownSince = null;
        bounceCount = 0;
        landingConfirmed = false;
    }
}
