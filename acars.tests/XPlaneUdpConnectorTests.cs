using System.Buffers.Binary;
using System.Net;
using System.Net.Sockets;
using System.Text;
using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class XPlaneUdpConnectorTests
{
    [Theory]
    [InlineData("RREF,")]
    [InlineData("RREF\0")]
    public void Receives_modern_and_legacy_RREF_and_preserves_speed_units(string replyHeader)
    {
        using var server = StartLoopbackServer();
        using var connector = new XPlaneUdpConnector(((IPEndPoint)server.Client.LocalEndPoint!).Port);

        connector.Poll();
        var remote = ReadSubscriptions(server);

        var reply = BuildReply(replyHeader,
            (1, 48.7f),   // latitude
            (2, 2.3f),    // longitude
            (3, 1500f),   // altitude meters
            (5, 140f),    // IAS is already in knots
            (6, 100f),    // ground speed is m/s
            (9, 1f));     // on-ground
        server.Send(reply, reply.Length, remote);
        connector.Poll();

        Assert.Equal(SimulatorConnectionState.Connected, connector.ConnectionState);
        var snapshot = Assert.IsType<AircraftSnapshot>(connector.LatestSnapshot);
        Assert.InRange(snapshot.Latitude!.Value, 48.69, 48.71);
        Assert.InRange(snapshot.Longitude!.Value, 2.29, 2.31);
        Assert.InRange(snapshot.AltitudeMslFeet!.Value, 4921, 4922);
        Assert.Equal(140d, snapshot.IndicatedAirspeedKnots!.Value);
        Assert.InRange(snapshot.GroundSpeedKnots!.Value, 194.3, 194.5);
        Assert.True(snapshot.OnGround);
        Assert.Null(snapshot.FuelWeight); // unknown must not become zero
    }

    [Fact]
    public void Disposed_standby_connector_can_reopen_without_restarting_Hermes()
    {
        using var server = StartLoopbackServer();
        using var connector = new XPlaneUdpConnector(((IPEndPoint)server.Client.LocalEndPoint!).Port);

        connector.Poll();
        ReadSubscriptions(server);
        connector.Dispose(); // SimulatorConnectorHub does this for inactive connectors.

        Assert.Null(connector.LatestSnapshot);
        Assert.Equal(SimulatorConnectionState.NotDetected, connector.ConnectionState);

        connector.Poll();
        var remote = ReadSubscriptions(server);
        var reply = BuildReply("RREF,", (1, 46f), (2, 7f), (3, 1000f));
        server.Send(reply, reply.Length, remote);
        connector.Poll();

        Assert.Equal(SimulatorConnectionState.Connected, connector.ConnectionState);
        Assert.NotNull(connector.LatestSnapshot);
    }

    [Fact]
    public void Invalid_position_is_not_treated_as_connected()
    {
        using var server = StartLoopbackServer();
        using var connector = new XPlaneUdpConnector(((IPEndPoint)server.Client.LocalEndPoint!).Port);

        connector.Poll();
        var remote = ReadSubscriptions(server);
        var invalid = BuildReply("RREF,", (1, float.NaN), (2, 2.3f), (3, 1000f));
        server.Send(invalid, invalid.Length, remote);
        connector.Poll();

        Assert.Equal(SimulatorConnectionState.Connecting, connector.ConnectionState);
        Assert.Null(connector.LatestSnapshot);
    }

    private static UdpClient StartLoopbackServer()
    {
        var server = new UdpClient(new IPEndPoint(IPAddress.Loopback, 0));
        server.Client.ReceiveTimeout = 3000;
        return server;
    }

    private static IPEndPoint ReadSubscriptions(UdpClient server)
    {
        IPEndPoint sender = new(IPAddress.Loopback, 0);
        var indexes = new HashSet<int>();
        for (var i = 0; i < 14; i++)
        {
            var request = server.Receive(ref sender);
            Assert.Equal(413, request.Length); // char[400] must be fully padded
            Assert.Equal("RREF\0", Encoding.ASCII.GetString(request, 0, 5));
            Assert.Equal(5, BinaryPrimitives.ReadInt32LittleEndian(request.AsSpan(5, 4)));
            indexes.Add(BinaryPrimitives.ReadInt32LittleEndian(request.AsSpan(9, 4)));
        }
        Assert.Equal(14, indexes.Count);
        return sender;
    }

    private static byte[] BuildReply(string header, params (int Index, float Value)[] values)
    {
        var packet = new byte[5 + 8 * values.Length];
        Encoding.ASCII.GetBytes(header).CopyTo(packet, 0);
        for (var i = 0; i < values.Length; i++)
        {
            var offset = 5 + i * 8;
            BinaryPrimitives.WriteInt32LittleEndian(packet.AsSpan(offset, 4), values[i].Index);
            BinaryPrimitives.WriteInt32LittleEndian(packet.AsSpan(offset + 4, 4),
                BitConverter.SingleToInt32Bits(values[i].Value));
        }
        return packet;
    }
}
