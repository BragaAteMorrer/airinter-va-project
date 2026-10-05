using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

/// <summary>
/// Audit UX step 8: simulator-boundary pilot journeys.
///
/// These scenarios deliberately use scripted connector output rather than a
/// locally installed simulator. They exercise the same normalized connector
/// boundary, connector hub, FlightRecorder, phase engine and Flight Review
/// used by Hermès in production.
/// </summary>
public sealed class PilotJourneyMatrixE2ETests
{
    private static readonly SimulatorCapabilities FullFsuipcCapabilities =
        SimulatorCapabilities.Position |
        SimulatorCapabilities.FlightDynamics |
        SimulatorCapabilities.Fuel |
        SimulatorCapabilities.AircraftSystems |
        SimulatorCapabilities.Engines |
        SimulatorCapabilities.Lights |
        SimulatorCapabilities.SimulatorControls;

    public static TheoryData<SimulatorKind, string, string, SimulatorCapabilities, bool> SupportedSimulatorProfiles => new()
    {
        {
            SimulatorKind.MicrosoftFlightSimulator,
            "Microsoft Flight Simulator 2020 (SimConnect)",
            "simconnect-msfs2020",
            FullFsuipcCapabilities,
            false
        },
        {
            SimulatorKind.MicrosoftFlightSimulator,
            "Microsoft Flight Simulator 2024 (SimConnect)",
            "simconnect-msfs2024",
            FullFsuipcCapabilities,
            false
        },
        {
            SimulatorKind.MicrosoftFlightSimulator,
            "Microsoft Flight Simulator 2020/2024 (FSUIPC7 fallback)",
            "fsuipc-msfs",
            FullFsuipcCapabilities,
            true
        },
        {
            SimulatorKind.XPlane,
            "X-Plane 11/12 (UDP DataRef)",
            "xplane-udp",
            SimulatorCapabilities.Position |
            SimulatorCapabilities.FlightDynamics |
            SimulatorCapabilities.Fuel |
            SimulatorCapabilities.AircraftSystems |
            SimulatorCapabilities.SimulatorControls,
            true
        },
        {
            SimulatorKind.FlightSimulator2004,
            "Microsoft Flight Simulator 2004 (FSUIPC)",
            "fsuipc-fs9",
            FullFsuipcCapabilities,
            true
        },
        {
            SimulatorKind.FlightSimulatorX,
            "Microsoft Flight Simulator X (FSUIPC)",
            "fsuipc-fsx",
            FullFsuipcCapabilities,
            true
        },
        {
            SimulatorKind.Prepar3D,
            "Prepar3D (FSUIPC)",
            "fsuipc-p3d",
            FullFsuipcCapabilities,
            true
        }
    };

    [Theory]
    [MemberData(nameof(SupportedSimulatorProfiles))]
    public void Supported_connector_profiles_complete_the_same_block_to_block_journey(
        SimulatorKind kind,
        string displayName,
        string connectorId,
        SimulatorCapabilities capabilities,
        bool experimental)
    {
        var start = DateTimeOffset.UtcNow.AddSeconds(2);
        var script = ReferenceFlight(start).ToArray();
        using var connector = new ScriptedConnector(
            new SimulatorDescriptor(kind, displayName, connectorId, capabilities, experimental),
            script.Select(x => (AircraftSnapshot?)x));
        using var hub = new SimulatorConnectorHub(connector);
        var folder = TestFolder(connectorId);

        try
        {
            var recorder = new FlightRecorder(folder);

            hub.Poll();
            var first = Assert.IsType<AircraftSnapshot>(hub.LatestSnapshot);
            Assert.Equal(connectorId, hub.Active?.Descriptor.ConnectorId);
            Assert.Equal(kind, hub.Active?.Descriptor.Kind);

            var operationId = "op_e2e_" + connectorId.Replace('-', '_');
            var pirepId = "pirep_e2e_" + connectorId.Replace('-', '_');
            recorder.Start("https://promethee.example", pirepId, first, operationId);

            while (connector.HasPending)
            {
                hub.Poll();
                var snapshot = Assert.IsType<AircraftSnapshot>(hub.LatestSnapshot);
                recorder.Capture(snapshot);
            }

            Assert.NotNull(recorder.Flight);
            Assert.Equal(operationId, recorder.Flight!.OperationId);
            Assert.Equal(pirepId, recorder.Flight.PirepId);
            Assert.Equal("IN", recorder.Flight.Phase);

            Assert.Single(recorder.Flight.Journal, x => x.Name == "OUT");
            Assert.Single(recorder.Flight.Journal, x => x.Name == "OFF");
            Assert.Single(recorder.Flight.Journal, x => x.Name == "ON");
            Assert.Single(recorder.Flight.Journal, x => x.Name == "IN");
            Assert.Contains(recorder.Flight.Journal, x => x.Name == "PAUSE_STARTED");
            Assert.Contains(recorder.Flight.Journal, x => x.Name == "PAUSE_ENDED");

            var review = Assert.IsType<FlightReview>(recorder.GetReview());
            Assert.True(review.ReadyToFile);
            Assert.Equal("IN", review.Phase);
            Assert.Equal(-300d, review.LandingRate);
            Assert.True(review.PauseCount >= 1);
            Assert.True(review.PausedSeconds >= 4);
            Assert.True(review.FuelUsed > 0);
            Assert.Contains(review.Timeline, x => x.Name == "CRUISE");
            Assert.Contains(review.Timeline, x => x.Name == "FINAL");
            Assert.Contains(review.Timeline, x => x.Name == "IN");

            recorder.AcknowledgePositions(recorder.Pending.Select(x => x.Sample.SampleId).ToArray());
            recorder.AcknowledgeEvents(recorder.PendingEvents.Select(x => x.EventId).ToArray());
            recorder.AcknowledgeFacts(recorder.PendingFacts.Select(x => x.FactId).ToArray());
            recorder.Complete();

            Assert.Null(recorder.Flight);
            Assert.Single(recorder.History);
            Assert.Equal(pirepId, recorder.History[0].PirepId);
        }
        finally
        {
            TryDelete(folder);
        }
    }

