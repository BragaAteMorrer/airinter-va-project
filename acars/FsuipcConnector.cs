namespace Promethee;

/// <summary>
/// FSUIPC connector for the classic Microsoft simulator families. Process
/// detection is only a hint: the connector becomes Connected exclusively after
/// a real FSUIPC frame has been read and validated.
/// </summary>
public sealed class FsuipcConnector : ISimulatorConnector
{
    private static readonly TimeSpan RetryDelay = TimeSpan.FromSeconds(3);

    private readonly SimulatorKind kind;
    private readonly string displayName;
    private readonly Func<IFsuipcSession> sessionFactory;
    private readonly Func<IReadOnlyList<DetectedSimulator>> detector;
    private readonly Func<DateTimeOffset> clock;
    private IFsuipcSession? session;
    private DateTimeOffset nextConnectAttemptAt = DateTimeOffset.MinValue;

    public FsuipcConnector(SimulatorKind kind, string displayName)
        : this(kind, displayName, () => new FsuipcNativeSession(), SimulatorDetector.DetectRunning, () => DateTimeOffset.UtcNow)
    {
    }

    public FsuipcConnector(
        SimulatorKind kind,
        string displayName,
        Func<IFsuipcSession> sessionFactory,
        Func<IReadOnlyList<DetectedSimulator>> detector,
        Func<DateTimeOffset>? clock = null)
    {
        if (kind is not (SimulatorKind.FlightSimulator2004 or SimulatorKind.FlightSimulatorX or SimulatorKind.Prepar3D))
            throw new ArgumentOutOfRangeException(nameof(kind));

        this.kind = kind;
        this.displayName = displayName;
        this.sessionFactory = sessionFactory ?? throw new ArgumentNullException(nameof(sessionFactory));
        this.detector = detector ?? throw new ArgumentNullException(nameof(detector));
        this.clock = clock ?? (() => DateTimeOffset.UtcNow);

        Descriptor = new(
            kind,
            displayName,
            "fsuipc",
            SimulatorCapabilities.Position |
            SimulatorCapabilities.FlightDynamics |
            SimulatorCapabilities.Fuel |
            SimulatorCapabilities.AircraftSystems |
            SimulatorCapabilities.Engines |
            SimulatorCapabilities.Lights |
            SimulatorCapabilities.SimulatorControls,
            IsExperimental: true);
    }

    public SimulatorDescriptor Descriptor { get; }
    public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.NotDetected;
    public string Status { get; private set; } = "FSUIPC en attente";
    public AircraftSnapshot? LatestSnapshot { get; private set; }
    public event Action<AircraftSnapshot>? SnapshotReceived;

    public void Poll()
    {
        var detected = detector().FirstOrDefault(x => x.Kind == kind);
        if (detected is null) {
            CloseSession();
            LatestSnapshot = null;
            ConnectionState = SimulatorConnectionState.NotDetected;
            Status = displayName + " non détecté";
            return;
        }

        var now = clock();
        if (now < nextConnectAttemptAt) {
            ConnectionState = SimulatorConnectionState.Detected;
            Status = displayName + " détecté — nouvelle tentative FSUIPC…";
            return;
        }

        try {
            if (session is null) {
                ConnectionState = SimulatorConnectionState.Connecting;
                Status = displayName + " détecté — connexion FSUIPC…";
                session = sessionFactory();
                session.Open();
            }

            var frame = session.Read();
            Validate(frame);
            var snapshot = FsuipcTelemetryMapper.ToSnapshot(frame);
            LatestSnapshot = snapshot;
            ConnectionState = SimulatorConnectionState.Connected;
            Status = "Connecté à " + displayName + " via FSUIPC";
            nextConnectAttemptAt = DateTimeOffset.MinValue;
            SnapshotReceived?.Invoke(snapshot);
        } catch (Exception exception)
        {
            System.Diagnostics.Trace.WriteLine($"FSUIPC connector ({displayName}) unavailable: {exception}");
            CloseSession();
            LatestSnapshot = null;
            ConnectionState = SimulatorConnectionState.Detected;
            Status = displayName + " détecté — FSUIPC indisponible";
            nextConnectAttemptAt = now.Add(RetryDelay);
        }
    }

    public void Dispose() => CloseSession();

    private void CloseSession()
    {
        if (session is null) return;
        try { session.Dispose(); }
        catch (Exception exception) { System.Diagnostics.Trace.WriteLine($"FSUIPC close failed: {exception}"); }
        finally { session = null; }
    }

    private static void Validate(FsuipcTelemetryFrame frame)
    {
        var numeric = new[] {
            frame.Latitude, frame.Longitude, frame.AltitudeMslFeet, frame.AltitudeAglFeet,
            frame.IndicatedAirspeedKnots, frame.GroundSpeedKnots, frame.VerticalSpeedFeetPerMinute,
            frame.HeadingDegrees, frame.PitchDegrees, frame.BankDegrees, frame.FuelWeightPounds,
            frame.FlapsPercent
        };

        if (numeric.Any(x => !double.IsFinite(x))
            || Math.Abs(frame.Latitude) > 90
            || Math.Abs(frame.Longitude) > 180
            || frame.AltitudeAglFeet < -100
            || frame.IndicatedAirspeedKnots < 0
            || frame.GroundSpeedKnots < 0
            || frame.FuelWeightPounds < 0
            || frame.FlapsPercent is < 0 or > 100)
            throw new InvalidOperationException("FSUIPC a retourné une trame de télémétrie invalide.");
    }
}
