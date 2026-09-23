using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class FlightRecorderRecoveryTests
{
    [Fact]
    public void Recorder_preserves_operation_identity_while_flight_is_active()
    {
        var recorder = NewRecorder();
        var sample = Ground(DateTimeOffset.Parse("2026-09-22T10:00:00Z"));
        recorder.Start("https://promethee.example", "pirep-42", sample, "op_bid-42");

        Assert.Equal("pirep-42", recorder.Flight?.PirepId);
        Assert.Equal("op_bid-42", recorder.Flight?.OperationId);
        Assert.NotEmpty(recorder.PendingEvents);
        Assert.Contains(recorder.PendingEvents, x => x.Name == "OUT");
    }

    [Fact]
    public void Acknowledgements_remove_only_the_confirmed_messages()
    {
        var recorder = NewRecorder();
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


    [Fact]
    public void Manual_pause_is_not_reported_as_crash_recovery()
    {
        var recorder = NewRecorder();
        recorder.Start("https://promethee.example", "pirep-pause", Ground(DateTimeOffset.Parse("2026-09-23T10:00:00Z")));
        recorder.Pause();

        Assert.False(recorder.RecoveryAvailable);
        Assert.Null(recorder.GetRecoveryInfo());
    }

    [Fact]
    public void Restart_exposes_recovery_and_resume_preserves_flight_identity()
    {
        var folder = NewFolder();
        var first = new FlightRecorder(folder);
        var t = DateTimeOffset.Parse("2026-09-23T10:00:00Z");
        first.Start("https://promethee.example", "pirep-recovery", Ground(t), "op-recovery");
        first.Capture(Ground(t.AddSeconds(20)) with { SampleId = Guid.NewGuid(), Gs = 7, ParkingBrake = false });

        var recovered = new FlightRecorder(folder);
        var info = recovered.GetRecoveryInfo();

        Assert.True(recovered.RecoveryAvailable);
        Assert.NotNull(info);
        Assert.Equal("pirep-recovery", info!.PirepId);
        Assert.Equal("op-recovery", info.OperationId);
        Assert.True(info.PendingMessages > 0);

        recovered.Resume("https://promethee.example");

        Assert.False(recovered.RecoveryAvailable);
        Assert.True(recovered.Flight?.Recording);
        Assert.Equal("pirep-recovery", recovered.Flight?.PirepId);
    }

    [Fact]
    public void Abandon_recovery_archives_state_before_clearing_session()
    {
        var folder = NewFolder();
        var first = new FlightRecorder(folder);
        first.Start("https://promethee.example", "pirep-abandon", Ground(DateTimeOffset.Parse("2026-09-23T10:00:00Z")));

        var recovered = new FlightRecorder(folder);
        Assert.True(recovered.RecoveryAvailable);

        recovered.AbandonRecovery();

        Assert.False(recovered.RecoveryAvailable);
        Assert.Null(recovered.Flight);
        Assert.Empty(recovered.Pending);
        Assert.Empty(recovered.PendingEvents);
        Assert.Single(Directory.GetFiles(Path.Combine(folder, "recovery-archive"), "abandoned-*.json"));
    }

    private static string NewFolder() =>
        Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Tests", Guid.NewGuid().ToString("N"));

    private static FlightRecorder NewRecorder() => new(NewFolder());

    private static Sample Ground(DateTimeOffset at) =>
        new(Guid.NewGuid(), at, 48.7, 2.3, 300, 0, 0, 0, 0, 180, 8000, true,
            0, true, 0, 0, false, 0, 0, true);
}
