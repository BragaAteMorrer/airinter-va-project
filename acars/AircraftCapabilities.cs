using System.Text.Json.Serialization;

namespace Promethee;

[JsonConverter(typeof(JsonStringEnumConverter))]
public enum CapabilityAvailability
{
    Unknown,
    Supported,
    Unsupported
}

[JsonConverter(typeof(JsonStringEnumConverter))]
public enum AircraftDataCapability
{
    Position,
    AltitudeMsl,
    AltitudeAgl,
    IndicatedAirspeed,
    GroundSpeed,
    VerticalSpeed,
    Heading,
    Track,
    Pitch,
    Bank,
    Fuel,
    GrossWeight,
    OnGround,
    ParkingBrake,
    Gear,
    Flaps,
    Spoilers,
    Engines,
    BeaconLight,
    NavigationLight,
    StrobeLight,
    LandingLight,
    TaxiLight,
    SeatBeltSign,
    Doors,
    Transponder,
    Autopilot,
    ThrustStable,
    Slew,
    Pause,
    SimulationRate,
    TouchdownRate,
    AircraftTitle,
    AircraftIcao,
    AircraftModel
}

public sealed record AircraftCapabilityState(
    AircraftDataCapability Capability,
    CapabilityAvailability Availability,
    string Source);

public sealed record AircraftCapabilityReport(
    string AdapterId,
    string AdapterName,
    string AircraftLabel,
    string? AircraftTitle,
    string? AircraftIcao,
    string? AircraftModel,
    string ConnectorId,
    SimulatorKind Simulator,
    DateTimeOffset UpdatedAt,
    IReadOnlyList<AircraftCapabilityState> Capabilities)
{
    public CapabilityAvailability Get(AircraftDataCapability capability) =>
        Capabilities.FirstOrDefault(x => x.Capability == capability)?.Availability
        ?? CapabilityAvailability.Unknown;

    public int SupportedCount => Capabilities.Count(x => x.Availability == CapabilityAvailability.Supported);
    public int UnknownCount => Capabilities.Count(x => x.Availability == CapabilityAvailability.Unknown);
    public int UnsupportedCount => Capabilities.Count(x => x.Availability == CapabilityAvailability.Unsupported);
}

/// <summary>
/// Aircraft-specific seam. Adapters may normalize vendor-specific data later,
/// but they must never invent a value that was not actually observed.
/// </summary>
public interface IAircraftAdapter
{
    string Id { get; }
    string DisplayName { get; }
    int Priority { get; }

    bool Matches(AircraftSnapshot snapshot);

    AircraftSnapshot Normalize(AircraftSnapshot snapshot) => snapshot;

    CapabilityAvailability? OverrideCapability(
        AircraftDataCapability capability,
        SimulatorDescriptor connector,
        AircraftSnapshot snapshot) => null;
}

public sealed class GenericAircraftAdapter : IAircraftAdapter
{
    public string Id => "generic";
    public string DisplayName => "Generic aircraft";
    public int Priority => int.MinValue;
    public bool Matches(AircraftSnapshot snapshot) => true;
}

public sealed class NamedAircraftAdapter(
    string id,
    string displayName,
    int priority,
    params string[] titleTokens) : IAircraftAdapter
{
    public string Id { get; } = id;
    public string DisplayName { get; } = displayName;
    public int Priority { get; } = priority;

    public bool Matches(AircraftSnapshot snapshot)
    {
        var haystack = string.Join(" ", snapshot.AircraftTitle, snapshot.AircraftIcao, snapshot.AircraftModel).Trim();
        if (string.IsNullOrWhiteSpace(haystack)) return false;
        return titleTokens.All(token => haystack.Contains(token, StringComparison.OrdinalIgnoreCase));
    }
}

public sealed class AircraftAdapterRegistry
{
    private readonly IReadOnlyList<IAircraftAdapter> adapters;

