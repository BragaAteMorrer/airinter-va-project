using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class SimulatorConnectorHubRecoveryTests
{
    [Fact]
    public void Healthy_connector_becomes_active_and_connected()
    {
        using var connector = new FakeConnector();
        connector.SetSnapshot(Snapshot(DateTimeOffset.UtcNow));

        using var hub = new SimulatorConnectorHub(connector);
        hub.Poll();

        Assert.Equal(SimulatorSessionState.Connected, hub.SessionState);
        Assert.Same(connector, hub.Active);
        Assert.NotNull(hub.LatestSnapshot);
    }

    [Fact]
    public void Stale_telemetry_is_treated_as_temporary_loss_not_new_flight()
    {
        using var connector = new FakeConnector();
        connector.SetSnapshot(Snapshot(DateTimeOffset.UtcNow));

        using var hub = new SimulatorConnectorHub(connector);
        hub.Poll();
        var descriptor = hub.Active?.Descriptor;

        connector.SetSnapshot(Snapshot(DateTimeOffset.UtcNow.AddSeconds(-20)));
        hub.Poll();

        Assert.Equal(SimulatorSessionState.TemporarilyLost, hub.SessionState);
        Assert.Null(hub.Active);
        Assert.Equal(descriptor, hub.LastActiveDescriptor);
        Assert.NotNull(hub.LostSince);
    }

    [Fact]
    public void Telemetry_return_reuses_the_session_and_clears_loss_state()
    {
        using var connector = new FakeConnector();
        connector.SetSnapshot(Snapshot(DateTimeOffset.UtcNow));

        using var hub = new SimulatorConnectorHub(connector);
        hub.Poll();

        connector.SetSnapshot(Snapshot(DateTimeOffset.UtcNow.AddSeconds(-20)));
        hub.Poll();
        Assert.Equal(SimulatorSessionState.TemporarilyLost, hub.SessionState);

        connector.SetSnapshot(Snapshot(DateTimeOffset.UtcNow));
        hub.Poll();

        Assert.Equal(SimulatorSessionState.Connected, hub.SessionState);
        Assert.Same(connector, hub.Active);
        Assert.Null(hub.LostSince);
    }

    private static AircraftSnapshot Snapshot(DateTimeOffset at) =>
        new(Guid.NewGuid(), at, Latitude: 48.7, Longitude: 2.3, AltitudeMslFeet: 300,
            AltitudeAglFeet: 0, IndicatedAirspeedKnots: 0, GroundSpeedKnots: 0,
            HeadingDegrees: 180, VerticalSpeedFeetPerMinute: 0, OnGround: true,
            ParkingBrake: true, FuelWeight: 8000, GearDown: true, FlapsPercent: 0);

    private sealed class FakeConnector : ISimulatorConnector
    {
        public SimulatorDescriptor Descriptor { get; } = new(
            SimulatorKind.MicrosoftFlightSimulator, "Fake simulator", "fake",
            SimulatorCapabilities.Position | SimulatorCapabilities.FlightDynamics);
        public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.Connected;
        public string Status => "Fake";
        public AircraftSnapshot? LatestSnapshot { get; private set; }
        public event Action<AircraftSnapshot>? SnapshotReceived;

        public void SetSnapshot(AircraftSnapshot snapshot)
        {
            LatestSnapshot = snapshot;
            SnapshotReceived?.Invoke(snapshot);
        }

        public void Poll() { }
        public void Dispose() { }
    }
}
