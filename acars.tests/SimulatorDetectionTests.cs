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
}
