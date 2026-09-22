using System.Diagnostics;
using System.IO;
using System.Reflection;
using System.Runtime.InteropServices;
using System.Text.Json;

namespace Promethee.Acars;

internal sealed record HermesDiagnosticReport(
    string Version, DateTimeOffset GeneratedAt, string Os, string Runtime,
    string ProcessArchitecture, string Server, bool ApiConnected,
    string Simulator, string FlightPhase, int PendingMessages,
    bool RecoveryAvailable, string? Warning);

internal static class DiagnosticsService
{
    public static HermesDiagnosticReport Create(string server, bool connected, string simulator,
        FlightRecorder recorder)
    {
        var flight = recorder.Flight;
        return new(
            UpdateService.CurrentVersion,
            DateTimeOffset.UtcNow,
            RuntimeInformation.OSDescription,
            RuntimeInformation.FrameworkDescription,
            RuntimeInformation.ProcessArchitecture.ToString(),
            SanitizeServer(server),
            connected,
            simulator,
            flight?.Phase ?? "NONE",
            recorder.Pending.Count + recorder.PendingEvents.Count,
            flight is not null && !flight.Recording,
            recorder.Warning);
    }

    public static string Export(HermesDiagnosticReport report)
    {
        var folder = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
            "AirInter", "Hermes", "diagnostics");
        Directory.CreateDirectory(folder);
        var path = Path.Combine(folder, $"diagnostic-{DateTimeOffset.UtcNow:yyyyMMdd-HHmmss}.json");
        File.WriteAllText(path, JsonSerializer.Serialize(report, new JsonSerializerOptions { WriteIndented = true }));
        return path;
    }

    public static string? LastCrashPath()
    {
        var path = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
            "AirInter", "Hermes", "last-crash.json");
        return File.Exists(path) ? path : null;
    }

    public static void RecordCrash(Exception exception)
    {
        try {
            var folder = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
                "AirInter", "Hermes");
            Directory.CreateDirectory(folder);
            var payload = new {
                generated_at = DateTimeOffset.UtcNow,
                version = UpdateService.CurrentVersion,
                exception = exception.GetType().FullName,
                message = exception.Message,
                stack = exception.StackTrace
            };
            File.WriteAllText(Path.Combine(folder, "last-crash.json"),
                JsonSerializer.Serialize(payload, new JsonSerializerOptions { WriteIndented = true }));
        } catch { /* Crash reporting must never hide the original failure. */ }
    }

    private static string SanitizeServer(string value)
    {
        if (!Uri.TryCreate(value, UriKind.Absolute, out var uri)) return "not-configured";
        return uri.GetLeftPart(UriPartial.Authority);
    }
}
