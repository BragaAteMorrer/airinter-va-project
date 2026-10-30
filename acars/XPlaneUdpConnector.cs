using System.Buffers.Binary;
using System.Net;
using System.Net.Sockets;
using System.Text;

namespace Promethee;

/// <summary>
/// Read-only X-Plane 11/12 RREF transport over localhost UDP.
/// Subscriptions have a fixed 413-byte wire format; replies may use either
/// the modern "RREF," or older "RREF\0" five-byte prefix.
/// Actual XP11/XP12 flight validation is still required.
/// </summary>
public sealed class XPlaneUdpConnector : ISimulatorConnector
{
    private const int DefaultXPlanePort = 49000;
    private const int RequestSize = 413; // 5-byte header, 2 int32 fields, char[400]
    private static readonly TimeSpan FreshnessTimeout = TimeSpan.FromSeconds(15);
    private static readonly TimeSpan SubscriptionRefresh = TimeSpan.FromSeconds(5);
    private readonly int xPlanePort;
    private UdpClient? socket;
    private readonly Dictionary<int, (float Value, DateTimeOffset ReceivedAt)> values = [];
    private DateTimeOffset lastRequest = DateTimeOffset.MinValue;
    private DateTimeOffset waitingSince = DateTimeOffset.MinValue;
    private DateTimeOffset? lastValidPacketAt;

    public SimulatorDescriptor Descriptor { get; } = new(
        SimulatorKind.XPlane, "X-Plane (UDP DataRef)", "xplane-udp",
        SimulatorCapabilities.Position | SimulatorCapabilities.FlightDynamics | SimulatorCapabilities.Fuel |
        SimulatorCapabilities.AircraftSystems | SimulatorCapabilities.SimulatorControls, IsExperimental: true);
    public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.NotDetected;
    public string Status { get; private set; } = "X-Plane non connecté";
    public AircraftSnapshot? LatestSnapshot { get; private set; }
    public event Action<AircraftSnapshot>? SnapshotReceived;

    // Port injection allows deterministic loopback tests without requiring a running X-Plane.
    public XPlaneUdpConnector(int xPlanePort = DefaultXPlanePort)
    {
        if (xPlanePort is < 1 or > 65535) throw new ArgumentOutOfRangeException(nameof(xPlanePort));
        this.xPlanePort = xPlanePort;
    }

    public void Poll()
    {
        try
        {
            EnsureSocket();
            if (DateTimeOffset.UtcNow - lastRequest >= SubscriptionRefresh)
                RequestDataRefs();
            while (socket!.Available > 0)
                Receive();
            if (ConnectionState != SimulatorConnectionState.Connected
                && waitingSince != DateTimeOffset.MinValue
                && DateTimeOffset.UtcNow - waitingSince > TimeSpan.FromSeconds(12))
            {
                Status = lastValidPacketAt is null
                    ? $"X-Plane : aucune réponse RREF depuis 127.0.0.1:{xPlanePort}. Vérifiez que X-Plane est lancé et que son port UDP est {xPlanePort}."
                    : "X-Plane : paquets RREF reçus, mais position incomplète. Vérifiez les DataRefs et la télémétrie.";
            }

            if (LatestSnapshot is { } latest
                && DateTimeOffset.UtcNow - latest.RecordedAt > FreshnessTimeout)
            {
                // A partial / stopped RREF feed is not an active simulator connection.
                values.Clear();
                LatestSnapshot = null;
                ConnectionState = SimulatorConnectionState.Connecting;
                Status = "X-Plane : données expirées, reconnexion UDP…";
            }
        }
        catch (SocketException exception)
        {
            ResetTransport();
            ConnectionState = SimulatorConnectionState.Faulted;
            Status = "X-Plane : erreur UDP (" + exception.SocketErrorCode + "), nouvel essai…";
        }
        catch (ObjectDisposedException)
        {
            // The hub may dispose an inactive transport: recreate it on next Poll.
            ResetTransport();
        }
    }

    private void EnsureSocket()
    {
        if (socket is not null) return;
        socket = new UdpClient(new IPEndPoint(IPAddress.Loopback, 0));
        socket.Client.Blocking = false;
        lastRequest = DateTimeOffset.MinValue;
        waitingSince = DateTimeOffset.UtcNow;
        lastValidPacketAt = null;
    }

    private void RequestDataRefs()
    {
        var destination = new IPEndPoint(IPAddress.Loopback, xPlanePort);
        foreach (var (index, dataRef) in DataRefs)
        {
            var name = Encoding.ASCII.GetBytes(dataRef);
            if (name.Length >= 400)
                throw new InvalidOperationException("Nom DataRef X-Plane trop long.");

            // Do not send 13 + name.Length: X-Plane requires the full char[400].
            var packet = new byte[RequestSize];
            Encoding.ASCII.GetBytes("RREF\0").CopyTo(packet, 0);
            BinaryPrimitives.WriteInt32LittleEndian(packet.AsSpan(5, 4), 5);
            BinaryPrimitives.WriteInt32LittleEndian(packet.AsSpan(9, 4), index);
            name.CopyTo(packet, 13);
            socket!.Send(packet, packet.Length, destination);
        }

        lastRequest = DateTimeOffset.UtcNow;
        if (ConnectionState != SimulatorConnectionState.Connected)
        {
            ConnectionState = SimulatorConnectionState.Connecting;
            Status = "En attente de données X-Plane UDP (127.0.0.1:" + xPlanePort + ")…";
        }
    }

