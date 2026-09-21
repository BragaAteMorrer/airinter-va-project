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
    public void Normal_ground_to_ground_sequence_reaches_block_on_once()
    {
        var engine = new FlightTrackingEngine();
        engine.Arm();
        var start = DateTimeOffset.Parse("2026-09-21T12:00:00Z");

        engine.Process(Snapshot(start, true, 0, 0, 0, true));
        Assert.Contains("BLOCK_OFF", engine.Process(Snapshot(start.AddSeconds(10), true, 1, 0, 0, false)).Events.Select(x => x.Type));
        Assert.Equal(FlightPhase.TaxiOut, engine.Process(Snapshot(start.AddSeconds(20), true, 12, 0, 0, false)).Phase);
        Assert.Equal(FlightPhase.Takeoff, engine.Process(Snapshot(start.AddSeconds(30), false, 150, 50, 1200, false)).Phase);
        Assert.Equal(FlightPhase.Climb, engine.Process(Snapshot(start.AddMinutes(2), false, 250, 3000, 1400, false)).Phase);
        Assert.Equal(FlightPhase.Cruise, engine.Process(Snapshot(start.AddMinutes(20), false, 450, 35000, 0, false)).Phase);
        Assert.Equal(FlightPhase.Descent, engine.Process(Snapshot(start.AddMinutes(50), false, 430, 25000, -1200, false)).Phase);
        Assert.Equal(FlightPhase.Approach, engine.Process(Snapshot(start.AddMinutes(65), false, 180, 8000, -800, false)).Phase);

        var landing = engine.Process(Snapshot(start.AddMinutes(75), true, 130, 0, -180, false));
        Assert.Contains(landing.Events, x => x.Type == "TOUCHDOWN_FIRST");
        var taxiIn = engine.Process(Snapshot(start.AddMinutes(77), true, 15, 0, 0, false));
        Assert.Contains(taxiIn.Events, x => x.Type == "TOUCHDOWN" && x.Value == -180);
        Assert.Equal(FlightPhase.TaxiIn, taxiIn.Phase);
        engine.Process(Snapshot(start.AddMinutes(78), true, 0, 0, 0, true));
        Assert.Equal(FlightPhase.BlockOn, engine.Process(Snapshot(start.AddMinutes(78).AddSeconds(16), true, 0, 0, 0, true)).Phase);
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

    [Fact]
    public void Bounced_landing_emits_one_confirmed_touchdown_with_bounce_count()
    {
        var engine = new FlightTrackingEngine();
        engine.Arm();
        var start = DateTimeOffset.Parse("2026-09-21T12:00:00Z");

        engine.Process(Snapshot(start, false, 145, 20, -160, false));
        var firstContact = engine.Process(Snapshot(start.AddSeconds(1), true, 130, 0, -140, false));
        Assert.Contains(firstContact.Events, x => x.Type == "TOUCHDOWN_FIRST");
        Assert.DoesNotContain(firstContact.Events, x => x.Type == "TOUCHDOWN");

        var bounce = engine.Process(Snapshot(start.AddSeconds(2), false, 125, 4, 80, false));
        Assert.Contains(bounce.Events, x => x.Type == "BOUNCE" && x.Value == 1);
        var finalContact = engine.Process(Snapshot(start.AddSeconds(3), true, 120, 0, -190, false));
        Assert.Contains(finalContact.Events, x => x.Type == "TOUCHDOWN_BOUNCE");

        var confirmed = engine.Process(Snapshot(start.AddSeconds(12), true, 90, 0, 0, false));
        Assert.Contains(confirmed.Events, x => x.Type == "TOUCHDOWN" && x.Value == -190);
        Assert.Contains(confirmed.Events, x => x.Type == "BOUNCE_COUNT" && x.Value == 1);
    }

    private static AircraftSnapshot Snapshot(DateTimeOffset time, bool onGround, double gs, double agl, double vs, bool parking) =>
        new(Guid.NewGuid(), time, Latitude: 48.7, Longitude: 2.3, GroundSpeedKnots: gs,
            AltitudeAglFeet: agl, VerticalSpeedFeetPerMinute: vs, OnGround: onGround, ParkingBrake: parking);
}
