namespace Promethee;

/// <summary>Polls available connectors and selects only one that has delivered a valid snapshot.</summary>
public sealed class SimulatorConnectorHub(params ISimulatorConnector[] connectors) : ISimulatorConnector
{
    public SimulatorDescriptor Descriptor { get; } = new(SimulatorKind.Unknown, "Détection automatique", "hub", SimulatorCapabilities.None);
    public SimulatorConnectionState ConnectionState => Active?.ConnectionState ?? SimulatorConnectionState.NotDetected;
    public string Status => Active?.Status ?? connectors.FirstOrDefault(x => x.ConnectionState == SimulatorConnectionState.Connecting)?.Status
        ?? "Simulateur non détecté";
    public AircraftSnapshot? LatestSnapshot => Active?.LatestSnapshot;
    public ISimulatorConnector? Active { get; private set; }
    public event Action<AircraftSnapshot>? SnapshotReceived;

    public void Poll()
    {
        foreach (var connector in connectors) {
            connector.Poll();
            if (connector.LatestSnapshot is not null && connector.ConnectionState == SimulatorConnectionState.Connected) {
                Active = connector;
                break;
            }
        }
        if (Active?.LatestSnapshot is { } snapshot) SnapshotReceived?.Invoke(snapshot);
    }

    public void Dispose()
    {
        foreach (var connector in connectors) connector.Dispose();
    }
}
