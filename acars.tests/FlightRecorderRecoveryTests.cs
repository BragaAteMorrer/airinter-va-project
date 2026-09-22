using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class FlightRecorderRecoveryTests
{
    [Fact]
    public void Recorder_preserves_operation_identity_while_flight_is_active()
    {
        var recorder = new FlightRecorder();
        var sample = Ground(DateTimeOffset.Parse("2026-09-22T10:00:00Z"));
        recorder.Start("https://promethee.example", "pirep-42", sample, "op_bid-42");

        Assert.Equal("pirep-42", recorder.Flight?.PirepId);
        Assert.Equal("op_bid-42", recorder.Flight?.OperationId);
        Assert.NotEmpty(recorder.PendingEvents);
        Assert.Contains(recorder.PendingEvents, x => x.Name == "OUT");
        recorder.AcknowledgePositions(recorder.Pending.Select(x => x.Sample.SampleId).ToArray());
        recorder.AcknowledgeEvents(recorder.PendingEvents.Select(x => x.EventId).ToArray());
        recorder.Pause();
        File.Delete(Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "AirInter", "Promethee", "state.json"));
    }

    [Fact]
    public void Acknowledgements_remove_only_the_confirmed_messages()
    {
        var recorder = new FlightRecorder();
        var t = DateTimeOffset.Parse("2026-09-22T10:00:00Z");
        recorder.Start("https://promethee.example", "pirep-42", Ground(t), "op_bid-42");
        recorder.Capture(Ground(t.AddSeconds(20)) with { SampleId = Guid.NewGuid(), Gs = 5, ParkingBrake = false });

        var keepPosition = recorder.Pending.Last().Sample.SampleId;
        var ackPosition = recorder.Pending.First().Sample.SampleId;
        recorder.AcknowledgePositions([ackPosition]);
        Assert.DoesNotContain(recorder.Pending, x => x.Sample.SampleId == ackPosition);
        Assert.Contains(recorder.Pending, x => x.Sample.SampleId == keepPosition);

        var keepEvent = recorder.PendingEvents.Last().EventId;
        var ackEvent = recorder.PendingEvents.First().EventId;
        recorder.AcknowledgeEvents([ackEvent]);
        Assert.DoesNotContain(recorder.PendingEvents, x => x.EventId == ackEvent);
        if (keepEvent != ackEvent) Assert.Contains(recorder.PendingEvents, x => x.EventId == keepEvent);
    }

    private static Sample Ground(DateTimeOffset at) =>
        new(Guid.NewGuid(), at, 48.7, 2.3, 300, 0, 0, 0, 0, 180, 8000, true,
            0, true, 0, 0, false, 0, 0, true);
}
