using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class SopFactQueueTests
{
    [Fact]
    public void Fdm_fact_is_persisted_until_server_acknowledgement()
    {
        var folder = TempFolder();
        var t = DateTimeOffset.Parse("2026-09-24T21:00:00Z");
        var recorder = new FlightRecorder(folder);

        recorder.Start("https://promethee.example", "pirep-sop", Snapshot(t, 1), "op_sop");
        recorder.Capture(Snapshot(t.AddSeconds(1), 2));

        var queued = Assert.Single(recorder.PendingFacts);
        Assert.Equal("SIM_RATE", queued.Observation.Code);
        Assert.Equal(2, queued.Observation.Value);

        var recovered = new FlightRecorder(folder);
        var pendingAfterCrash = Assert.Single(recovered.PendingFacts);
        Assert.Equal(queued.FactId, pendingAfterCrash.FactId);

        recovered.AcknowledgeFacts([queued.FactId]);
        Assert.Empty(recovered.PendingFacts);

        var persistedAck = new FlightRecorder(folder);
        Assert.Empty(persistedAck.PendingFacts);
    }

    [Fact]
    public void Legacy_flight_without_operation_id_does_not_create_unsendable_sop_outbox()
    {
        var folder = TempFolder();
        var t = DateTimeOffset.Parse("2026-09-24T21:00:00Z");
        var recorder = new FlightRecorder(folder);

        recorder.Start("https://promethee.example", "legacy-pirep", Snapshot(t, 1));
        recorder.Capture(Snapshot(t.AddSeconds(1), 2));

        Assert.Contains(recorder.Flight!.Observations, x => x.Code == "SIM_RATE");
        Assert.Empty(recorder.PendingFacts);
    }

    [Fact]
    public void Taxi_monitor_emits_peak_speed_as_fact_instead_of_company_threshold()
    {
        var monitor = new FlightDataMonitor();
        var t = DateTimeOffset.Parse("2026-09-24T21:00:00Z");

        monitor.Process(Snapshot(t, 1, true, 10), FlightPhase.TaxiOut, []);
        monitor.Process(Snapshot(t.AddSeconds(2), 1, true, 37), FlightPhase.TaxiOut, []);
        var facts = monitor.Process(Snapshot(t.AddSeconds(4), 1, false, 145), FlightPhase.Takeoff, []);

        var taxi = Assert.Single(facts, x => x.Code == "TAXI_SPEED_MAX");
        Assert.Equal(37, taxi.Value);
        Assert.Equal("kt", taxi.Unit);
        Assert.Equal("TAXI_OUT", taxi.Phase);
    }

    private static string TempFolder()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Sop-Tests", Guid.NewGuid().ToString("N"));
        Directory.CreateDirectory(folder);
        return folder;
    }

    private static AircraftSnapshot Snapshot(
        DateTimeOffset time,
        double simRate,
        bool onGround = true,
        double groundSpeed = 0) =>
        new(
            Guid.NewGuid(),
            time,
            Latitude: 48.7,
            Longitude: 2.3,
            AltitudeMslFeet: onGround ? 300 : 500,
            AltitudeAglFeet: onGround ? 0 : 200,
            IndicatedAirspeedKnots: groundSpeed,
            GroundSpeedKnots: groundSpeed,
            VerticalSpeedFeetPerMinute: onGround ? 0 : 1000,
            HeadingDegrees: 180,
            FuelWeight: 8000,
            OnGround: onGround,
            ParkingBrake: groundSpeed == 0,
            GearDown: true,
            FlapsPercent: 0,
            SimulationRate: simRate,
            BankDegrees: 0);
}
