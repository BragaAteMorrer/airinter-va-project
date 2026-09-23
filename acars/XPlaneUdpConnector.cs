using System.Net;
using System.Net.Sockets;
using System.Text;

namespace Promethee;

/// <summary>
/// X-Plane's documented local UDP DataRef (RREF) protocol. It only talks to
/// loopback and requests read-only values; X-Plane must be running locally.
/// Validation on XP11/XP12 remains required before this can be called supported.
/// </summary>
public sealed class XPlaneUdpConnector : ISimulatorConnector
{
    private const int XPlanePort = 49000;
    private readonly UdpClient socket = new(0);
    private readonly Dictionary<int, float> values = [];
    private DateTimeOffset lastRequest;

    public SimulatorDescriptor Descriptor { get; } = new(
        SimulatorKind.XPlane, "X-Plane (UDP DataRef)", "xplane-udp",
        SimulatorCapabilities.Position | SimulatorCapabilities.FlightDynamics | SimulatorCapabilities.Fuel |
        SimulatorCapabilities.AircraftSystems, IsExperimental: true);
    public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.NotDetected;
    public string Status { get; private set; } = "X-Plane non connecté";
    public AircraftSnapshot? LatestSnapshot { get; private set; }
    public event Action<AircraftSnapshot>? SnapshotReceived;

    public XPlaneUdpConnector()
    {
        socket.Client.ReceiveTimeout = 1;
        socket.Client.Blocking = false;
    }

    public void Poll()
    {
        try {
            if (DateTimeOffset.UtcNow - lastRequest > TimeSpan.FromSeconds(5)) RequestDataRefs();
            while (socket.Available > 0) Receive();
            if (LatestSnapshot is not null && DateTimeOffset.UtcNow - LatestSnapshot.RecordedAt > TimeSpan.FromSeconds(15)) {
                ConnectionState = SimulatorConnectionState.NotDetected;
                Status = "X-Plane non connecté";
            }
        } catch (SocketException) {
            ConnectionState = SimulatorConnectionState.NotDetected;
            Status = "X-Plane non connecté";
        }
    }

    private void RequestDataRefs()
    {
        lastRequest = DateTimeOffset.UtcNow;
        foreach (var (index, dataRef) in DataRefs) {
            var name = Encoding.ASCII.GetBytes(dataRef + "\0");
            var packet = new byte[13 + name.Length];
            Encoding.ASCII.GetBytes("RREF\0").CopyTo(packet, 0);
            BitConverter.GetBytes(5).CopyTo(packet, 5); // five reports per second
            BitConverter.GetBytes(index).CopyTo(packet, 9);
            name.CopyTo(packet, 13);
            socket.Send(packet, packet.Length, new IPEndPoint(IPAddress.Loopback, XPlanePort));
        }
        if (ConnectionState == SimulatorConnectionState.NotDetected) {
            ConnectionState = SimulatorConnectionState.Connecting;
            Status = "Connexion à X-Plane (UDP)…";
        }
    }

    private void Receive()
    {
        var source = new IPEndPoint(IPAddress.Loopback, 0);
        var packet = socket.Receive(ref source);
        if (!source.Address.Equals(IPAddress.Loopback) || packet.Length < 13 || Encoding.ASCII.GetString(packet, 0, 5) != "RREF\0") return;
        for (var offset = 5; offset + 8 <= packet.Length; offset += 8)
            values[BitConverter.ToInt32(packet, offset)] = BitConverter.ToSingle(packet, offset + 4);

        if (!values.TryGetValue(Latitude, out var lat) || !values.TryGetValue(Longitude, out var lon)
            || !values.TryGetValue(AltitudeMeters, out var altitude)) return;

        var snapshot = new AircraftSnapshot(
            Guid.NewGuid(), DateTimeOffset.UtcNow,
            Latitude: lat, Longitude: lon, AltitudeMslFeet: MetersToFeet(altitude),
            AltitudeAglFeet: GetConverted(AglMeters, 3.280839895),
            IndicatedAirspeedKnots: GetConverted(IasMps, 1.943844492),
            GroundSpeedKnots: GetConverted(GroundSpeedMps, 1.943844492),
            HeadingDegrees: GetOptional(HeadingDegrees),
            VerticalSpeedFeetPerMinute: GetConverted(VerticalSpeedMps, 196.850394),
            OnGround: GetBoolean(OnGround), ParkingBrake: GetBoolean(ParkingBrake),
            FuelWeight: GetOptional(FuelKg), GearDown: GetBoolean(GearRatio, .95f),
            FlapsPercent: GetConverted(FlapsRatio, 100));
        LatestSnapshot = snapshot;
        ConnectionState = SimulatorConnectionState.Connected;
        Status = "Connecté à X-Plane (UDP expérimental)";
        SnapshotReceived?.Invoke(snapshot);
    }

    // Missing DataRefs must stay UNKNOWN. Returning 0/false here would create
    // fake ground, fuel, gear or flight-dynamics facts and can corrupt FDM.
    private float? GetOptional(int index) =>
        values.TryGetValue(index, out var value) && float.IsFinite(value) ? value : null;

    private double? GetConverted(int index, double factor)
    {
        var value = GetOptional(index);
        return value is null ? null : value.Value * factor;
    }

    private bool? GetBoolean(int index, float threshold = .5f)
    {
        var value = GetOptional(index);
        return value is null ? null : value.Value > threshold;
    }

    public void Dispose() => socket.Dispose();

    private static double MetersToFeet(float value) => value * 3.280839895;
    private static double MetersPerSecondToKnots(float value) => value * 1.943844492;
    private static double MetersPerSecondToFeetPerMinute(float value) => value * 196.850394;

    private const int Latitude = 1, Longitude = 2, AltitudeMeters = 3, AglMeters = 4, IasMps = 5,
        GroundSpeedMps = 6, HeadingDegrees = 7, VerticalSpeedMps = 8, OnGround = 9, ParkingBrake = 10,
        FuelKg = 11, GearRatio = 12, FlapsRatio = 13;
    private static readonly (int Index, string DataRef)[] DataRefs = [
        (Latitude, "sim/flightmodel/position/latitude"),
        (Longitude, "sim/flightmodel/position/longitude"),
        (AltitudeMeters, "sim/flightmodel/position/elevation"),
        (AglMeters, "sim/flightmodel/position/y_agl"),
        (IasMps, "sim/flightmodel/position/indicated_airspeed"),
        (GroundSpeedMps, "sim/flightmodel/position/groundspeed"),
        (HeadingDegrees, "sim/flightmodel/position/true_psi"),
        (VerticalSpeedMps, "sim/flightmodel/position/vh_ind"),
        (OnGround, "sim/flightmodel/failures/onground_any"),
        (ParkingBrake, "sim/cockpit2/controls/parking_brake_ratio"),
        (FuelKg, "sim/flightmodel/weight/m_fuel_total"),
        (GearRatio, "sim/flightmodel2/gear/deploy_ratio[0]"),
        (FlapsRatio, "sim/flightmodel2/controls/flap_handle_deploy_ratio")];
}