    public AircraftAdapterRegistry(IEnumerable<IAircraftAdapter>? adapters = null)
    {
        this.adapters = (adapters ?? DefaultAdapters())
            .OrderByDescending(x => x.Priority)
            .ToArray();
    }

    public IAircraftAdapter Resolve(AircraftSnapshot snapshot) =>
        adapters.FirstOrDefault(x => x.Matches(snapshot))
        ?? new GenericAircraftAdapter();

    private static IEnumerable<IAircraftAdapter> DefaultAdapters()
    {
        // Identification only for now. Capabilities are still based on observed
        // telemetry and connector contracts, never on the product name.
        yield return new NamedAircraftAdapter("fenix-a319", "Fenix A319", 120, "Fenix", "A319");
        yield return new NamedAircraftAdapter("fenix-a321", "Fenix A321", 120, "Fenix", "A321");
        yield return new NamedAircraftAdapter("fenix-a320", "Fenix A320", 110, "Fenix", "A320");
        yield return new NamedAircraftAdapter("fbw-a32nx", "FlyByWire A32NX", 105, "FlyByWire");
        yield return new NamedAircraftAdapter("inibuilds-a320", "iniBuilds A320neo", 100, "iniBuilds", "A320");
        yield return new NamedAircraftAdapter("inibuilds-a310", "iniBuilds A310", 100, "iniBuilds", "A310");
        yield return new NamedAircraftAdapter("pmdg", "PMDG aircraft", 90, "PMDG");
        yield return new NamedAircraftAdapter("fslabs", "Flight Sim Labs aircraft", 90, "FSLabs");
        yield return new NamedAircraftAdapter("tfdi-md11", "TFDi MD-11", 90, "TFDi", "MD-11");
        yield return new GenericAircraftAdapter();
    }
}

/// <summary>
/// Builds an aircraft-level capability matrix from what Hermès has really
/// observed. SUPPORTED means seen at least once. UNSUPPORTED is used only when
/// the active connector explicitly does not expose the required data family.
/// Everything else remains UNKNOWN.
/// </summary>
public sealed class AircraftCapabilityMonitor
{
    private readonly AircraftAdapterRegistry registry;
    private readonly HashSet<AircraftDataCapability> observed = [];
    private string? currentIdentity;

    public AircraftCapabilityMonitor(AircraftAdapterRegistry? registry = null)
    {
        this.registry = registry ?? new AircraftAdapterRegistry();
    }

    public AircraftCapabilityReport Observe(
        SimulatorDescriptor connector,
        AircraftSnapshot rawSnapshot) =>
        AdaptAndObserve(connector, rawSnapshot).Report;

    public (AircraftSnapshot Snapshot, AircraftCapabilityReport Report) AdaptAndObserve(
        SimulatorDescriptor connector,
        AircraftSnapshot rawSnapshot)
    {
        var adapter = registry.Resolve(rawSnapshot);
        var snapshot = adapter.Normalize(rawSnapshot);
        var identity = IdentityKey(connector, adapter, snapshot);

        if (!string.Equals(identity, currentIdentity, StringComparison.Ordinal))
        {
            observed.Clear();
            currentIdentity = identity;
        }

        foreach (var capability in Enum.GetValues<AircraftDataCapability>())
            if (HasValue(snapshot, capability))
                observed.Add(capability);

        var states = Enum.GetValues<AircraftDataCapability>()
            .Select(capability => new AircraftCapabilityState(
                capability,
                ResolveAvailability(adapter, connector, snapshot, capability),
                ResolveSource(adapter, connector, snapshot, capability)))
            .ToArray();

        var report = new AircraftCapabilityReport(
            adapter.Id,
            adapter.DisplayName,
            BuildAircraftLabel(snapshot),
            snapshot.AircraftTitle,
            snapshot.AircraftIcao,
            snapshot.AircraftModel,
            connector.ConnectorId,
            connector.Kind,
            snapshot.RecordedAt,
            states);
        return (snapshot, report);
    }

