namespace Promethee;

public enum FlightPhase
{
    Reserved, Preparation, Boarding, AcarsReady, BlockOff, TaxiOut, Takeoff,
    Climb, Cruise, Descent, Approach, Landing, TaxiIn, BlockOn, Completed
}

public sealed record FlightEvent(string Type, DateTimeOffset OccurredAt, AircraftSnapshot Snapshot, double? Value = null);
public sealed record TrackingDecision(FlightPhase Phase, IReadOnlyList<FlightEvent> Events);

/// <summary>
/// Deterministic, simulator-independent phase and event detector. It records
/// observed facts only; company policy and penalties remain on Promethee.
/// </summary>
public sealed class FlightTrackingEngine
{
    private AircraftSnapshot? previous;
    private DateTimeOffset? parkedSince;
    private AircraftSnapshot? touchdownCandidate;
    private DateTimeOffset? touchdownCandidateSince;
    private int bounceCount;
    private const int TouchdownConfirmationSeconds = 8;
    public FlightPhase Phase { get; private set; } = FlightPhase.Reserved;

    public void Arm()
    {
        previous = null;
        parkedSince = null;
        touchdownCandidate = null;
        touchdownCandidateSince = null;
        bounceCount = 0;
        Phase = FlightPhase.AcarsReady;
    }

    public TrackingDecision Process(AircraftSnapshot current)
    {
        var events = new List<FlightEvent>();
        if (previous is null) { previous = current; return new(Phase, events); }
        DetectSystemChanges(previous, current, events);

        if (Phase == FlightPhase.AcarsReady && current.OnGround == true && current.ParkingBrake == false && (current.GroundSpeedKnots ?? 0) > .5)
            Transition(FlightPhase.BlockOff, "BLOCK_OFF", current, events);
        if (Phase is FlightPhase.BlockOff or FlightPhase.AcarsReady && current.OnGround == true && (current.GroundSpeedKnots ?? 0) >= 5)
            Transition(FlightPhase.TaxiOut, "TAXI_OUT", current, events);
        if (previous.OnGround == true && current.OnGround == false)
        {
            if (Phase is FlightPhase.Landing or FlightPhase.TaxiIn)
            {
                bounceCount++;
                Phase = FlightPhase.Landing;
                events.Add(new("BOUNCE", current.RecordedAt, current, bounceCount));
            }
            else
                Transition(FlightPhase.Takeoff, "TAKEOFF", current, events);
        }
        if (Phase == FlightPhase.Takeoff && (current.AltitudeAglFeet ?? 0) >= 1500 && (current.VerticalSpeedFeetPerMinute ?? 0) > 100)
            Transition(FlightPhase.Climb, "CLIMB", current, events);
        if (Phase == FlightPhase.Climb && (current.AltitudeAglFeet ?? 0) > 10000 && Math.Abs(current.VerticalSpeedFeetPerMinute ?? 0) < 300)
            Transition(FlightPhase.Cruise, "CRUISE", current, events);
        if (Phase is FlightPhase.Climb or FlightPhase.Cruise && (current.VerticalSpeedFeetPerMinute ?? 0) < -300)
            Transition(FlightPhase.Descent, "DESCENT", current, events);
        if (Phase == FlightPhase.Descent && (current.AltitudeAglFeet ?? double.MaxValue) < 10000)
            Transition(FlightPhase.Approach, "APPROACH", current, events);
        if (previous.OnGround == false && current.OnGround == true)
        {
            var landingRate = current.VerticalSpeedFeetPerMinute ?? previous.VerticalSpeedFeetPerMinute;
            Transition(FlightPhase.Landing, "LANDING", current, events, landingRate);
            touchdownCandidate = current;
            touchdownCandidateSince = current.RecordedAt;
            events.Add(new(bounceCount == 0 ? "TOUCHDOWN_FIRST" : "TOUCHDOWN_BOUNCE", current.RecordedAt, current, landingRate));
        }
        ConfirmTouchdownIfStable(current, events);
        if (Phase == FlightPhase.Landing && current.OnGround == true && (current.GroundSpeedKnots ?? 0) < 30)
            Transition(FlightPhase.TaxiIn, "TAXI_IN", current, events);
        if (Phase == FlightPhase.TaxiIn && current.OnGround == true && current.ParkingBrake == true && (current.GroundSpeedKnots ?? 0) < 2)
        {
            parkedSince ??= current.RecordedAt;
            if (current.RecordedAt - parkedSince >= TimeSpan.FromSeconds(15)) Transition(FlightPhase.BlockOn, "BLOCK_ON", current, events);
        }
        else if (Phase != FlightPhase.BlockOn) parkedSince = null;

        previous = current;
        return new(Phase, events);
    }

    private void ConfirmTouchdownIfStable(AircraftSnapshot current, List<FlightEvent> events)
    {
        if (touchdownCandidate is null || touchdownCandidateSince is null || current.OnGround != true)
            return;
        if (current.RecordedAt - touchdownCandidateSince < TimeSpan.FromSeconds(TouchdownConfirmationSeconds))
            return;

        var rate = touchdownCandidate.VerticalSpeedFeetPerMinute;
        events.Add(new("TOUCHDOWN", touchdownCandidate.RecordedAt, touchdownCandidate, rate));
        if (bounceCount > 0)
            events.Add(new("BOUNCE_COUNT", current.RecordedAt, current, bounceCount));
        touchdownCandidate = null;
        touchdownCandidateSince = null;
        bounceCount = 0;
    }

    private static void DetectSystemChanges(AircraftSnapshot before, AircraftSnapshot after, List<FlightEvent> events)
    {
        Change(before.BeaconLight, after.BeaconLight, "BEACON", after, events);
        Change(before.ParkingBrake, after.ParkingBrake, "PARKING_BRAKE", after, events);
        Change(before.GearDown, after.GearDown, "GEAR", after, events);
        Change(before.LandingLight, after.LandingLight, "LANDING_LIGHTS", after, events);
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
}
