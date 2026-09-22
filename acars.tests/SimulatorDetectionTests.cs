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
            && !x.TelemetryImplemented);
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
    public void Detection_does_not_claim_fsuipc_telemetry_before_protocol_validation()
    {
        var detected = SimulatorDetector.Detect(["fs9", "fsx", "Prepar3D"]);
        Assert.All(detected, x => Assert.False(x.TelemetryImplemented));
    }
}