    public void Reset()
    {
        currentIdentity = null;
        observed.Clear();
    }

    private CapabilityAvailability ResolveAvailability(
        IAircraftAdapter adapter,
        SimulatorDescriptor connector,
        AircraftSnapshot snapshot,
        AircraftDataCapability capability)
    {
        var overridden = adapter.OverrideCapability(capability, connector, snapshot);
        if (overridden is not null) return overridden.Value;
        if (observed.Contains(capability)) return CapabilityAvailability.Supported;

        var requiredFamily = RequiredConnectorCapability(capability);
        if (requiredFamily != SimulatorCapabilities.None
            && !connector.Capabilities.HasFlag(requiredFamily))
            return CapabilityAvailability.Unsupported;

        return CapabilityAvailability.Unknown;
    }

    private string ResolveSource(
        IAircraftAdapter adapter,
        SimulatorDescriptor connector,
        AircraftSnapshot snapshot,
        AircraftDataCapability capability)
    {
        if (adapter.OverrideCapability(capability, connector, snapshot) is not null)
            return "aircraft-adapter";
        if (observed.Contains(capability)) return "observed";
        var requiredFamily = RequiredConnectorCapability(capability);
        if (requiredFamily != SimulatorCapabilities.None
            && !connector.Capabilities.HasFlag(requiredFamily))
            return "connector-not-exposed";
        return "not-observed";
    }

    private static string IdentityKey(
        SimulatorDescriptor connector,
        IAircraftAdapter adapter,
        AircraftSnapshot snapshot) =>
        string.Join("|",
            connector.ConnectorId,
            connector.Kind,
            adapter.Id,
            snapshot.AircraftTitle?.Trim() ?? "",
            snapshot.AircraftIcao?.Trim() ?? "",
            snapshot.AircraftModel?.Trim() ?? "");

    private static string BuildAircraftLabel(AircraftSnapshot snapshot)
    {
        if (!string.IsNullOrWhiteSpace(snapshot.AircraftIcao)
            && !string.IsNullOrWhiteSpace(snapshot.AircraftTitle))
            return $"{snapshot.AircraftIcao} · {snapshot.AircraftTitle}";
        if (!string.IsNullOrWhiteSpace(snapshot.AircraftModel)
            && !string.IsNullOrWhiteSpace(snapshot.AircraftTitle))
            return $"{snapshot.AircraftModel} · {snapshot.AircraftTitle}";
        return snapshot.AircraftTitle
            ?? snapshot.AircraftIcao
            ?? snapshot.AircraftModel
            ?? "Aircraft identity unknown";
    }

