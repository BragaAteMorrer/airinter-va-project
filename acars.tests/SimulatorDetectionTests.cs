using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class SimulatorDetectionTests
{
    [Fact]
    public void Detection_reports_connector_boundary_and_telemetry_separately()
    {
        var detected = SimulatorDetector.Detect(["FlightSimulator2024", "X-Plane-64", "FSX"]);

        Assert.Contains(detected, x =>
            x.DisplayName == "Microsoft Flight Simulator 2024"
            && x.HasConnectorBoundary
            && x.TelemetryImplemented);

        Assert.Contains(detected, x =>
            x.Kind == SimulatorKind.XPlane
            && x.HasConnectorBoundary
            && x.TelemetryImplemented);

        Assert.Contains(detected, x =>
            x.Kind == SimulatorKind.FlightSimulatorX
            && x.HasConnectorBoundary
            && x.TelemetryImplemented);
    }

    [Theory]
    [InlineData("fs9", SimulatorKind.FlightSimulator2004)]
    [InlineData("fsx_se", SimulatorKind.FlightSimulatorX)]
    [InlineData("Prepar3D_v6", SimulatorKind.Prepar3D)]
    [InlineData("x-plane-x86_64", SimulatorKind.XPlane)]
    public void Detection_recognises_supported_simulator_families(string process, SimulatorKind expected)
    {
        var detected = SimulatorDetector.Detect([process]);
        Assert.Contains(detected, x => x.Kind == expected);
    }

    [Fact]
    public void Detection_reports_fsuipc_telemetry_implementation_for_classic_simulators()
    {
        var detected = SimulatorDetector.Detect(["fs9", "fsx", "Prepar3D"]);
        Assert.All(detected, x => Assert.True(x.TelemetryImplemented));
    }
    [Fact]
    public void Connector_hub_distinguishes_temporary_loss_from_never_detected()
    {
        var connector = new FakeConnector();
        connector.Publish(DateTimeOffset.UtcNow);
        using var hub = new SimulatorConnectorHub(connector);

        hub.Poll();
        Assert.Equal("CONNECTED", hub.LinkState);
        Assert.NotNull(hub.Active);

        connector.Publish(DateTimeOffset.UtcNow.AddSeconds(-30));
        hub.Poll();
        Assert.Equal("RECONNECTING", hub.LinkState);
        Assert.True(hub.TemporarilyLost);
        Assert.Null(hub.Active);

        connector.Publish(DateTimeOffset.UtcNow);
        hub.Poll();
        Assert.Equal("CONNECTED", hub.LinkState);
        Assert.False(hub.TemporarilyLost);
        Assert.NotNull(hub.RecoveredAt);
    }

    private sealed class FakeConnector : ISimulatorConnector
    {
        public SimulatorDescriptor Descriptor { get; } = new(
            SimulatorKind.MicrosoftFlightSimulator, "Fake simulator", "fake",
            SimulatorCapabilities.Position | SimulatorCapabilities.FlightDynamics);
        public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.Connected;
        public string Status => "Fake connected";
        public AircraftSnapshot? LatestSnapshot { get; private set; }
        public event Action<AircraftSnapshot>? SnapshotReceived;

        public void Publish(DateTimeOffset recordedAt)
        {
            LatestSnapshot = new AircraftSnapshot(Guid.NewGuid(), recordedAt, Latitude: 48.7, Longitude: 2.3);
            SnapshotReceived?.Invoke(LatestSnapshot);
        }

        public void Poll() { }
        public void Dispose() { }
    }

}
