using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class AircraftGroundSafetyTests
{
    private static AircraftSnapshot Snapshot(string title, bool? standard, bool? fenix = null, bool? chocks = null) =>
        new(Guid.NewGuid(), DateTimeOffset.UtcNow, AircraftTitle: title,
            OnGround: true, ParkingBrake: standard, FenixParkingBrake: fenix,
            WheelChocks: chocks, GroundSpeedKnots: 0, EnginesRunning: [false, false]);

    [Fact]
    public void Fenix_lever_true_overrides_unsynchronised_standard_brake()
    {
        var snapshot = Snapshot("Fenix Simulations A320 CFM", false, fenix: true);
        Assert.False(snapshot.ParkingBrake);
        Assert.True(snapshot.StandSecured);
    }

    [Fact]
    public void Fenix_chocks_secure_stand_without_falsifying_brake()
    {
        var snapshot = Snapshot("Fenix Simulations A321 IAE", false, chocks: true);
        Assert.False(snapshot.ParkingBrake);
        Assert.True(snapshot.StandSecured);
    }

    [Fact]
    public void Generic_aircraft_cannot_use_Fenix_chocks()
    {
        var snapshot = Snapshot("iniBuilds A320neo", false, chocks: true);
        Assert.False(snapshot.StandSecured);
    }

    [Fact]
    public void Unknown_parking_state_is_never_accepted()
    {
        Assert.Null(Snapshot("Fenix Simulations A319", null).StandSecured);
        Assert.False(Snapshot("Fenix Simulations A319", false).StandSecured);
    }

    [Fact]
    public void Standard_brake_still_secures_any_aircraft()
    {
        Assert.True(Snapshot("Air Inter A300", true).StandSecured);
    }

    [Fact]
    public void Fenix_chocks_are_carried_from_fsuipc_frame_into_snapshot()
    {
        var now = DateTimeOffset.UtcNow;
        var frame = new FsuipcTelemetryFrame(
            now, 48.7, 2.3, 310, 0, 0, 0, 0, 180, 0, 0, 8200,
            true, false, true, 0, [false, false], false,
            false, false, false, false, false, false,
            "Fenix Simulations A320 CFM",
            FenixParkingBrake: false, WheelChocks: true);
        var snapshot = FsuipcTelemetryMapper.ToSnapshot(frame);
        Assert.True(snapshot.StandSecured);
        Assert.False(snapshot.ParkingBrake);
        Assert.True(snapshot.WheelChocks);
    }

    [Fact]
    public void Fenix_chocks_confirm_IN_only_after_fifteen_seconds_stationary()
    {
        var engine = new FlightTrackingEngine();
        engine.Restore(FlightPhase.TaxiIn);
        var t = DateTimeOffset.UtcNow;
        var parked = Snapshot("Fenix Simulations A320 CFM", false, chocks: true);
        engine.Process(parked with { RecordedAt = t, GroundSpeedKnots = 0 });
        var early = engine.Process(parked with { SampleId = Guid.NewGuid(), RecordedAt = t.AddSeconds(14) });
        Assert.DoesNotContain(early.Events, x => x.Type == "IN");
        var confirmed = engine.Process(parked with { SampleId = Guid.NewGuid(), RecordedAt = t.AddSeconds(16) });
        Assert.Contains(confirmed.Events, x => x.Type == "IN");
    }

    [Fact]
    public void Fenix_chocks_restart_block_on_timer_after_movement()
    {
        var engine = new FlightTrackingEngine();
        engine.Restore(FlightPhase.TaxiIn);
        var t = DateTimeOffset.UtcNow;
        var parked = Snapshot("Fenix Simulations A320 CFM", false, chocks: true);
        engine.Process(parked with { RecordedAt = t });
        engine.Process(parked with { SampleId = Guid.NewGuid(), RecordedAt = t.AddSeconds(8), GroundSpeedKnots = 4 });
        var early = engine.Process(parked with { SampleId = Guid.NewGuid(), RecordedAt = t.AddSeconds(16) });
        Assert.DoesNotContain(early.Events, x => x.Type == "IN");
        var confirmed = engine.Process(parked with { SampleId = Guid.NewGuid(), RecordedAt = t.AddSeconds(31) });
        Assert.Contains(confirmed.Events, x => x.Type == "IN");
    }

    [Fact]
    public void Fenix_chocks_do_not_confirm_IN_when_moving()
    {
        var engine = new FlightTrackingEngine();
        engine.Restore(FlightPhase.TaxiIn);
        var t = DateTimeOffset.UtcNow;
        var moving = Snapshot("Fenix Simulations A320 CFM", false, chocks: true);
        engine.Process(moving with { RecordedAt = t, GroundSpeedKnots = 5 });
        var decision = engine.Process(moving with { SampleId = Guid.NewGuid(), RecordedAt = t.AddSeconds(25), GroundSpeedKnots = 5 });
        Assert.DoesNotContain(decision.Events, x => x.Type == "IN");
    }
}
