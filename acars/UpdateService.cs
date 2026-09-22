using System.IO;
using System.Diagnostics;
using System.Net.Http;
using System.Reflection;
using System.Security.Cryptography;
using System.Text.Json;

namespace Promethee.Acars;

internal sealed record HermesRelease(string Version, string DownloadUrl, bool Mandatory, string? Sha256, string Channel, string? Notes, string? PublishedAt, string? ReleaseUrl);

internal static class UpdateService
{
    public static string CurrentVersion => Assembly.GetExecutingAssembly().GetCustomAttribute<AssemblyInformationalVersionAttribute>()?.InformationalVersion ?? Assembly.GetExecutingAssembly().GetName().Version?.ToString() ?? "0.0.0";

    public static async Task<HermesRelease?> CheckAsync(HttpClient http, Uri server, CancellationToken ct = default)
    {
        using var response = await http.GetAsync(new Uri(server, "/api/v1/hermes/releases/latest"), ct);
        if (!response.IsSuccessStatusCode) return null;
        using var document = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        var root = document.RootElement.TryGetProperty("data", out var data) ? data : document.RootElement;
        var version = root.TryGetProperty("version", out var versionNode) ? versionNode.GetString() : null;
        var url = root.TryGetProperty("download_url", out var urlNode) ? urlNode.GetString() : null;
        if (string.IsNullOrWhiteSpace(version) || string.IsNullOrWhiteSpace(url)) return null;
        return new(version, url,
            root.TryGetProperty("mandatory", out var required) && required.ValueKind == JsonValueKind.True,
            root.TryGetProperty("sha256", out var checksum) ? checksum.GetString() : null,
            root.TryGetProperty("channel", out var channel) ? channel.GetString() ?? "stable" : "stable",
            root.TryGetProperty("notes", out var notes) ? notes.GetString() : null,
            root.TryGetProperty("published_at", out var published) ? published.GetString() : null,
            root.TryGetProperty("release_url", out var releaseUrl) ? releaseUrl.GetString() : null);
    }

    public static bool IsNewer(string candidate)
    {
        static Version Parse(string value) { var clean=value.Split(new[] { '+', '-' },2,StringSplitOptions.None)[0].TrimStart('v','V'); return Version.TryParse(clean,out var parsed)?parsed:new Version(0,0); }
        return Parse(candidate)>Parse(CurrentVersion);
    }

    public static async Task<string> DownloadAndVerifyAsync(HermesRelease release, IProgress<int>? progress = null, CancellationToken ct = default)
    {
        var uri = new Uri(release.DownloadUrl);
        if (uri.Scheme != Uri.UriSchemeHttps) throw new InvalidOperationException("La mise à jour Hermès doit utiliser HTTPS.");
        var directory = Path.Combine(Path.GetTempPath(), "AirInter", "Hermes", "updates");
        Directory.CreateDirectory(directory);
        var path = Path.Combine(directory, $"Hermes-ACARS-Setup-{release.Version}.exe");

        using var http = new HttpClient { Timeout = TimeSpan.FromMinutes(10) };
        using var response = await http.GetAsync(uri, HttpCompletionOption.ResponseHeadersRead, ct);
        response.EnsureSuccessStatusCode();
        var total = response.Content.Headers.ContentLength;
        await using (var source = await response.Content.ReadAsStreamAsync(ct))
        await using (var target = File.Create(path)) {
            var buffer = new byte[81920]; long readTotal=0; int read;
            while ((read = await source.ReadAsync(buffer, ct)) > 0) {
                await target.WriteAsync(buffer.AsMemory(0, read), ct); readTotal += read;
                if (total > 0) progress?.Report((int)Math.Min(100, readTotal * 100 / total.Value));
            }
        }

        if (!string.IsNullOrWhiteSpace(release.Sha256)) {
            await using var stream = File.OpenRead(path);
            var actual = Convert.ToHexString(await SHA256.HashDataAsync(stream, ct)).ToLowerInvariant();
            if (!actual.Equals(release.Sha256.Trim(), StringComparison.OrdinalIgnoreCase)) {
                File.Delete(path); throw new InvalidOperationException("Le contrôle d’intégrité SHA-256 de la mise à jour a échoué.");
            }
        }
        return path;
    }

    public static void LaunchInstaller(string installerPath) => Process.Start(new ProcessStartInfo(installerPath){UseShellExecute=true});
}
