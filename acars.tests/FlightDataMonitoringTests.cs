using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class FlightDataMonitoringTests
{
    [Fact]
    public void Approach_gates_record_stable_1000_and_unstable_500()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");

        monitor.Process(Snapshot(t, 1200, -700, 10, true, 25), FlightPhase.Final, []);
        var gate1000 = monitor.Process(Snapshot(t.AddSeconds(5), 950, -800, 12, true, 25), FlightPhase.Final, []);

        var stable = Assert.Single(gate1000);
        Assert.Equal("APPROACH_1000_STABLE", stable.Code);
        Assert.Equal("STABLE", stable.Status);

        monitor.Process(Snapshot(t.AddSeconds(10), 700, -900, 8, true, 30), FlightPhase.Final, []);
        var gate500 = monitor.Process(Snapshot(t.AddSeconds(15), 480, -1450, 34, true, 30), FlightPhase.Final, []);

        var unstable = Assert.Single(gate500);
        Assert.Equal("APPROACH_500_UNSTABLE", unstable.Code);
        Assert.Equal("UNSTABLE", unstable.Status);
        Assert.Contains("VS", unstable.Message);
        Assert.Contains("bank", unstable.Message);
    }

    [Fact]
    public void Missing_connector_data_marks_gate_unknown_instead_of_inventing_values()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");

        monitor.Process(Snapshot(t, 1200, -700, null, true, 25), FlightPhase.Approach, []);
        var decision = monitor.Process(Snapshot(t.AddSeconds(5), 950, -700, null, true, 25), FlightPhase.Approach, []);

        var observation = Assert.Single(decision);
        Assert.Equal("APPROACH_1000_UNKNOWN", observation.Code);
        Assert.Equal("UNKNOWN", observation.Status);
    }

    [Fact]
    public void Bank_excursion_is_closed_with_peak_and_duration()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");

        monitor.Process(Snapshot(t, 6000, 0, 20, false, 0), FlightPhase.Cruise, []);
        Assert.Empty(monitor.Process(Snapshot(t.AddSeconds(1), 6000, 0, 38, false, 0), FlightPhase.Cruise, []));
        Assert.Empty(monitor.Process(Snapshot(t.AddSeconds(4), 6000, 0, 47, false, 0), FlightPhase.Cruise, []));
        var recovered = monitor.Process(Snapshot(t.AddSeconds(8), 6000, 0, 25, false, 0), FlightPhase.Cruise, []);

        var observation = Assert.Single(recovered);
        Assert.Equal("EXCESSIVE_BANK", observation.Code);
        Assert.Equal(47, observation.Value);
        Assert.Contains("7 s", observation.Message);
    }

    [Fact]
    public void Existing_tracking_facts_become_review_observations_without_penalties()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");
        var snapshot = Snapshot(t, 15000, 0, 0, false, 0);
        var facts = new[] {
            new FlightEvent("FUEL_INCREASED", t, snapshot, 350),
            new FlightEvent("SIM_RATE_INCREASED", t.AddSeconds(1), snapshot, 2),
            new FlightEvent("SLEW_ACTIVE", t.AddSeconds(2), snapshot),
            new FlightEvent("GO_AROUND", t.AddSeconds(3), snapshot),
            new FlightEvent("TOUCHDOWN", t.AddSeconds(4), snapshot, -240),
            new FlightEvent("BOUNCE_COUNT", t.AddSeconds(5), snapshot, 2)
        };

        var observations = monitor.Process(snapshot, FlightPhase.Climb, facts);

        Assert.Contains(observations, x => x.Code == "FUEL_ADDED" && x.Value == 350);
        Assert.Contains(observations, x => x.Code == "SIM_RATE" && x.Value == 2);
        Assert.Contains(observations, x => x.Code == "SLEW");
        Assert.Contains(observations, x => x.Code == "GO_AROUND");
        Assert.Contains(observations, x => x.Code == "TOUCHDOWN" && x.Value == -240);
        Assert.Contains(observations, x => x.Code == "BOUNCE" && x.Value == 2);
        Assert.DoesNotContain(observations, x => x.Code.Contains("PENALTY", StringComparison.Ordinal));
    }

    [Fact]
    public void Restore_prevents_duplicate_approach_gate_after_recovery()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");
        monitor.Restore([
            new FdmObservation("APPROACH_1000_STABLE", "approach", t, "saved", Status: "STABLE")
        ]);

        monitor.Process(Snapshot(t.AddMinutes(1), 1200, -600, 5, true, 20), FlightPhase.Final, []);
        var after = monitor.Process(Snapshot(t.AddMinutes(1).AddSeconds(5), 950, -600, 5, true, 20), FlightPhase.Final, []);

        Assert.DoesNotContain(after, x => x.Code.StartsWith("APPROACH_1000_", StringComparison.Ordinal));
    }

    private static AircraftSnapshot Snapshot(
        DateTimeOffset time,
        double agl,
        double vs,
        double? bank,
        bool gearDown,
        double flaps) =>
        new(
            Guid.NewGuid(),
            time,
            Latitude: 48.7,
            Longitude: 2.3,
            AltitudeMslFeet: agl + 300,
            AltitudeAglFeet: agl,
            IndicatedAirspeedKnots: 145,
            GroundSpeedKnots: 150,
            VerticalSpeedFeetPerMinute: vs,
            HeadingDegrees: 180,
            FuelWeight: 8000,
            OnGround: false,
            ParkingBrake: false,
            GearDown: gearDown,
            FlapsPercent: flaps,
            BankDegrees: bank);
}
