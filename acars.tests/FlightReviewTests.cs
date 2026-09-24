using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class FlightReviewTests
{
    [Fact]
    public void Recorder_builds_review_with_approach_gates_and_landing_data()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Review-Tests", Guid.NewGuid().ToString("N"));
        var recorder = new FlightRecorder(folder);
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");

        recorder.Start("https://promethee.example", "pirep-review", S(t, true, 0, 0, 0, true));
        recorder.Capture(S(t.AddSeconds(5), true, 8, 0, 0, false));
        recorder.Capture(S(t.AddSeconds(10), false, 150, 80, 1300, false));
        recorder.Capture(S(t.AddSeconds(20), false, 250, 3500, 1500, false, gearDown: false));
        recorder.Capture(S(t.AddMinutes(10), false, 430, 35000, 0, false, gearDown: false));
        recorder.Capture(S(t.AddMinutes(20), false, 380, 9000, -900, false, gearDown: false));
        recorder.Capture(S(t.AddMinutes(22), false, 180, 2200, -700, false, gearDown: true, flaps: 20, bank: 10));
        recorder.Capture(S(t.AddMinutes(23), false, 160, 1200, -700, false, gearDown: true, flaps: 25, bank: 8));
        recorder.Capture(S(t.AddMinutes(23).AddSeconds(5), false, 155, 950, -800, false, gearDown: true, flaps: 25, bank: 12));
        recorder.Capture(S(t.AddMinutes(24), false, 150, 700, -800, false, gearDown: true, flaps: 30, bank: 10));
        recorder.Capture(S(t.AddMinutes(24).AddSeconds(5), false, 148, 480, -1450, false, gearDown: true, flaps: 30, bank: 38));
        recorder.Capture(S(t.AddMinutes(25), true, 125, 0, -220, false, gearDown: true, flaps: 30, bank: 0, touchdownVelocity: -3.6667));
        recorder.Capture(S(t.AddMinutes(25).AddSeconds(9), true, 80, 0, 0, false, gearDown: true, flaps: 30));
        recorder.Capture(S(t.AddMinutes(26), true, 15, 0, 0, false, gearDown: true));
        recorder.Capture(S(t.AddMinutes(27), true, 0, 0, 0, true, gearDown: true));
        recorder.Capture(S(t.AddMinutes(27).AddSeconds(16), true, 0, 0, 0, true, gearDown: true));

        var review = recorder.GetReview();

        Assert.NotNull(review);
        Assert.True(review!.ReadyToFile);
        Assert.Equal("IN", review.Phase);
        Assert.Equal("STABLE", review.Approach1000Status);
        Assert.Equal("UNSTABLE", review.Approach500Status);
        Assert.NotNull(review.LandingRate);
        Assert.Equal(-220d, review.LandingRate!.Value, 0);
        Assert.Contains(review.Observations, x => x.Code == "APPROACH_1000_STABLE");
        Assert.Contains(review.Observations, x => x.Code == "APPROACH_500_UNSTABLE");
        Assert.Contains(review.Observations, x => x.Code == "TOUCHDOWN");
    }

    [Fact]
    public void Connector_unknown_bank_remains_unknown_in_recorder_review()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Review-Tests", Guid.NewGuid().ToString("N"));
        var recorder = new FlightRecorder(folder);
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");

        recorder.Start("https://promethee.example", "pirep-unknown", A(t, true, 0, 0, 0, true, bank: null));
        recorder.Capture(A(t.AddSeconds(5), true, 8, 0, 0, false, bank: null));
        recorder.Capture(A(t.AddSeconds(10), false, 150, 80, 1300, false, bank: null));
        recorder.Capture(A(t.AddSeconds(20), false, 250, 3500, 1500, false, gearDown: false, bank: null));
        recorder.Capture(A(t.AddMinutes(10), false, 380, 9000, -900, false, gearDown: false, bank: null));
        recorder.Capture(A(t.AddMinutes(12), false, 180, 1200, -700, false, gearDown: true, flaps: 25, bank: null));
        recorder.Capture(A(t.AddMinutes(12).AddSeconds(5), false, 170, 950, -700, false, gearDown: true, flaps: 25, bank: null));

        var review = recorder.GetReview();

        Assert.NotNull(review);
        Assert.Equal("UNKNOWN", review!.Approach1000Status);
        Assert.Contains(review.Observations, x => x.Code == "APPROACH_1000_UNKNOWN");
    }

    [Fact]
    public void Fdm_observations_survive_crash_recovery()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Review-Tests", Guid.NewGuid().ToString("N"));
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");
        var first = new FlightRecorder(folder);

        first.Start("https://promethee.example", "pirep-recovery-review", S(t, true, 0, 0, 0, true));
        first.Capture(S(t.AddSeconds(5), true, 8, 0, 0, false));
        first.Capture(S(t.AddSeconds(10), false, 150, 80, 1300, false));
        first.Capture(S(t.AddSeconds(20), false, 250, 3500, 1500, false, gearDown: false));
        first.Capture(S(t.AddMinutes(10), false, 380, 9000, -900, false, gearDown: false));
        first.Capture(S(t.AddMinutes(12), false, 180, 1200, -700, false, gearDown: true, flaps: 25, bank: 8));
        first.Capture(S(t.AddMinutes(12).AddSeconds(5), false, 170, 950, -700, false, gearDown: true, flaps: 25, bank: 8));

        Assert.Contains(first.Flight!.Observations, x => x.Code == "APPROACH_1000_STABLE");

        var recovered = new FlightRecorder(folder);
        Assert.True(recovered.RecoveryAvailable);
        Assert.Contains(recovered.Flight!.Observations, x => x.Code == "APPROACH_1000_STABLE");

        recovered.Resume("https://promethee.example");
        var review = recovered.GetReview();
        Assert.Equal("STABLE", review!.Approach1000Status);
    }

    private static AircraftSnapshot A(
        DateTimeOffset time,
        bool onGround,
        double gs,
        double agl,
        double vs,
        bool parking,
        bool gearDown = true,
        double flaps = 0,
        double? bank = 0) =>
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
            BankDegrees: bank);

    private static Sample S(
        DateTimeOffset time,
        bool onGround,
        double gs,
        double agl,
        double vs,
        bool parking,
        bool gearDown = true,
        double flaps = 0,
        double bank = 0,
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
            bank,
            gearDown,
            touchdownVelocity,
            flaps,
            false,
            0,
            0,
            parking);
}
