using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class LegacyScoringTelemetryTests
{
    [Fact]
    public void Simconnect_sample_preserves_signals_required_by_legacy_scoring()
    {
        var sample = new Sample(
            Guid.NewGuid(),
            DateTimeOffset.Parse("2026-10-03T00:30:00Z"),
            48.7, 2.3, 12000, 11700, 260, 270, 0, 180, 8000,
            false, 12, false, -4.5, 0, true, 0, 0, false,
            true, true, true, true, false, false, false, 1, 2,
            GForce: 2.2,
            OverspeedWarning: true,
            StallWarning: true,
            Reverse1Percent: 75,
            Reverse2Percent: 72);

        var snapshot = sample.ToSnapshot();

        Assert.Equal(2.2, snapshot.GForce);
        Assert.True(snapshot.OverspeedWarning);
        Assert.True(snapshot.StallWarning);
        Assert.Equal(new[] { 75d, 72d, 0d, 0d }, snapshot.ThrustReverserPercent);
        Assert.True(snapshot.BeaconLight);
        Assert.True(snapshot.LandingLight);
        Assert.Contains(true, snapshot.EnginesRunning!);
    }
}
