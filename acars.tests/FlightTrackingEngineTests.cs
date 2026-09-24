using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class FlightTrackingEngineTests
{
    [Fact]
    public void Connector_facts_are_emitted_only_when_a_known_value_changes()
    {
        var engine = new FlightTrackingEngine();
        var time = DateTimeOffset.Parse("2026-09-21T12:00:00Z");
        engine.Process(new(Guid.NewGuid(), time, BeaconLight: null));
        var changed = engine.Process(new(Guid.NewGuid(), time.AddSeconds(1), BeaconLight: true));
        Assert.DoesNotContain(changed.Events, x => x.Type == "BEACON_ON");

        var switched = engine.Process(new(Guid.NewGuid(), time.AddSeconds(2), BeaconLight: false));
        Assert.Contains(switched.Events, x => x.Type == "BEACON_OFF");
    }

    [Fact]
    public void Normal_ground_to_ground_sequence_emits_single_out_off_on_in()
    {
        var engine = new FlightTrackingEngine();
        engine.Arm(FlightPhase.Boarding);
        var start = DateTimeOffset.Parse("2026-09-21T12:00:00Z");
        var all = new List<FlightEvent>();

        Add(engine.Process(Snapshot(start, true, 0, 0, 0, true)), all);
        Assert.Empty(all.Where(x => x.Type == "OUT"));

        Add(engine.Process(Snapshot(start.AddSeconds(10), true, 1, 0, 0, false)), all);
        Assert.Equal(FlightPhase.Pushback, engine.Phase);
        Add(engine.Process(Snapshot(start.AddSeconds(20), true, 12, 0, 0, false)), all);
        Assert.Equal(FlightPhase.TaxiOut, engine.Phase);

        Add(engine.Process(Snapshot(start.AddSeconds(30), false, 150, 50, 1200, false)), all);
        Assert.Equal(FlightPhase.Takeoff, engine.Phase);
        Add(engine.Process(Snapshot(start.AddMinutes(2), false, 250, 3000, 1400, false, gearDown: false)), all);
        Assert.Equal(FlightPhase.Climb, engine.Phase);
        Add(engine.Process(Snapshot(start.AddMinutes(20), false, 450, 35000, 0, false, gearDown: false)), all);
        Assert.Equal(FlightPhase.Cruise, engine.Phase);
        Add(engine.Process(Snapshot(start.AddMinutes(50), false, 430, 25000, -1200, false, gearDown: false)), all);
        Assert.Equal(FlightPhase.Descent, engine.Phase);
        Add(engine.Process(Snapshot(start.AddMinutes(65), false, 180, 8000, -800, false, gearDown: true, flaps: 10)), all);
        Assert.Equal(FlightPhase.Approach, engine.Phase);
        Add(engine.Process(Snapshot(start.AddMinutes(70), false, 150, 2500, -600, false, gearDown: true, flaps: 25)), all);
        Assert.Equal(FlightPhase.Final, engine.Phase);

        var firstTouch = engine.Process(Snapshot(start.AddMinutes(75), true, 130, 0, -180, false, touchdownRate: -180));
        Add(firstTouch, all);
        Assert.Equal(FlightPhase.Landing, firstTouch.Phase);
        Assert.Contains(firstTouch.Events, x => x.Type == "TOUCHDOWN_FIRST");
        Assert.DoesNotContain(firstTouch.Events, x => x.Type == "ON");

        Add(engine.Process(Snapshot(start.AddMinutes(75).AddSeconds(9), true, 90, 0, 0, false)), all);
        Assert.Single(all.Where(x => x.Type == "ON"));

        Add(engine.Process(Snapshot(start.AddMinutes(77), true, 15, 0, 0, false)), all);
        Assert.Equal(FlightPhase.TaxiIn, engine.Phase);
        Add(engine.Process(Snapshot(start.AddMinutes(78), true, 0, 0, 0, true)), all);
        Add(engine.Process(Snapshot(start.AddMinutes(78).AddSeconds(16), true, 0, 0, 0, true)), all);
        Assert.Equal(FlightPhase.In, engine.Phase);

        Assert.Single(all.Where(x => x.Type == "OUT"));
        Assert.Single(all.Where(x => x.Type == "OFF"));
        Assert.Single(all.Where(x => x.Type == "ON"));
        Assert.Single(all.Where(x => x.Type == "IN"));
    }

    [Fact]
    public void Direct_taxi_without_pushback_still_emits_out_once()
    {
        var engine = new FlightTrackingEngine();
        engine.Arm(FlightPhase.Boarding);
        var t = DateTimeOffset.Parse("2026-09-21T12:00:00Z");
        engine.Process(Snapshot(t, true, 0, 0, 0, true));

        var decision = engine.Process(Snapshot(t.AddSeconds(5), true, 8, 0, 0, false));

        Assert.Equal(FlightPhase.TaxiOut, decision.Phase);
        Assert.Single(decision.Events.Where(x => x.Type == "OUT"));
        Assert.Contains(decision.Events, x => x.Type == "TAXI_OUT");
    }

    [Fact]
    public void Accelerating_takeoff_roll_enters_takeoff_before_off()
    {
        var engine = new FlightTrackingEngine();
        engine.Arm(FlightPhase.Boarding);
        var t = DateTimeOffset.Parse("2026-09-21T12:00:00Z");

        engine.Process(Snapshot(t, true, 0, 0, 0, true));
        engine.Process(Snapshot(t.AddSeconds(5), true, 12, 0, 0, false));
        engine.Process(Snapshot(t.AddSeconds(10), true, 22, 0, 0, false));
        var roll = engine.Process(Snapshot(t.AddSeconds(14), true, 42, 0, 0, false));

        Assert.Equal(FlightPhase.Takeoff, roll.Phase);
        Assert.Contains(roll.Events, x => x.Type == "TAKEOFF");
        Assert.DoesNotContain(roll.Events, x => x.Type == "OFF");

        var airborne = engine.Process(Snapshot(t.AddSeconds(17), false, 145, 50, 1300, false));
        Assert.Contains(airborne.Events, x => x.Type == "OFF");
    }

    [Fact]
    public void Go_around_before_touchdown_returns_to_climb_without_on()
    {
        var engine = new FlightTrackingEngine();
        engine.Restore(FlightPhase.Final);
        var t = DateTimeOffset.Parse("2026-09-21T12:00:00Z");
        engine.Process(Snapshot(t, false, 145, 1200, -700, false, gearDown: true, flaps: 30));

        var goAround = engine.Process(Snapshot(t.AddSeconds(8), false, 155, 900, 900, false, gearDown: true, flaps: 20));

        Assert.Equal(FlightPhase.Climb, goAround.Phase);
        Assert.Contains(goAround.Events, x => x.Type == "GO_AROUND");
        Assert.Contains(goAround.Events, x => x.Type == "CLIMB");
        Assert.DoesNotContain(goAround.Events, x => x.Type == "ON");
    }

    [Fact]
    public void Bounced_landing_confirms_only_final_touchdown()
    {
        var engine = new FlightTrackingEngine();
        engine.Restore(FlightPhase.Final);
        var start = DateTimeOffset.Parse("2026-09-21T12:00:00Z");

        engine.Process(Snapshot(start, false, 145, 20, -160, false, gearDown: true, flaps: 30));
        var firstContact = engine.Process(Snapshot(start.AddSeconds(1), true, 130, 0, -140, false, touchdownRate: -140));
        Assert.Contains(firstContact.Events, x => x.Type == "TOUCHDOWN_FIRST");
        Assert.DoesNotContain(firstContact.Events, x => x.Type == "ON");

        var bounce = engine.Process(Snapshot(start.AddSeconds(2), false, 125, 4, 80, false, gearDown: true, flaps: 30));
        Assert.Contains(bounce.Events, x => x.Type == "BOUNCE" && x.Value == 1);

        var finalContact = engine.Process(Snapshot(start.AddSeconds(3), true, 120, 0, -190, false, touchdownRate: -190));
        Assert.Contains(finalContact.Events, x => x.Type == "TOUCHDOWN_BOUNCE");

        var confirmed = engine.Process(Snapshot(start.AddSeconds(12), true, 90, 0, 0, false));
        Assert.Contains(confirmed.Events, x => x.Type == "TOUCHDOWN" && x.Value == -190);
        Assert.Contains(confirmed.Events, x => x.Type == "ON" && x.Value == -190);
        Assert.Contains(confirmed.Events, x => x.Type == "BOUNCE_COUNT" && x.Value == 1);
    }

    [Fact]
    public void Touch_and_go_never_emits_on_for_the_runway_contact()
    {
        var engine = new FlightTrackingEngine();
        engine.Restore(FlightPhase.Final);
        var t = DateTimeOffset.Parse("2026-09-21T12:00:00Z");
        var events = new List<FlightEvent>();

        Add(engine.Process(Snapshot(t, false, 135, 30, -250, false, gearDown: true, flaps: 30)), events);
        Add(engine.Process(Snapshot(t.AddSeconds(1), true, 125, 0, -200, false, touchdownRate: -200)), events);
        Add(engine.Process(Snapshot(t.AddSeconds(3), false, 130, 20, 500, false, gearDown: true, flaps: 20)), events);
        Add(engine.Process(Snapshot(t.AddSeconds(10), false, 150, 250, 1200, false, gearDown: false, flaps: 10)), events);

        Assert.Equal(FlightPhase.Climb, engine.Phase);
        Assert.Contains(events, x => x.Type == "TOUCH_AND_GO");
        Assert.DoesNotContain(events, x => x.Type == "ON");
    }

    [Fact]
    public void Restored_cruise_does_not_restart_at_acars_ready()
    {
        var engine = new FlightTrackingEngine();
        engine.Restore(FlightPhase.Cruise);
        var t = DateTimeOffset.Parse("2026-09-21T12:00:00Z");

        Assert.Equal(FlightPhase.Cruise, engine.Process(Snapshot(t, false, 430, 35000, 0, false, gearDown: false)).Phase);
        Assert.Equal(FlightPhase.Descent, engine.Process(Snapshot(t.AddSeconds(5), false, 420, 34500, -900, false, gearDown: false)).Phase);
    }

    [Fact]
    public void Legacy_persisted_phase_names_have_safe_mappings()
    {
        Assert.Equal(FlightPhase.Pushback, FlightTrackingEngine.ParsePhase("OUT"));
        Assert.Equal(FlightPhase.Cruise, FlightTrackingEngine.ParsePhase("ENROUTE"));
        Assert.Equal(FlightPhase.TaxiIn, FlightTrackingEngine.ParsePhase("TAXI IN"));
        Assert.Equal(FlightPhase.In, FlightTrackingEngine.ParsePhase("BLOCK_ON"));
    }

    [Fact]
    public void Telemetry_anomalies_are_exposed_as_facts_not_penalties()
    {
        var engine = new FlightTrackingEngine();
        var start = DateTimeOffset.Parse("2026-09-21T12:00:00Z");

        engine.Process(new AircraftSnapshot(
            Guid.NewGuid(), start, FuelWeight: 1_000, SlewActive: false, SimulationRate: 1));

        var decision = engine.Process(new AircraftSnapshot(
            Guid.NewGuid(), start.AddSeconds(5), FuelWeight: 1_301, SlewActive: true, SimulationRate: 2));

        Assert.Contains(decision.Events, x => x.Type == "SLEW_ACTIVE");
        Assert.Contains(decision.Events, x => x.Type == "SIM_RATE_INCREASED" && x.Value == 2);
        Assert.Contains(decision.Events, x => x.Type == "FUEL_INCREASED" && x.Value == 301);
        Assert.DoesNotContain(decision.Events, x => x.Type.Contains("PENALTY", StringComparison.Ordinal));
    }

    private static void Add(TrackingDecision decision, List<FlightEvent> events) => events.AddRange(decision.Events);

    private static AircraftSnapshot Snapshot(
        DateTimeOffset time,
        bool onGround,
        double gs,
        double agl,
        double vs,
        bool parking,
        bool gearDown = true,
        double flaps = 0,
        double? touchdownRate = null) =>
        new(
            Guid.NewGuid(),
            time,
            Latitude: 48.7,
            Longitude: 2.3,
            AltitudeMslFeet: agl + 300,
            AltitudeAglFeet: agl,
            IndicatedAirspeedKnots: gs,
            GroundSpeedKnots: gs,
            VerticalSpeedFeetPerMinute: vs,
            HeadingDegrees: 180,
            FuelWeight: 8000,
            OnGround: onGround,
            ParkingBrake: parking,
            GearDown: gearDown,
            FlapsPercent: flaps,
            TouchdownVerticalSpeedFeetPerMinute: touchdownRate);
}
