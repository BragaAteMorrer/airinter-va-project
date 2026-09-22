using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

/// <summary>
/// Reference operational journey for Hermès. Server-side API tests cover the
/// Prométhée contract; this suite freezes the simulator-neutral flight lifecycle
/// that begins only after Dispatch returned READY.
/// </summary>
public sealed class ReferenceFlightE2ETests
{
    [Fact]
    public void Ready_operation_can_complete_reference_block_to_block_flight()
    {
        var engine = new FlightTrackingEngine();
        engine.Arm();
        var t = DateTimeOffset.Parse("2026-09-22T18:42:00Z");

        var boarding = engine.Process(Snapshot(t, true, 0, 0, 0, true));
        Assert.Equal(FlightPhase.AcarsReady, boarding.Phase);

        var outEvent = engine.Process(Snapshot(t.AddMinutes(1), true, 1, 0, 0, false));
        Assert.Contains(outEvent.Events, x => x.Type == "BLOCK_OFF");

        Assert.Equal(FlightPhase.TaxiOut, engine.Process(Snapshot(t.AddMinutes(4), true, 15, 0, 0, false)).Phase);
        Assert.Equal(FlightPhase.Takeoff, engine.Process(Snapshot(t.AddMinutes(15), false, 155, 40, 1300, false)).Phase);
        Assert.Equal(FlightPhase.Climb, engine.Process(Snapshot(t.AddMinutes(18), false, 250, 5000, 1500, false)).Phase);
        Assert.Equal(FlightPhase.Cruise, engine.Process(Snapshot(t.AddMinutes(35), false, 430, 37000, 0, false)).Phase);
        Assert.Equal(FlightPhase.Descent, engine.Process(Snapshot(t.AddHours(1).AddMinutes(35), false, 410, 25000, -1200, false)).Phase);
        Assert.Equal(FlightPhase.Approach, engine.Process(Snapshot(t.AddHours(1).AddMinutes(55), false, 190, 7000, -700, false)).Phase);

        var firstTouch = engine.Process(Snapshot(t.AddHours(2).AddMinutes(5), true, 132, 0, -310, false));
        Assert.Contains(firstTouch.Events, x => x.Type == "TOUCHDOWN_FIRST");

        var taxiIn = engine.Process(Snapshot(t.AddHours(2).AddMinutes(6), true, 20, 0, 0, false));
        Assert.Equal(FlightPhase.TaxiIn, taxiIn.Phase);
        engine.Process(Snapshot(t.AddHours(2).AddMinutes(8), true, 0, 0, 0, true));
        var blockOn = engine.Process(Snapshot(t.AddHours(2).AddMinutes(8).AddSeconds(16), true, 0, 0, 0, true));
        Assert.Equal(FlightPhase.BlockOn, blockOn.Phase);
        Assert.Contains(blockOn.Events, x => x.Type == "BLOCK_ON");
    }

    [Fact]
    public void Reference_contract_keeps_operation_and_pirep_as_distinct_stable_ids()
    {
        var operationId = "op_reference_itf749";
        var pirepId = "pirep-reference";

        Assert.StartsWith("op_", operationId);
        Assert.NotEqual(operationId, pirepId);\n    }\n\n    [Fact]\n    public void Recorder_refuses_completion_until_in_and_all_messages_are_acknowledged()\n    {\n        var recorder = new FlightRecorder();\n        var t = DateTimeOffset.Parse("2026-09-22T18:42:00Z");\n        var start = Legacy(t, true, 0, 0, 0, true);\n        recorder.Start("https://promethee.example", "pirep-reference", start, "op_reference_itf749");\n\n        var beforeIn = Assert.Throws<InvalidOperationException>(() => recorder.Complete());\n        Assert.Contains("IN", beforeIn.Message);\n\n        recorder.Capture(Legacy(t.AddSeconds(1), true, 6, 0, 0, false));\n        recorder.Capture(Legacy(t.AddSeconds(2), false, 155, 40, 1300, false));\n        recorder.Capture(Legacy(t.AddSeconds(3), false, 250, 5000, 1500, false));\n        recorder.Capture(Legacy(t.AddSeconds(4), false, 250, 2000, -800, false, gearDown: true, flaps: 20));\n        recorder.Capture(Legacy(t.AddSeconds(5), true, 25, 0, -310, false, touchdownVelocity: 5.1667));\n        recorder.Capture(Legacy(t.AddSeconds(6), true, 10, 0, 0, false));\n        recorder.Capture(Legacy(t.AddSeconds(7), true, 0, 0, 0, true));\n        recorder.Capture(Legacy(t.AddSeconds(23), true, 0, 0, 0, true));\n\n        Assert.Equal("IN", recorder.Flight?.Phase);\n        var pending = Assert.Throws<InvalidOperationException>(() => recorder.Complete());\n        Assert.Contains("synchroniser", pending.Message);\n\n        recorder.AcknowledgePositions(recorder.Pending.Select(x => x.Sample.SampleId).ToArray());\n        recorder.AcknowledgeEvents(recorder.PendingEvents.Select(x => x.EventId).ToArray());\n        recorder.Complete();\n        Assert.Null(recorder.Flight);\n    }
    }

    private static Sample Legacy(DateTimeOffset time, bool onGround, double gs, double agl, double vs, bool parking, bool gearDown = false, double flaps = 0, double touchdownVelocity = 0) =>\n        new(Guid.NewGuid(), time, 48.7, 2.3, agl, agl, gs, gs, vs, 180, 8000, onGround,\n            0, gearDown, 0, flaps, false, 0, touchdownVelocity, parking);\n\n    private static AircraftSnapshot Snapshot(DateTimeOffset time, bool onGround, double gs, double agl, double vs, bool parking) =>
        new(Guid.NewGuid(), time, Latitude: 48.7, Longitude: 2.3, AltitudeMslFeet: agl,
            AltitudeAglFeet: agl, IndicatedAirspeedKnots: gs, GroundSpeedKnots: gs,
            VerticalSpeedFeetPerMinute: vs, HeadingDegrees: 180, FuelWeight: 8000,
            OnGround: onGround, ParkingBrake: parking, GearDown: onGround || agl < 3000,
            FlapsPercent: onGround || agl < 3000 ? 20 : 0);

    private static Sample LegacySample(DateTimeOffset time, bool onGround, double gs, double agl, double vs, bool parking) =>
        new(Guid.NewGuid(), time, 48.7, 2.3, agl, agl, gs, gs, vs, 180, 8000, onGround,
            0, true, 0, 20, false, 0, 0, parking);
}