    private void Receive()
    {
        var source = new IPEndPoint(IPAddress.Loopback, 0);
        var packet = socket!.Receive(ref source);
        if (!source.Address.Equals(IPAddress.Loopback) || source.Port != xPlanePort
            || packet.Length < 13 || (packet.Length - 5) % 8 != 0)
            return;

        // RREF replies from X-Plane 11/12 start with "RREF,"; older versions
        // may send "RREF\0". The request header is always "RREF\0".
        if (packet[0] != (byte)'R' || packet[1] != (byte)'R'
            || packet[2] != (byte)'E' || packet[3] != (byte)'F'
            || (packet[4] != (byte)',' && packet[4] != 0))
            return;

        var now = DateTimeOffset.UtcNow;
        lastValidPacketAt = now;
        for (var offset = 5; offset + 8 <= packet.Length; offset += 8)
        {
            var index = BinaryPrimitives.ReadInt32LittleEndian(packet.AsSpan(offset, 4));
            if (index < Latitude || index > Paused) continue;

            var bits = BinaryPrimitives.ReadInt32LittleEndian(packet.AsSpan(offset + 4, 4));
            var value = BitConverter.Int32BitsToSingle(bits);
            // Unknown refs can report sentinel values; never turn them into
            // valid positions, ground states or aircraft-system readings.
            if (float.IsFinite(value) && value > -999000f)
                values[index] = (value, now);
            else
                values.Remove(index);
        }

        if (GetOptional(Latitude, now) is not { } lat
            || GetOptional(Longitude, now) is not { } lon
            || GetOptional(AltitudeMeters, now) is not { } altitude
            || lat is < -90 or > 90 || lon is < -180 or > 180)
            return;

        var snapshot = new AircraftSnapshot(
            Guid.NewGuid(), now,
            Latitude: lat, Longitude: lon, AltitudeMslFeet: MetersToFeet(altitude),
            AltitudeAglFeet: GetConverted(AglMeters, 3.280839895, now),
            // X-Plane indicated_airspeed is already KIAS, not m/s.
            IndicatedAirspeedKnots: GetOptional(IasKnots, now),
            GroundSpeedKnots: GetConverted(GroundSpeedMps, 1.943844492, now),
            HeadingDegrees: GetOptional(HeadingDegrees, now),
            VerticalSpeedFeetPerMinute: GetConverted(VerticalSpeedMps, 196.850394, now),
            OnGround: GetBoolean(OnGround, now),
            ParkingBrake: GetBoolean(ParkingBrake, now),
            FuelWeight: GetOptional(FuelKg, now),
            GearDown: GetBoolean(GearRatio, now, .95f),
            FlapsPercent: GetConverted(FlapsRatio, 100, now),
            Paused: GetBoolean(Paused, now),
            PauseKind: GetBoolean(Paused, now) == true ? "PAUSE" : null);

        LatestSnapshot = snapshot;
        ConnectionState = SimulatorConnectionState.Connected;
        Status = "Connecté à X-Plane (UDP expérimental)";
        SnapshotReceived?.Invoke(snapshot);
    }

    // Missing or stale DataRefs stay null, never false/zero by assumption.
    private float? GetOptional(int index, DateTimeOffset now) =>
        values.TryGetValue(index, out var entry)
        && now - entry.ReceivedAt <= FreshnessTimeout
            ? entry.Value : null;

    private double? GetConverted(int index, double factor, DateTimeOffset now)
    {
        var value = GetOptional(index, now);
        return value is null ? null : value.Value * factor;
    }

    private bool? GetBoolean(int index, DateTimeOffset now, float threshold = .5f)
    {
        var value = GetOptional(index, now);
        return value is null ? null : value.Value > threshold;
    }

    private void ResetTransport()
    {
        socket?.Dispose();
        socket = null;
        values.Clear();
        lastRequest = DateTimeOffset.MinValue;
        waitingSince = DateTimeOffset.MinValue;
        lastValidPacketAt = null;
        LatestSnapshot = null;
        ConnectionState = SimulatorConnectionState.NotDetected;
        Status = "X-Plane non connecté";
    }

    // The simulator hub also calls Dispose on standby connectors. Dispose
    // releases the UDP socket but does not permanently disable future Polls.
    public void Dispose() => ResetTransport();

    private static double MetersToFeet(float value) => value * 3.280839895;

    private const int Latitude = 1, Longitude = 2, AltitudeMeters = 3, AglMeters = 4, IasKnots = 5,
        GroundSpeedMps = 6, HeadingDegrees = 7, VerticalSpeedMps = 8, OnGround = 9, ParkingBrake = 10,
        FuelKg = 11, GearRatio = 12, FlapsRatio = 13, Paused = 14;
    private static readonly (int Index, string DataRef)[] DataRefs = [
        (Latitude, "sim/flightmodel/position/latitude"),
        (Longitude, "sim/flightmodel/position/longitude"),
        (AltitudeMeters, "sim/flightmodel/position/elevation"),
        (AglMeters, "sim/flightmodel/position/y_agl"),
        (IasKnots, "sim/flightmodel/position/indicated_airspeed"),
        (GroundSpeedMps, "sim/flightmodel/position/groundspeed"),
        (HeadingDegrees, "sim/flightmodel/position/true_psi"),
        (VerticalSpeedMps, "sim/flightmodel/position/vh_ind"),
        (OnGround, "sim/flightmodel/failures/onground_any"),
        (ParkingBrake, "sim/cockpit2/controls/parking_brake_ratio"),
        (FuelKg, "sim/flightmodel/weight/m_fuel_total"),
        (GearRatio, "sim/flightmodel2/gear/deploy_ratio[0]"),
        (FlapsRatio, "sim/flightmodel2/controls/flap_handle_deploy_ratio"),
        (Paused, "sim/time/paused")];
}
