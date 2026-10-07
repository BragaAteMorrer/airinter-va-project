using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class SimulatorConnectorHubTests
{
    [Fact]
    public void Healthy_primary_connector_keeps_fallback_dormant()
    {
        var primary = new FakeConnector("simconnect", healthy: true);
        var fallback = new FakeConnector("fsuipc", healthy: true);
        using var hub = new SimulatorConnectorHub(primary, fallback);

        hub.Poll();
        hub.Poll();

        Assert.Same(primary, hub.Active);
        Assert.Equal(2, primary.PollCount);
        Assert.Equal(0, fallback.PollCount);
        Assert.True(fallback.DisposeCount >= 1);
    }

    [Fact]
    public void Fallback_is_polled_only_after_active_connector_is_lost()
    {
        var primary = new FakeConnector("simconnect", healthy: true);
        var fallback = new FakeConnector("fsuipc", healthy: true);
        using var hub = new SimulatorConnectorHub(primary, fallback);

        hub.Poll();
        Assert.Same(primary, hub.Active);
        Assert.Equal(0, fallback.PollCount);

        primary.Healthy = false;
        hub.Poll();

        Assert.Same(fallback, hub.Active);
        Assert.Equal(2, primary.PollCount);
        Assert.Equal(1, fallback.PollCount);
        Assert.True(primary.DisposeCount >= 1);
    }

    private sealed class FakeConnector : ISimulatorConnector
    {
        public FakeConnector(string id, bool healthy)
        {
            Healthy = healthy;
            Descriptor = new(
                SimulatorKind.MicrosoftFlightSimulator,
                id,
                id,
                SimulatorCapabilities.Position | SimulatorCapabilities.FlightDynamics);
        }

        public bool Healthy { get; set; }
        public int PollCount { get; private set; }
        public int DisposeCount { get; private set; }
        public SimulatorDescriptor Descriptor { get; }
        public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.NotDetected;
        public string Status { get; private set; } = "idle";
        public AircraftSnapshot? LatestSnapshot { get; private set; }
        public event Action<AircraftSnapshot>? SnapshotReceived;

        public void Poll()
        {
            PollCount++;
            if (!Healthy)
            {
                ConnectionState = SimulatorConnectionState.NotDetected;
                Status = "offline";
                LatestSnapshot = null;
                return;
            }

            var snapshot = new AircraftSnapshot(
                Guid.NewGuid(),
                DateTimeOffset.UtcNow,
                Latitude: 48.7,
                Longitude: 2.3,
                AltitudeMslFeet: 35000,
                AltitudeAglFeet: 34000,
                IndicatedAirspeedKnots: 280,
                GroundSpeedKnots: 460,
                VerticalSpeedFeetPerMinute: 0,
                HeadingDegrees: 150,
                FuelWeight: 20000,
                OnGround: false);

            ConnectionState = SimulatorConnectionState.Connected;
            Status = "connected";
            LatestSnapshot = snapshot;
            SnapshotReceived?.Invoke(snapshot);
        }

        public void Dispose()
        {
            DisposeCount++;
            ConnectionState = SimulatorConnectionState.NotDetected;
            Status = "standby";
            LatestSnapshot = null;
        }
    }
}
