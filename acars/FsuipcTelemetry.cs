namespace Promethee;

/// <summary>
/// Raw-but-normalized frame produced by the FSUIPC adapter. Units are already
/// converted to those used by AircraftSnapshot so the ACARS core never needs
/// to know about offsets or FSUIPC fixed-point encodings.
/// </summary>
public sealed record FsuipcTelemetryFrame(
    DateTimeOffset RecordedAt,
    double Latitude,
    double Longitude,
    double AltitudeMslFeet,
    double AltitudeAglFeet,
    double IndicatedAirspeedKnots,
    double GroundSpeedKnots,
    double VerticalSpeedFeetPerMinute,
    double HeadingDegrees,
    double PitchDegrees,
    double BankDegrees,
    double FuelWeightPounds,
    bool OnGround,
    bool ParkingBrake,
    bool GearDown,
    double FlapsPercent,
    IReadOnlyList<bool> EnginesRunning,
    bool SlewActive,
    bool BeaconLight,
    bool NavigationLight,
    bool StrobeLight,
    bool LandingLight,
    bool TaxiLight,
    bool SpoilersArmed,
    string? AircraftTitle,
    double? GrossWeightPounds = null,
    bool? SeatBeltSign = null,
    bool? DoorsOpen = null,
    int? TransponderCode = null,
    bool? AutopilotEnabled = null,
    bool? Paused = null,
    double? SimulationRate = null,
    bool? ThrustStable = null,
    string? AircraftIcao = null,
    string? AircraftModel = null);

/// <summary>
/// Small seam around the Windows-only FSUIPC client. It keeps the simulator
/// connector testable on CI without requiring a running simulator.
/// </summary>
public interface IFsuipcSession : IDisposable
{
    bool IsOpen { get; }
    void Open();
    FsuipcTelemetryFrame Read();
    void Close();
}

public static class FsuipcTelemetryMapper
{
    public static AircraftSnapshot ToSnapshot(FsuipcTelemetryFrame frame) => new(
        Guid.NewGuid(),
        frame.RecordedAt,
        Latitude: frame.Latitude,
        Longitude: frame.Longitude,
        AltitudeMslFeet: frame.AltitudeMslFeet,
        AltitudeAglFeet: frame.AltitudeAglFeet,
        IndicatedAirspeedKnots: frame.IndicatedAirspeedKnots,
        GroundSpeedKnots: frame.GroundSpeedKnots,
        HeadingDegrees: frame.HeadingDegrees,
        TrackDegrees: frame.HeadingDegrees,
        VerticalSpeedFeetPerMinute: frame.VerticalSpeedFeetPerMinute,
        OnGround: frame.OnGround,
        ParkingBrake: frame.ParkingBrake,
        EnginesRunning: frame.EnginesRunning,
        FuelWeight: frame.FuelWeightPounds,
        GearDown: frame.GearDown,
        FlapsPercent: frame.FlapsPercent,
        SpoilersArmed: frame.SpoilersArmed,
        BeaconLight: frame.BeaconLight,
        NavigationLight: frame.NavigationLight,
        StrobeLight: frame.StrobeLight,
        LandingLight: frame.LandingLight,
        TaxiLight: frame.TaxiLight,
        SlewActive: frame.SlewActive,
        Paused: frame.Paused,
        PauseKind: frame.Paused == true ? "PAUSE" : null,
        SimulationRate: frame.SimulationRate,
        AircraftTitle: frame.AircraftTitle,
        AircraftIcao: frame.AircraftIcao,
        AircraftModel: frame.AircraftModel,
        PitchDegrees: frame.PitchDegrees,
        BankDegrees: frame.BankDegrees,
        GrossWeight: frame.GrossWeightPounds,
        SeatBeltSign: frame.SeatBeltSign,
        DoorsOpen: frame.DoorsOpen,
        TransponderCode: frame.TransponderCode,
        AutopilotEnabled: frame.AutopilotEnabled,
        ThrustStable: frame.ThrustStable,
        TouchdownVerticalSpeedFeetPerMinute: frame.VerticalSpeedFeetPerMinute);
}
