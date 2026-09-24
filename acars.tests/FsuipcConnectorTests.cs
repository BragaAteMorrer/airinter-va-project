using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class FsuipcConnectorTests
{
    private static readonly DateTimeOffset Now = DateTimeOffset.Parse("2026-09-24T18:00:00Z");

    [Fact]
    public void Detected_simulator_opens_fsuipc_and_publishes_snapshot()
    {
        var session = new FakeSession(Frame(Now));
        using var connector = Connector(session, detected: true);

        connector.Poll();

        Assert.True(session.IsOpen);
        Assert.Equal(SimulatorConnectionState.Connected, connector.ConnectionState);
        Assert.NotNull(connector.LatestSnapshot);
        Assert.Equal(48.725, connector.LatestSnapshot!.Latitude);
        Assert.Equal(2.36, connector.LatestSnapshot.Longitude);
        Assert.Equal(8200, connector.LatestSnapshot.FuelWeight);
        Assert.Equal(-2.5, connector.LatestSnapshot.BankDegrees);
        Assert.Contains("FSUIPC", connector.Status);
        Assert.True(connector.Descriptor.Capabilities.HasFlag(SimulatorCapabilities.Position));
        Assert.True(connector.Descriptor.Capabilities.HasFlag(SimulatorCapabilities.Fuel));
        Assert.True(connector.Descriptor.Capabilities.HasFlag(SimulatorCapabilities.Engines));
    }

    [Fact]
    public void Missing_simulator_process_never_opens_fsuipc()
    {
        var session = new FakeSession(Frame(Now));
        using var connector = Connector(session, detected: false);

        connector.Poll();

        Assert.False(session.IsOpen);
        Assert.Equal(0, session.OpenCount);
        Assert.Equal(SimulatorConnectionState.NotDetected, connector.ConnectionState);
        Assert.Null(connector.LatestSnapshot);
    }

    [Fact]
    public void Failed_fsuipc_read_is_non_fatal_and_retryable()
    {
        var session = new FakeSession(Frame(Now)) { ThrowOnRead = true };
        using var connector = Connector(session, detected: true);

        connector.Poll();

        Assert.Equal(SimulatorConnectionState.Detected, connector.ConnectionState);
        Assert.False(session.IsOpen);
        Assert.Null(connector.LatestSnapshot);
        Assert.Contains("indisponible", connector.Status);
    }

    [Fact]
    public void Fsuipc_snapshot_can_start_the_existing_recorder()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-FSUIPC-Tests", Guid.NewGuid().ToString("N"));
        var recorder = new FlightRecorder(folder);
        var snapshot = FsuipcTelemetryMapper.ToSnapshot(Frame(Now));

        recorder.Start("https://promethee.example", "pirep-fsuipc", snapshot, "op-fsuipc");

        Assert.NotNull(recorder.Flight);
        Assert.Equal("pirep-fsuipc", recorder.Flight!.PirepId);
        Assert.Equal(8200, recorder.Flight.InitialFuel);
        Assert.NotEmpty(recorder.Pending);
        Assert.Equal("BOARDING", recorder.Flight.Phase);
        Assert.Empty(recorder.PendingEvents);
    }

    private static FsuipcConnector Connector(FakeSession session, bool detected) =>
        new(
            SimulatorKind.FlightSimulatorX,
            "Microsoft Flight Simulator X",
            () => session,
            () => detected
                ? [new DetectedSimulator(SimulatorKind.FlightSimulatorX, "Microsoft Flight Simulator X", true, true)]
                : [],
            () => Now);

    private static FsuipcTelemetryFrame Frame(DateTimeOffset at) => new(
        at,
        Latitude: 48.725,
        Longitude: 2.36,
        AltitudeMslFeet: 310,
        AltitudeAglFeet: 5,
        IndicatedAirspeedKnots: 0,
        GroundSpeedKnots: 0,
        VerticalSpeedFeetPerMinute: 0,
        HeadingDegrees: 245,
        PitchDegrees: 0.5,
        BankDegrees: -2.5,
        FuelWeightPounds: 8200,
        OnGround: true,
        ParkingBrake: true,
        GearDown: true,
        FlapsPercent: 0,
        EnginesRunning: [false, false],
        SlewActive: false,
        BeaconLight: false,
        NavigationLight: true,
        StrobeLight: false,
        LandingLight: false,
        TaxiLight: false,
        SpoilersArmed: false,
        AircraftTitle: "Air Inter A320");

    private sealed class FakeSession(FsuipcTelemetryFrame frame) : IFsuipcSession
    {
        public bool IsOpen { get; private set; }
        public int OpenCount { get; private set; }
        public bool ThrowOnRead { get; set; }

        public void Open()
        {
            OpenCount++;
            IsOpen = true;
        }

        public FsuipcTelemetryFrame Read()
        {
            if (ThrowOnRead) throw new InvalidOperationException("FSUIPC test failure");
            return frame;
        }

        public void Close() => IsOpen = false;
        public void Dispose() => Close();
    }
}
