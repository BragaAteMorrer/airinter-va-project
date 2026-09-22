namespace Promethee;

/// <summary>
/// Polls all available connectors and selects the first one that actually
/// delivers telemetry. A process being detected is never enough to become
/// active. The active connector is released after a stale/faulted session so
/// another simulator can take over without restarting Hermès.
/// </summary>
public sealed class SimulatorConnectorHub(params ISimulatorConnector[] connectors) : ISimulatorConnector
{
    private static readonly TimeSpan SnapshotTimeout = TimeSpan.FromSeconds(15);

    public SimulatorDescriptor Descriptor { get; } = new(SimulatorKind.Unknown, "Détection automatique", "hub", SimulatorCapabilities.None);
    public SimulatorConnectionState ConnectionState => Active?.ConnectionState
        ?? connectors.Select(x => x.ConnectionState).OrderByDescending(StateRank).FirstOrDefault();
    public string Status => Active?.Status
        ?? connectors.FirstOrDefault(x => x.ConnectionState == SimulatorConnectionState.Connecting)?.Status
        ?? connectors.FirstOrDefault(x => x.ConnectionState == SimulatorConnectionState.Detected)?.Status
        ?? "Simulateur non détecté";
    public AircraftSnapshot? LatestSnapshot => Active?.LatestSnapshot;
    public ISimulatorConnector? Active { get; private set; }
    public event Action<AircraftSnapshot>? SnapshotReceived;

    public IReadOnlyList<SimulatorConnectorStatus> Connectors => connectors.Select(x => new SimulatorConnectorStatus(
        x.Descriptor.ConnectorId, x.Descriptor.Kind, x.Descriptor.DisplayName,
        x.ConnectionState, x.Status, x.Descriptor.Capabilities, ReferenceEquals(x, Active),
        x.LatestSnapshot?.RecordedAt)).ToArray();

    public void Poll()
    {
        foreach (var connector in connectors) connector.Poll();

        if (Active is not null && !IsHealthy(Active)) Active = null;

        if (Active is null) {
            Active = connectors.FirstOrDefault(IsHealthy);
        }

        if (Active?.LatestSnapshot is { } snapshot) SnapshotReceived?.Invoke(snapshot);
    }

    private static bool IsHealthy(ISimulatorConnector connector) =>
        connector.ConnectionState == SimulatorConnectionState.Connected
        && connector.LatestSnapshot is { } sample
        && DateTimeOffset.UtcNow - sample.RecordedAt <= SnapshotTimeout;

    private static int StateRank(SimulatorConnectionState state) => state switch {
        SimulatorConnectionState.Connected => 4,
        SimulatorConnectionState.Connecting => 3,
        SimulatorConnectionState.Detected => 2,
        SimulatorConnectionState.Faulted => 1,
        _ => 0,
    };

    public void Dispose()
    {
        foreach (var connector in connectors) connector.Dispose();
    }
}

public sealed record SimulatorConnectorStatus(
    string ConnectorId,
    SimulatorKind Kind,
    string DisplayName,
    SimulatorConnectionState State,
    string Status,
    SimulatorCapabilities Capabilities,
    bool Active,
    DateTimeOffset? LastSnapshotAt);