    private static bool HasValue(AircraftSnapshot s, AircraftDataCapability capability) => capability switch
    {
        AircraftDataCapability.Position => s.Latitude is not null && s.Longitude is not null,
        AircraftDataCapability.AltitudeMsl => s.AltitudeMslFeet is not null,
        AircraftDataCapability.AltitudeAgl => s.AltitudeAglFeet is not null,
        AircraftDataCapability.IndicatedAirspeed => s.IndicatedAirspeedKnots is not null,
        AircraftDataCapability.GroundSpeed => s.GroundSpeedKnots is not null,
        AircraftDataCapability.VerticalSpeed => s.VerticalSpeedFeetPerMinute is not null,
        AircraftDataCapability.Heading => s.HeadingDegrees is not null,
        AircraftDataCapability.Track => s.TrackDegrees is not null,
        AircraftDataCapability.Pitch => s.PitchDegrees is not null,
        AircraftDataCapability.Bank => s.BankDegrees is not null,
        AircraftDataCapability.Fuel => s.FuelWeight is not null,
        AircraftDataCapability.GrossWeight => s.GrossWeight is not null,
        AircraftDataCapability.OnGround => s.OnGround is not null,
        AircraftDataCapability.ParkingBrake => s.ParkingBrake is not null,
        AircraftDataCapability.Gear => s.GearDown is not null,
        AircraftDataCapability.Flaps => s.FlapsPercent is not null,
        AircraftDataCapability.Spoilers => s.SpoilersArmed is not null,
        AircraftDataCapability.Engines => s.EnginesRunning is not null,
        AircraftDataCapability.BeaconLight => s.BeaconLight is not null,
        AircraftDataCapability.NavigationLight => s.NavigationLight is not null,
        AircraftDataCapability.StrobeLight => s.StrobeLight is not null,
        AircraftDataCapability.LandingLight => s.LandingLight is not null,
        AircraftDataCapability.TaxiLight => s.TaxiLight is not null,
        AircraftDataCapability.SeatBeltSign => s.SeatBeltSign is not null,
        AircraftDataCapability.Doors => s.DoorsOpen is not null,
        AircraftDataCapability.Transponder => s.TransponderCode is not null,
        AircraftDataCapability.Autopilot => s.AutopilotEnabled is not null,
        AircraftDataCapability.ThrustStable => s.ThrustStable is not null,
        AircraftDataCapability.Slew => s.SlewActive is not null,
        AircraftDataCapability.Pause => s.Paused is not null,
        AircraftDataCapability.SimulationRate => s.SimulationRate is not null,
        AircraftDataCapability.TouchdownRate => s.TouchdownVerticalSpeedFeetPerMinute is not null,
        AircraftDataCapability.AircraftTitle => !string.IsNullOrWhiteSpace(s.AircraftTitle),
        AircraftDataCapability.AircraftIcao => !string.IsNullOrWhiteSpace(s.AircraftIcao),
        AircraftDataCapability.AircraftModel => !string.IsNullOrWhiteSpace(s.AircraftModel),
        _ => false
    };

    private static SimulatorCapabilities RequiredConnectorCapability(AircraftDataCapability capability) => capability switch
    {
        AircraftDataCapability.Position
            or AircraftDataCapability.AltitudeMsl
            or AircraftDataCapability.AltitudeAgl
            or AircraftDataCapability.AircraftTitle
            or AircraftDataCapability.AircraftIcao
            or AircraftDataCapability.AircraftModel
            => SimulatorCapabilities.Position,

        AircraftDataCapability.IndicatedAirspeed
            or AircraftDataCapability.GroundSpeed
            or AircraftDataCapability.VerticalSpeed
            or AircraftDataCapability.Heading
            or AircraftDataCapability.Track
            or AircraftDataCapability.Pitch
            or AircraftDataCapability.Bank
            or AircraftDataCapability.OnGround
            or AircraftDataCapability.TouchdownRate
            => SimulatorCapabilities.FlightDynamics,

        AircraftDataCapability.Fuel
            or AircraftDataCapability.GrossWeight
            => SimulatorCapabilities.Fuel,

        AircraftDataCapability.ParkingBrake
            or AircraftDataCapability.Gear
            or AircraftDataCapability.Flaps
            or AircraftDataCapability.Spoilers
            or AircraftDataCapability.SeatBeltSign
            or AircraftDataCapability.Doors
            or AircraftDataCapability.Transponder
            or AircraftDataCapability.Autopilot
            or AircraftDataCapability.ThrustStable
            => SimulatorCapabilities.AircraftSystems,

        AircraftDataCapability.Engines => SimulatorCapabilities.Engines,

        AircraftDataCapability.BeaconLight
            or AircraftDataCapability.NavigationLight
            or AircraftDataCapability.StrobeLight
            or AircraftDataCapability.LandingLight
            or AircraftDataCapability.TaxiLight
            => SimulatorCapabilities.Lights,

        AircraftDataCapability.Slew
            or AircraftDataCapability.Pause
            or AircraftDataCapability.SimulationRate
            => SimulatorCapabilities.SimulatorControls,

        _ => SimulatorCapabilities.None
    };
}
