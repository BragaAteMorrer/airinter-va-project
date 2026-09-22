using System.Diagnostics;

namespace Promethee;

/// <summary>
/// Best-effort process detection. This is only a user-facing hint and never a
/// support claim: a connector must still establish its own protocol session.
/// </summary>
public sealed record DetectedSimulator(SimulatorKind Kind, string DisplayName, bool HasConnectorBoundary, bool TelemetryImplemented);

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
        Add(result, names, ["flightsimulator2024", "microsoft.flightsimulator2024"], SimulatorKind.MicrosoftFlightSimulator, "Microsoft Flight Simulator 2024", true, true);
        Add(result, names, ["flightsimulator", "microsoft.flightsimulator"], SimulatorKind.MicrosoftFlightSimulator, "Microsoft Flight Simulator 2020", true, true);
        Add(result, names, ["prepar3d", "prepar3d_v2", "prepar3d_v3", "prepar3d_v4", "prepar3d_v5", "prepar3d_v6"], SimulatorKind.Prepar3D, "Prepar3D", true, false);
        Add(result, names, ["fsx", "fsx_se"], SimulatorKind.FlightSimulatorX, "Microsoft Flight Simulator X / Steam Edition", true, false);
        Add(result, names, ["fs9"], SimulatorKind.FlightSimulator2004, "Microsoft Flight Simulator 2004", true, false);
        Add(result, names, ["x-plane", "x-plane-64", "xplane", "x-plane-x86_64"], SimulatorKind.XPlane, "X-Plane 11/12", true, true);
        return result;
    }

    private static void Add(List<DetectedSimulator> result, HashSet<string> names, IEnumerable<string> candidates,
        SimulatorKind kind, string displayName, bool connectorBoundary, bool telemetryImplemented)
    {
        if (candidates.Any(names.Contains))
            result.Add(new(kind, displayName, connectorBoundary, telemetryImplemented));
    }
}
