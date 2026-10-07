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
    public void Stabilized_approach_descent_rate_requires_four_continuous_seconds()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-10-05T00:10:00Z");

        Assert.Empty(monitor.Process(Snapshot(t, 900, -1100, 5, true, 30), FlightPhase.Final, []));
        Assert.Empty(monitor.Process(Snapshot(t.AddSeconds(1), 885, -1150, 5, true, 30), FlightPhase.Final, []));
        Assert.Empty(monitor.Process(Snapshot(t.AddSeconds(2), 870, -1200, 5, true, 30), FlightPhase.Final, []));
        Assert.Empty(monitor.Process(Snapshot(t.AddSeconds(3), 850, -1250, 5, true, 30), FlightPhase.Final, []));

        var recovered = monitor.Process(Snapshot(t.AddSeconds(3.5), 840, -900, 5, true, 30), FlightPhase.Final, []);
        Assert.DoesNotContain(recovered, x => x.Code == "APPROACH_DESCENT_RATE_UNSTABLE");
    }

    [Fact]
    public void Stabilized_approach_descent_rate_is_reported_from_1000_agl_to_touchdown()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-10-05T00:20:00Z");

        monitor.Process(Snapshot(t, 980, -1100, 5, true, 30), FlightPhase.Final, []);
        monitor.Process(Snapshot(t.AddSeconds(1), 960, -1150, 5, true, 30), FlightPhase.Final, []);
        monitor.Process(Snapshot(t.AddSeconds(2), 940, -1200, 5, true, 30), FlightPhase.Final, []);
        monitor.Process(Snapshot(t.AddSeconds(3), 920, -1300, 5, true, 30), FlightPhase.Final, []);
        var observations = monitor.Process(Snapshot(t.AddSeconds(4), 900, -1450, 5, true, 30), FlightPhase.Final, []);

        var unstable = Assert.Single(observations, x => x.Code == "APPROACH_DESCENT_RATE_UNSTABLE");
        Assert.Equal("UNSTABLE", unstable.Status);
        Assert.Equal(-1450, unstable.Value);
        Assert.Equal("ft/min", unstable.Unit);
        Assert.Contains("4 s", unstable.Message);
        Assert.Contains("1000 ft AGL", unstable.Message);
    }


    [Fact]
    public void Speed_under_10k_requires_ten_continuous_seconds_above_255_knots()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-10-07T20:00:00Z");

        for (var second = 0; second <= 9; second++)
        {
            var observations = monitor.Process(
                Snapshot(t.AddSeconds(second), 9000, 0, 0, false, 0, 257),
                FlightPhase.Climb,
                []);
            Assert.DoesNotContain(observations, x => x.Code == "SPEED_UNDER_10K");
        }

        var recovered = monitor.Process(
            Snapshot(t.AddSeconds(10.5), 9000, 0, 0, false, 0, 255),
            FlightPhase.Climb,
            []);
        Assert.DoesNotContain(recovered, x => x.Code == "SPEED_UNDER_10K");

        for (var second = 20; second < 30; second++)
        {
            var observations = monitor.Process(
                Snapshot(t.AddSeconds(second), 9000, 0, 0, false, 0, 256 + (second % 2)),
                FlightPhase.Climb,
                []);
            Assert.DoesNotContain(observations, x => x.Code == "SPEED_UNDER_10K");
        }

        var sustained = monitor.Process(
            Snapshot(t.AddSeconds(30), 9000, 0, 0, false, 0, 260),
            FlightPhase.Climb,
            []);

        var observation = Assert.Single(sustained, x => x.Code == "SPEED_UNDER_10K");
        Assert.Equal(260, observation.Value);
        Assert.Equal("kt", observation.Unit);
        Assert.Contains("10 s", observation.Message);
        Assert.Contains("255 kt", observation.Message);
    }


    [Fact]
    public void Load_factor_exceedance_is_recorded_once_with_air_inter_limits()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-10-05T09:00:00Z");

        var moderate = monitor.Process(new AircraftSnapshot(
            Guid.NewGuid(), t, OnGround: false, GForce: 2.6), FlightPhase.Climb, []);

        var observation = Assert.Single(moderate, x => x.Code == "LOAD_FACTOR_EXCEEDED");
        Assert.Equal(2.6, observation.Value);
        Assert.Equal("g", observation.Unit);
        Assert.Equal("EXCEEDED", observation.Status);

        var duplicate = monitor.Process(new AircraftSnapshot(
            Guid.NewGuid(), t.AddSeconds(1), OnGround: false, GForce: -1.1), FlightPhase.Climb, []);
        Assert.DoesNotContain(duplicate, x => x.Code == "LOAD_FACTOR_EXCEEDED");
    }

    [Fact]
    public void Severe_load_factor_requests_maintenance_and_suppresses_normal_fact()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-10-05T09:10:00Z");

        var observations = monitor.Process(new AircraftSnapshot(
            Guid.NewGuid(), t, OnGround: false, GForce: -1.25), FlightPhase.Cruise, []);

        var severe = Assert.Single(observations);
        Assert.Equal("LOAD_FACTOR_MAINTENANCE", severe.Code);
        Assert.Equal(-1.25, severe.Value);
        Assert.Equal("MAINTENANCE_REQUIRED", severe.Status);

        var after = monitor.Process(new AircraftSnapshot(
            Guid.NewGuid(), t.AddSeconds(1), OnGround: false, GForce: 2.7), FlightPhase.Cruise, []);
        Assert.DoesNotContain(after, x => x.Code == "LOAD_FACTOR_EXCEEDED");
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
    public void Ground_fuel_event_from_legacy_tracker_is_ignored()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-10-06T13:04:54Z");
        var snapshot = new AircraftSnapshot(
            Guid.NewGuid(),
            t,
            GroundSpeedKnots: 0.1,
            FuelWeight: 20_000,
            OnGround: true);

        var observations = monitor.Process(
            snapshot,
            FlightPhase.Boarding,
            [new FlightEvent("FUEL_INCREASED", t, snapshot, 11_696)]);

        Assert.DoesNotContain(observations, x => x.Code == "FUEL_ADDED");
    }

    [Fact]
    public void Pause_segment_is_recorded_with_kind_and_duration()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-10-03T20:00:00Z");

        monitor.Process(new AircraftSnapshot(
            Guid.NewGuid(), t, OnGround: false, Paused: true, PauseKind: "ACTIVE_PAUSE"), FlightPhase.Cruise, []);
        Assert.Empty(monitor.Process(new AircraftSnapshot(
            Guid.NewGuid(), t.AddSeconds(5), OnGround: false, Paused: true, PauseKind: "ACTIVE_PAUSE"), FlightPhase.Cruise, []));

        var observations = monitor.Process(new AircraftSnapshot(
            Guid.NewGuid(), t.AddSeconds(12), OnGround: false, Paused: false), FlightPhase.Cruise, []);

        var pause = Assert.Single(observations);
        Assert.Equal("PAUSE", pause.Code);
        Assert.Equal("ACTIVE_PAUSE", pause.Status);
        Assert.Equal(12d, pause.Value);
        Assert.Contains("Active Pause", pause.Message);
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
        double flaps,
        double ias = 145) =>
        new(
            Guid.NewGuid(),
            time,
            Latitude: 48.7,
            Longitude: 2.3,
            AltitudeMslFeet: agl + 300,
            AltitudeAglFeet: agl,
            IndicatedAirspeedKnots: ias,
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