    [Fact]
    public async Task Simulator_loss_and_recovery_keep_the_same_operation_and_pirep()
    {
        var start = DateTimeOffset.UtcNow.AddSeconds(2);
        var initial = Snapshot(start, 0, true, 0, 0, 0, true, paused: false, fuel: 8000);
        var taxi = Snapshot(start.AddSeconds(2), 1, true, 12, 0, 0, false, paused: false, fuel: 7950);
        var recovered = Snapshot(start.AddSeconds(6), 2, true, 18, 0, 0, false, paused: false, fuel: 7900);

        using var connector = new ScriptedConnector(
            new SimulatorDescriptor(
                SimulatorKind.MicrosoftFlightSimulator,
                "Microsoft Flight Simulator 2024 (SimConnect)",
                "simconnect-recovery",
                FullFsuipcCapabilities),
            new AircraftSnapshot?[] { initial, taxi, null, recovered });
        using var hub = new SimulatorConnectorHub(connector);
        var folder = TestFolder("recovery");

        try
        {
            var recorder = new FlightRecorder(folder);
            hub.Poll();
            recorder.Start(
                "https://promethee.example",
                "pirep_recovery_reference",
                Assert.IsType<AircraftSnapshot>(hub.LatestSnapshot),
                "op_recovery_reference");

            var telemetry = new TelemetryService(hub, recorder, new PhpVmsClient());

            await telemetry.Tick();
            Assert.Equal("CONNECTED", hub.LinkState);

            await telemetry.Tick();
            Assert.Equal("RECONNECTING", hub.LinkState);
            Assert.Contains(recorder.Flight!.Journal, x => x.Name == "SIMULATOR_LOST");

            await telemetry.Tick();
            Assert.Equal("CONNECTED", hub.LinkState);
            Assert.Contains(recorder.Flight!.Journal, x => x.Name == "SIMULATOR_RECOVERED");
            Assert.Equal("op_recovery_reference", recorder.Flight.OperationId);
            Assert.Equal("pirep_recovery_reference", recorder.Flight.PirepId);
            Assert.Equal("DISCONNECTED", telemetry.SyncState);
        }
        finally
        {
            TryDelete(folder);
        }
    }

