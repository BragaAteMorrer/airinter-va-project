using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class PartialTelemetryRecorderTests
{
    [Fact]
    public void Recorder_starts_with_unknown_optional_aircraft_systems()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Capability-Tests", Guid.NewGuid().ToString("N"));
        var recorder = new FlightRecorder(folder);
        var snapshot = CoreSnapshot(DateTimeOffset.Parse("2026-09-24T20:00:00Z"));

        recorder.Start("https://promethee.example", "pirep-partial", snapshot);

        Assert.NotNull(recorder.Flight);
        Assert.True(recorder.Flight!.Recording);
        var pending = Assert.Single(recorder.Pending);
        Assert.NotNull(pending.Snapshot);
        Assert.Null(pending.Snapshot!.GearDown);
        Assert.Null(pending.Snapshot.FlapsPercent);
        Assert.Null(pending.Snapshot.ParkingBrake);
        Assert.Null(pending.Snapshot.BankDegrees);
    }

    [Fact]
    public void Unknown_gear_does_not_create_false_gear_up_issue()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Capability-Tests", Guid.NewGuid().ToString("N"));
        var recorder = new FlightRecorder(folder);
        var t = DateTimeOffset.Parse("2026-09-24T20:00:00Z");

        recorder.Start("https://promethee.example", "pirep-partial-final", CoreSnapshot(t));
        recorder.Capture(CoreSnapshot(t.AddSeconds(5)) with { GroundSpeedKnots = 10, ParkingBrake = false });
        recorder.Capture(CoreSnapshot(t.AddSeconds(10)) with { OnGround = false, GroundSpeedKnots = 150, AltitudeAglFeet = 80, VerticalSpeedFeetPerMinute = 1300 });
        recorder.Capture(CoreSnapshot(t.AddSeconds(20)) with { OnGround = false, GroundSpeedKnots = 220, AltitudeAglFeet = 3000, VerticalSpeedFeetPerMinute = 1500 });
        recorder.Capture(CoreSnapshot(t.AddMinutes(5)) with { OnGround = false, GroundSpeedKnots = 190, AltitudeAglFeet = 1800, VerticalSpeedFeetPerMinute = -900 });
        recorder.Capture(CoreSnapshot(t.AddMinutes(6)) with { OnGround = false, GroundSpeedKnots = 160, AltitudeAglFeet = 900, VerticalSpeedFeetPerMinute = -700, GearDown = null });

        Assert.DoesNotContain(recorder.Flight!.Issues, issue => issue.Code == "GEAR_UP_FINAL");
    }

    private static AircraftSnapshot CoreSnapshot(DateTimeOffset time) =>
        new(
            Guid.NewGuid(),
            time,
            Latitude: 48.7,
            Longitude: 2.3,
            AltitudeMslFeet: 300,
            AltitudeAglFeet: 0,
            IndicatedAirspeedKnots: 0,
            GroundSpeedKnots: 0,
            VerticalSpeedFeetPerMinute: 0,
            HeadingDegrees: 180,
            FuelWeight: 8000,
            OnGround: true,
            ParkingBrake: null,
            GearDown: null,
            FlapsPercent: null,
            BankDegrees: null,
            AircraftTitle: "Partial Systems Test");
}
