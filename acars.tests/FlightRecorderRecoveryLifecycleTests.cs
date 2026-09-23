using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class FlightRecorderRecoveryLifecycleTests
{
    [Fact]
    public void Restart_exposes_recoverable_flight_without_auto_resuming()
    {
        var folder = NewFolder();
        var started = DateTimeOffset.Parse("2026-09-23T10:00:00Z");

        var first = new FlightRecorder(folder);
        first.Start("https://promethee.airinter-va.org", "pirep-100", Ground(started), "op-100");
        first.Capture(Ground(started.AddSeconds(20)) with { SampleId = Guid.NewGuid(), Gs = 6, ParkingBrake = false });

        var restarted = new FlightRecorder(folder);
        var recovery = restarted.GetRecoverySnapshot();

        Assert.NotNull(recovery);
        Assert.False(restarted.Flight?.Recording);
        Assert.Equal("pirep-100", recovery!.PirepId);
        Assert.Equal("op-100", recovery.OperationId);
        Assert.True(recovery.PendingPositions > 0);
        Assert.True(recovery.PendingEvents > 0);
        Assert.NotEmpty(recovery.Timeline);
    }

    [Fact]
    public void Resume_keeps_identity_and_pending_messages()
    {
        var folder = NewFolder();
        var started = DateTimeOffset.Parse("2026-09-23T10:00:00Z");
        var first = new FlightRecorder(folder);
        first.Start("https://promethee.airinter-va.org", "pirep-101", Ground(started), "op-101");

        var restarted = new FlightRecorder(folder);
        var positions = restarted.Pending.Count;
        var events = restarted.PendingEvents.Count;

        restarted.Resume("https://promethee.airinter-va.org");

        Assert.True(restarted.Flight?.Recording);
        Assert.Equal("pirep-101", restarted.Flight?.PirepId);
        Assert.Equal("op-101", restarted.Flight?.OperationId);
        Assert.Equal(positions, restarted.Pending.Count);
        Assert.Equal(events, restarted.PendingEvents.Count);
    }

    [Fact]
    public void Abandon_archives_summary_and_clears_unsent_flight_state()
    {
        var folder = NewFolder();
        var started = DateTimeOffset.Parse("2026-09-23T10:00:00Z");
        var first = new FlightRecorder(folder);
        first.Start("https://promethee.airinter-va.org", "pirep-102", Ground(started), "op-102");

        var restarted = new FlightRecorder(folder);
        restarted.Abandon();

        Assert.Null(restarted.Flight);
        Assert.Empty(restarted.Pending);
        Assert.Empty(restarted.PendingEvents);
        Assert.Empty(restarted.Track);
        Assert.Contains(restarted.History, item => item.PirepId == "pirep-102" && item.Status == "ABANDONED");

        var afterSecondRestart = new FlightRecorder(folder);
        Assert.Null(afterSecondRestart.GetRecoverySnapshot());
        Assert.Contains(afterSecondRestart.History, item => item.PirepId == "pirep-102" && item.Status == "ABANDONED");
    }

    private static string NewFolder() =>
        Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Recovery-Tests", Guid.NewGuid().ToString("N"));

    private static Sample Ground(DateTimeOffset at) =>
        new(Guid.NewGuid(), at, 48.7, 2.3, 300, 0, 0, 0, 0, 180, 8000, true,
            0, true, 0, 0, false, 0, 0, true);
}
