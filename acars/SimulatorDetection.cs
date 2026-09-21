using System.Diagnostics;

namespace Promethee;

/// <summary>
/// Best-effort process detection. This is only a user-facing hint and never a
/// support claim: a connector must still establish its own protocol session.
/// </summary>
public sealed record DetectedSimulator(SimulatorKind Kind, string DisplayName, bool HasImplementedConnector);

public static class SimulatorDetector
{
    public static IReadOnlyList<DetectedSimulator> DetectRunning()
    {
        try { return Detect(Process.GetProcesses().Select(x => x.ProcessName)); }
        catch { return []; }
    }

    public static IReadOnlyList<DetectedSimulator> Detect(IEnumerable<string> processNames)
    {
        var names = processNames.Select(x => x.Trim().ToLowerInvariant()).ToHashSet();
        var result = new List<DetectedSimulator>();
        Add(result, names, ["flightsimulator2024", "microsoft.flightsimulator2024"], SimulatorKind.MicrosoftFlightSimulator, "Microsoft Flight Simulator 2024", true);
        Add(result, names, ["flightsimulator", "microsoft.flightsimulator"], SimulatorKind.MicrosoftFlightSimulator, "Microsoft Flight Simulator 2020", true);
        Add(result, names, ["prepar3d"], SimulatorKind.Prepar3D, "Prepar3D", true);
        Add(result, names, ["fsx"], SimulatorKind.FlightSimulatorX, "Microsoft Flight Simulator X / Steam Edition", true);
        Add(result, names, ["fs9"], SimulatorKind.FlightSimulator2004, "Microsoft Flight Simulator 2004", true);
        Add(result, names, ["x-plane", "x-plane-64", "xplane"], SimulatorKind.XPlane, "X-Plane 11/12", true);
        return result;
    }

    private static void Add(List<DetectedSimulator> result, HashSet<string> names, IEnumerable<string> candidates,
        SimulatorKind kind, string displayName, bool connectorImplemented)
    {
        if (candidates.Any(names.Contains))
            result.Add(new(kind, displayName, connectorImplemented));
    }
}