    private static IEnumerable<AircraftSnapshot> ReferenceFlight(DateTimeOffset t)
    {
        yield return Snapshot(t, 0, true, 0, 0, 0, true, paused: false, fuel: 8000);
        yield return Snapshot(t.AddSeconds(1), 1, true, 1, 0, 0, false, paused: false, fuel: 7980);
        yield return Snapshot(t.AddSeconds(3), 2, true, 12, 0, 0, false, paused: false, fuel: 7940);
        yield return Snapshot(t.AddSeconds(5), 3, true, 42, 0, 0, false, paused: false, fuel: 7900);
        yield return Snapshot(t.AddSeconds(6), 4, false, 155, 80, 1400, false, gearDown: false, paused: false, fuel: 7850);
        yield return Snapshot(t.AddSeconds(8), 5, false, 250, 1200, 1500, false, gearDown: false, paused: false, fuel: 7750);
        yield return Snapshot(t.AddSeconds(10), 6, false, 420, 15000, 0, false, gearDown: false, paused: false, fuel: 7600);
        yield return Snapshot(t.AddSeconds(12), 7, false, 420, 15000, 0, false, gearDown: false, paused: true, pauseKind: "ACTIVE_PAUSE", fuel: 7580);
        yield return Snapshot(t.AddSeconds(16), 8, false, 420, 15000, 0, false, gearDown: false, paused: false, fuel: 7540);
        yield return Snapshot(t.AddSeconds(18), 9, false, 380, 9000, -1000, false, gearDown: false, paused: false, fuel: 7440);
        yield return Snapshot(t.AddSeconds(20), 10, false, 180, 2500, -700, false, gearDown: true, flaps: 15, paused: false, fuel: 7350);
        yield return Snapshot(t.AddSeconds(22), 11, false, 155, 1100, -600, false, gearDown: true, flaps: 25, paused: false, fuel: 7280);
        yield return Snapshot(t.AddSeconds(24), 12, false, 145, 600, -500, false, gearDown: true, flaps: 30, paused: false, fuel: 7210);
        yield return Snapshot(t.AddSeconds(26), 13, true, 130, 0, -300, false, gearDown: true, flaps: 30, paused: false, fuel: 7160, touchdownRate: -300);
        yield return Snapshot(t.AddSeconds(35), 14, true, 80, 0, 0, false, gearDown: true, flaps: 20, paused: false, fuel: 7100);
        yield return Snapshot(t.AddSeconds(37), 15, true, 20, 0, 0, false, gearDown: true, flaps: 10, paused: false, fuel: 7070);
        yield return Snapshot(t.AddSeconds(40), 16, true, 5, 0, 0, false, gearDown: true, paused: false, fuel: 7050);
        yield return Snapshot(t.AddSeconds(41), 17, true, 0, 0, 0, true, gearDown: true, paused: false, fuel: 7040);
        yield return Snapshot(t.AddSeconds(49), 18, true, 0, 0, 0, true, gearDown: true, paused: false, fuel: 7030);
        yield return Snapshot(t.AddSeconds(57), 19, true, 0, 0, 0, true, gearDown: true, paused: false, fuel: 7020);
    }

    private static AircraftSnapshot Snapshot(
        DateTimeOffset at,
        int index,
        bool onGround,
        double gs,
        double agl,
        double vs,
        bool parking,
        bool gearDown = true,
        double flaps = 0,
        bool? paused = null,
        string? pauseKind = null,
        double fuel = 8000,
        double? touchdownRate = null)
    {
        var offset = index * 0.0005;
        return new AircraftSnapshot(
            Guid.NewGuid(),
            at,
            Latitude: 48.70 + offset,
            Longitude: 2.30 + offset,
            AltitudeMslFeet: agl + 300,
            AltitudeAglFeet: agl,
            IndicatedAirspeedKnots: gs,
            GroundSpeedKnots: gs,
            HeadingDegrees: 180,
            TrackDegrees: 180,
            VerticalSpeedFeetPerMinute: vs,
            OnGround: onGround,
            ParkingBrake: parking,
            EnginesRunning: [true, true],
            FuelWeight: fuel,
            GearDown: gearDown,
            FlapsPercent: flaps,
            BeaconLight: !parking,
            NavigationLight: true,
            StrobeLight: !onGround,
            LandingLight: agl < 10000,
            SlewActive: false,
            Paused: paused,
            PauseKind: pauseKind,
            SimulationRate: 1,
            AircraftTitle: "Air Inter reference aircraft",
            AircraftIcao: "A320",
            PitchDegrees: onGround ? 0 : 2,
            BankDegrees: 0,
            TouchdownVerticalSpeedFeetPerMinute: touchdownRate,
            ThrustStable: true);
    }

    private static string TestFolder(string suffix) =>
        Path.Combine(
            Path.GetTempPath(),
            "AirInter-Hermes-Pilot-E2E",
            suffix + "-" + Guid.NewGuid().ToString("N"));

    private static void TryDelete(string folder)
    {
        try
        {
            if (Directory.Exists(folder)) Directory.Delete(folder, true);
        }
        catch (IOException)
        {
            // Windows runners can briefly keep a file handle after JsonSerializer writes.
        }
    }

    private sealed class ScriptedConnector(
        SimulatorDescriptor descriptor,
        IEnumerable<AircraftSnapshot?> snapshots) : ISimulatorConnector
    {
        private readonly Queue<AircraftSnapshot?> snapshots = new(snapshots);

        public SimulatorDescriptor Descriptor { get; } = descriptor;
        public SimulatorConnectionState ConnectionState { get; private set; } = SimulatorConnectionState.Detected;
        public string Status => ConnectionState == SimulatorConnectionState.Connected
            ? Descriptor.DisplayName + " connecté"
            : Descriptor.DisplayName + " détecté";
        public AircraftSnapshot? LatestSnapshot { get; private set; }
        public bool HasPending => snapshots.Count > 0;
        public event Action<AircraftSnapshot>? SnapshotReceived;

        public void Poll()
        {
            if (snapshots.Count == 0) return;
            LatestSnapshot = snapshots.Dequeue();
            ConnectionState = LatestSnapshot is null
                ? SimulatorConnectionState.Detected
                : SimulatorConnectionState.Connected;
            if (LatestSnapshot is not null) SnapshotReceived?.Invoke(LatestSnapshot);
        }

        public void Dispose() { }
    }
}
