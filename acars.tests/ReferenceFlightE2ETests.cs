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
        engine.Arm(FlightPhase.Boarding);
        var t = DateTimeOffset.Parse("2026-09-22T18:42:00Z");
        var events = new List<FlightEvent>();

        Add(engine.Process(Snapshot(t, true, 0, 0, 0, true)), events);

        Add(engine.Process(Snapshot(t.AddMinutes(1), true, 1, 0, 0, false)), events);
        Assert.Equal(FlightPhase.Pushback, engine.Phase);
        Assert.Contains(events, x => x.Type == "OUT");

        Add(engine.Process(Snapshot(t.AddMinutes(4), true, 15, 0, 0, false)), events);
        Assert.Equal(FlightPhase.TaxiOut, engine.Phase);

        Add(engine.Process(Snapshot(t.AddMinutes(15), false, 155, 40, 1300, false)), events);
        Assert.Equal(FlightPhase.Takeoff, engine.Phase);
        Assert.Contains(events, x => x.Type == "OFF");

        Add(engine.Process(Snapshot(t.AddMinutes(18), false, 250, 5000, 1500, false, gearDown: false)), events);
        Assert.Equal(FlightPhase.Climb, engine.Phase);

        Add(engine.Process(Snapshot(t.AddMinutes(35), false, 430, 37000, 0, false, gearDown: false)), events);
        Assert.Equal(FlightPhase.Cruise, engine.Phase);

        Add(engine.Process(Snapshot(t.AddHours(1).AddMinutes(35), false, 410, 25000, -1200, false, gearDown: false)), events);
        Assert.Equal(FlightPhase.Descent, engine.Phase);

        Add(engine.Process(Snapshot(t.AddHours(1).AddMinutes(55), false, 190, 7000, -700, false, gearDown: true, flaps: 10)), events);
        Assert.Equal(FlightPhase.Approach, engine.Phase);

        Add(engine.Process(Snapshot(t.AddHours(2), false, 155, 2200, -600, false, gearDown: true, flaps: 25)), events);
        Assert.Equal(FlightPhase.Final, engine.Phase);

        var firstTouch = engine.Process(Snapshot(t.AddHours(2).AddMinutes(5), true, 132, 0, -310, false, touchdownRate: -310));
        Add(firstTouch, events);
        Assert.Contains(firstTouch.Events, x => x.Type == "TOUCHDOWN_FIRST");
        Assert.DoesNotContain(firstTouch.Events, x => x.Type == "ON");

        Add(engine.Process(Snapshot(t.AddHours(2).AddMinutes(5).AddSeconds(9), true, 90, 0, 0, false)), events);
        Assert.Contains(events, x => x.Type == "ON" && x.Value == -310);

        Add(engine.Process(Snapshot(t.AddHours(2).AddMinutes(6), true, 20, 0, 0, false)), events);
        Assert.Equal(FlightPhase.TaxiIn, engine.Phase);

        Add(engine.Process(Snapshot(t.AddHours(2).AddMinutes(8), true, 0, 0, 0, true)), events);
        Add(engine.Process(Snapshot(t.AddHours(2).AddMinutes(8).AddSeconds(16), true, 0, 0, 0, true)), events);
        Assert.Equal(FlightPhase.In, engine.Phase);
        Assert.Contains(events, x => x.Type == "IN");

        Assert.Single(events.Where(x => x.Type == "OUT"));
        Assert.Single(events.Where(x => x.Type == "OFF"));
        Assert.Single(events.Where(x => x.Type == "ON"));
        Assert.Single(events.Where(x => x.Type == "IN"));
    }

    [Fact]
    public void Reference_contract_keeps_operation_and_pirep_as_distinct_stable_ids()
    {
        const string operationId = "op_reference_itf749";
        const string pirepId = "pirep-reference";
        Assert.StartsWith("op_", operationId);
        Assert.NotEqual(operationId, pirepId);
    }

    [Fact]
    public void Recorder_refuses_completion_until_in_and_all_messages_are_acknowledged()
    {
        var recorder = new FlightRecorder(Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Tests", Guid.NewGuid().ToString("N")));
        var t = DateTimeOffset.Parse("2026-09-22T18:42:00Z");
        recorder.Start("https://promethee.example", "pirep-reference", Legacy(t, true, 0, 0, 0, true), "op_reference_itf749");

        Assert.Contains("IN", Assert.Throws<InvalidOperationException>(() => recorder.Complete()).Message);
        Assert.Empty(recorder.PendingEvents);

        recorder.Capture(Legacy(t.AddSeconds(1), true, 6, 0, 0, false));
        Assert.Equal("TAXI_OUT", recorder.Flight?.Phase);
        Assert.Contains(recorder.PendingEvents, x => x.Name == "OUT");

        recorder.Capture(Legacy(t.AddSeconds(2), false, 155, 40, 1300, false));
        recorder.Capture(Legacy(t.AddSeconds(3), false, 250, 5000, 1500, false, gearDown: false));
        recorder.Capture(Legacy(t.AddSeconds(4), false, 250, 2000, -800, false, gearDown: true, flaps: 20));
        Assert.Equal("FINAL", recorder.Flight?.Phase);

        recorder.Capture(Legacy(t.AddSeconds(5), true, 25, 0, -310, false, gearDown: true, flaps: 20, touchdownVelocity: -5.1667));
        Assert.Equal("LANDING", recorder.Flight?.Phase);
        Assert.DoesNotContain(recorder.PendingEvents, x => x.Name == "ON");

        recorder.Capture(Legacy(t.AddSeconds(14), true, 10, 0, 0, false, gearDown: true, flaps: 20));
        Assert.Contains(recorder.PendingEvents, x => x.Name == "ON");

        recorder.Capture(Legacy(t.AddSeconds(15), true, 0, 0, 0, true, gearDown: true));
        Assert.Equal("TAXI_IN", recorder.Flight?.Phase);
        recorder.Capture(Legacy(t.AddSeconds(31), true, 0, 0, 0, true, gearDown: true));
        Assert.Equal("IN", recorder.Flight?.Phase);

        Assert.Contains("synchroniser", Assert.Throws<InvalidOperationException>(() => recorder.Complete()).Message);

        recorder.AcknowledgePositions(recorder.Pending.Select(x => x.Sample.SampleId).ToArray());
        recorder.AcknowledgeEvents(recorder.PendingEvents.Select(x => x.EventId).ToArray());
        recorder.AcknowledgeFacts(recorder.PendingFacts.Select(x => x.FactId).ToArray());
        recorder.Complete();
        Assert.Null(recorder.Flight);
    }

    [Fact]
    public void Recorder_does_not_flag_takeoff_roll_as_taxi_overspeed()
    {
        var recorder = new FlightRecorder(Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Tests", Guid.NewGuid().ToString("N")));
        var t = DateTimeOffset.Parse("2026-09-22T18:42:00Z");
        recorder.Start("https://promethee.example", "pirep-roll", Legacy(t, true, 0, 0, 0, true));

        recorder.Capture(Legacy(t.AddSeconds(5), true, 12, 0, 0, false));
        recorder.Capture(Legacy(t.AddSeconds(10), true, 22, 0, 0, false));
        recorder.Capture(Legacy(t.AddSeconds(14), true, 42, 0, 0, false));

        Assert.Equal("TAKEOFF", recorder.Flight?.Phase);
        Assert.DoesNotContain(recorder.Flight!.Issues, x => x.Code == "TAXI_OVERSPEED");
    }

    [Fact]
    public void Recorder_uses_tracking_engine_phase_names_instead_of_legacy_enroute()
    {
        var recorder = new FlightRecorder(Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Tests", Guid.NewGuid().ToString("N")));
        var t = DateTimeOffset.Parse("2026-09-22T18:42:00Z");
        recorder.Start("https://promethee.example", "pirep-phases", Legacy(t, true, 0, 0, 0, true));

        recorder.Capture(Legacy(t.AddSeconds(1), true, 8, 0, 0, false));
        recorder.Capture(Legacy(t.AddSeconds(2), false, 150, 50, 1200, false));
        recorder.Capture(Legacy(t.AddSeconds(3), false, 250, 3000, 1400, false, gearDown: false));
        recorder.Capture(Legacy(t.AddSeconds(4), false, 430, 35000, 0, false, gearDown: false));

        Assert.Equal("CRUISE", recorder.Flight?.Phase);
        Assert.DoesNotContain(recorder.Flight!.Timeline, x => x.Name == "ENROUTE");
        Assert.Contains(recorder.Flight.Timeline, x => x.Name == "CLIMB");
        Assert.Contains(recorder.Flight.Timeline, x => x.Name == "CRUISE");
    }

    private static void Add(TrackingDecision decision, List<FlightEvent> events) => events.AddRange(decision.Events);

    private static Sample Legacy(
        DateTimeOffset time,
        bool onGround,
        double gs,
        double agl,
        double vs,
        bool parking,
        bool gearDown = true,
        double flaps = 0,
        double touchdownVelocity = 0) =>
        new(
            Guid.NewGuid(),
            time,
            48.7,
            2.3,
            agl + 300,
            agl,
            gs,
            gs,
            vs,
            180,
            8000,
            onGround,
            0,
            gearDown,
            touchdownVelocity,
            flaps,
            false,
            0,
            0,
            parking);

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
