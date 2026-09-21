using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class SimulatorDetectionTests
{
    [Fact]
    public void Detection_distinguishes_running_simulators_without_claiming_support()
    {
        var detected = SimulatorDetector.Detect(["FlightSimulator2024", "X-Plane-64"]);

        Assert.Contains(detected, x => x.DisplayName == "Microsoft Flight Simulator 2024" && x.HasImplementedConnector);
        Assert.Contains(detected, x => x.Kind == SimulatorKind.XPlane && !x.HasImplementedConnector);
    }
}
