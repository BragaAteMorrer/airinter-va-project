namespace Promethee;

/// <summary>Simulator families known by the ACARS core. A value is not a support claim.</summary>
public enum SimulatorKind
{
    Unknown,
    MicrosoftFlightSimulator,
    FlightSimulatorX,
    Prepar3D,
    FlightSimulator2004,
    XPlane
}

public enum SimulatorConnectionState { NotDetected, Detected, Connecting, Connected, Faulted }

/// <summary>Features a connector has actually observed or can expose.</summary>
[Flags]
public enum SimulatorCapabilities
{
    None = 0, Position = 1, FlightDynamics = 2, Fuel = 4, AircraftSystems = 8,
    Engines = 16, Lights = 32, SimulatorControls = 64, Weather = 128
}

public sealed record SimulatorDescriptor(
    SimulatorKind Kind, string DisplayName, string ConnectorId,
    SimulatorCapabilities Capabilities, bool IsExperimental = false);

/// <summary>
/// A simulator-neutral observation. Nullable values mean "not supplied by this
/// connector or aircraft", never zero or false by assumption.
/// </summary>
public sealed record AircraftSnapshot(
    Guid SampleId,
    DateTimeOffset RecordedAt,
    double? Latitude = null,
    double? Longitude = null,
    double? AltitudeMslFeet = null,
    double? AltitudeAglFeet = null,
    double? IndicatedAirspeedKnots = null,
    double? GroundSpeedKnots = null,
    double? Mach = null,
    double? HeadingDegrees = null,
    double? TrackDegrees = null,
    double? VerticalSpeedFeetPerMinute = null,
    double? QnhHpa = null,
    double? OutsideAirTemperatureCelsius = null,
    double? WindSpeedKnots = null,
    double? WindDirectionDegrees = null,
    bool? OnGround = null,
    bool? ParkingBrake = null,
    IReadOnlyList<bool>? EnginesRunning = null,
    double? FuelWeight = null,
    double? GrossWeight = null,
    bool? GearDown = null,
    double? FlapsPercent = null,
    bool? SpoilersArmed = null,
    bool? BeaconLight = null,
    bool? NavigationLight = null,
    bool? StrobeLight = null,
    bool? LandingLight = null,
    bool? TaxiLight = null,
    bool? SeatBeltSign = null,
    bool? DoorsOpen = null,
    int? TransponderCode = null,
    bool? AutopilotEnabled = null,
    bool? SlewActive = null,
    bool? Paused = null,
    double? SimulationRate = null,
    string? AircraftTitle = null,
    string? AircraftIcao = null);

/// <summary>
/// Boundary between the ACARS core and simulator-specific code. Connectors must
/// never make HTTP calls or assign business penalties.
/// </summary>
public interface ISimulatorConnector : IDisposable
{
    SimulatorDescriptor Descriptor { get; }
    SimulatorConnectionState ConnectionState { get; }
    string Status { get; }
    AircraftSnapshot? LatestSnapshot { get; }
    event Action<AircraftSnapshot>? SnapshotReceived;
    void Poll();
}

/// <summary>Maps the existing SimConnect sample without losing legacy behaviour.</summary>
public static class SimulatorSnapshotMapper
{
    public static AircraftSnapshot ToSnapshot(this Sample sample) => new(
        sample.SampleId, sample.RecordedAt,
        Latitude: sample.Lat, Longitude: sample.Lon, AltitudeMslFeet: sample.Altitude,
        AltitudeAglFeet: sample.Agl, IndicatedAirspeedKnots: sample.Ias,
        GroundSpeedKnots: sample.Gs, HeadingDegrees: sample.Heading,
        TrackDegrees: sample.Heading, VerticalSpeedFeetPerMinute: sample.Vs,
        OnGround: sample.OnGround, ParkingBrake: sample.ParkingBrake,
        FuelWeight: sample.Fuel, GearDown: sample.GearDown, FlapsPercent: sample.Flaps,
        BeaconLight: sample.BeaconLight, LandingLight: sample.LandingLight,
        EnginesRunning: [sample.Engine1Running, sample.Engine2Running, sample.Engine3Running, sample.Engine4Running],
        SlewActive: sample.SlewActive, SimulationRate: sample.SimulationRate);
}
