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
        Assert.True(review.Profile.Count >= 6);
        Assert.Equal(300d, review.Profile.First().Altitude, 0);
        Assert.Contains(review.Profile, point => point.Altitude >= 35000);
    }

    [Fact]
    public void Review_profile_is_sampled_with_altitude_and_fuel_and_survives_recovery()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Review-Tests", Guid.NewGuid().ToString("N"));
        var t = DateTimeOffset.Parse("2026-09-24T18:00:00Z");
        var first = new FlightRecorder(folder);

        first.Start("https://promethee.example", "pirep-profile",
            S(t, true, 0, 0, 0, true) with { Fuel = 10000 });
        first.Capture(S(t.AddSeconds(31), true, 10, 0, 0, false) with { Fuel = 9800 });
        first.Capture(S(t.AddSeconds(62), false, 180, 2500, 1200, false, gearDown: false) with { Fuel = 9400 });
        first.Capture(S(t.AddSeconds(93), false, 300, 12000, 1400, false, gearDown: false) with { Fuel = 9000 });

        var review = first.GetReview();
        Assert.NotNull(review);
        Assert.True(review!.Profile.Count >= 4);
        Assert.Equal(10000d, review.Profile.First().Fuel, 0);
        Assert.Equal(9000d, review.Profile.Last().Fuel, 0);
        Assert.True(review.Profile.Last().Altitude > review.Profile.First().Altitude);

        var recovered = new FlightRecorder(folder);
        Assert.True(recovered.RecoveryAvailable);
        var recoveredReview = recovered.GetReview();
        Assert.NotNull(recoveredReview);
        Assert.Equal(review.Profile.Count, recoveredReview!.Profile.Count);
        Assert.Equal(review.Profile.Last().Fuel, recoveredReview.Profile.Last().Fuel);
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
    public void Paused_intervals_are_excluded_from_airborne_time_and_shown_in_review()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Review-Tests", Guid.NewGuid().ToString("N"));
        var recorder = new FlightRecorder(folder);
        var t = DateTimeOffset.Parse("2026-10-03T20:00:00Z");

        recorder.Start("https://promethee.example", "pirep-pause", A(t, true, 0, 0, 0, true));
        recorder.Capture(A(t.AddSeconds(1), true, 8, 0, 0, false));
        recorder.Capture(A(t.AddSeconds(2), false, 150, 80, 1200, false));
        recorder.Capture(A(t.AddSeconds(3), false, 180, 500, 1200, false, gearDown: false));
        recorder.Capture(A(t.AddSeconds(4), false, 180, 600, 0, false, gearDown: false, paused: true, pauseKind: "ACTIVE_PAUSE"));
        recorder.Capture(A(t.AddSeconds(9), false, 180, 600, 0, false, gearDown: false, paused: true, pauseKind: "ACTIVE_PAUSE"));
        recorder.Capture(A(t.AddSeconds(10), false, 180, 600, 0, false, gearDown: false, paused: false));
        recorder.Capture(A(t.AddSeconds(11), false, 180, 600, 0, false, gearDown: false, paused: false));

        var review = recorder.GetReview();

        Assert.NotNull(review);
        Assert.Equal(1, review!.PauseCount);
        Assert.Equal(6, review.PausedSeconds);
        Assert.Equal(3d, recorder.Flight!.AirborneSeconds);
        Assert.Contains(review.Observations, x => x.Code == "PAUSE" && x.Status == "ACTIVE_PAUSE");
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
        double? bank = 0,
        bool? paused = false,
        string? pauseKind = null) =>
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
            BankDegrees: bank,
            Paused: paused,
            PauseKind: pauseKind);

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
