namespace Promethee;

/// <summary>
/// Adapter boundary for the FSUIPC families used by FS2004, FSX and Prepar3D.
/// The protocol reader is intentionally not bundled until the compatible client
/// SDK/runtime and redistribution terms have been validated.
/// </summary>
public sealed class FsuipcConnector : ISimulatorConnector
{
    private readonly SimulatorKind kind;
    private readonly string displayName;
    public FsuipcConnector(SimulatorKind kind, string displayName)
    {
        if (kind is not (SimulatorKind.FlightSimulator2004 or SimulatorKind.FlightSimulatorX or SimulatorKind.Prepar3D))
            throw new ArgumentOutOfRangeException(nameof(kind));
        this.kind = kind;
        this.displayName = displayName;
        Descriptor = new(kind, displayName, "fsuipc", SimulatorCapabilities.None, IsExperimental: true);
    }

    public SimulatorDescriptor Descriptor { get; }
    public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.NotDetected;
    public string Status { get; private set; } = "FSUIPC en attente";
    public AircraftSnapshot? LatestSnapshot => null;
    public event Action<AircraftSnapshot>? SnapshotReceived;

    public void Poll()
    {
        var detected = SimulatorDetector.DetectRunning().FirstOrDefault(x => x.Kind == kind);
        if (detected is null) {
            ConnectionState = SimulatorConnectionState.NotDetected;
            Status = displayName + " non détecté";
            return;
        }

        // Detection is useful today, but pretending that telemetry exists would
        // be dangerous. The future protocol implementation plugs in here.
        ConnectionState = SimulatorConnectionState.Detected;
        Status = displayName + " détecté — connecteur FSUIPC requis";
    }

    public void Dispose() { }
}
