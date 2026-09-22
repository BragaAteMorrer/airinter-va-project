using System.Diagnostics;
using System.Reflection;
using System.Text.Json;
namespace Promethee.Acars;
internal sealed record HermesRelease(string Version, string DownloadUrl, bool Mandatory, string? Sha256);
internal static class UpdateService
{
 public static string CurrentVersion => Assembly.GetExecutingAssembly().GetCustomAttribute<AssemblyInformationalVersionAttribute>()?.InformationalVersion ?? Assembly.GetExecutingAssembly().GetName().Version?.ToString() ?? "0.0.0";
 public static async Task<HermesRelease?> CheckAsync(HttpClient http, Uri server, CancellationToken ct = default)
 {
  using var response = await http.GetAsync(new Uri(server, "/api/v1/hermes/releases/latest"), ct);
  if (!response.IsSuccessStatusCode) return null;
  using var document = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
  var root = document.RootElement.TryGetProperty("data", out var data) ? data : document.RootElement;
  var version = root.GetProperty("version").GetString(); var url = root.GetProperty("download_url").GetString();
  if (string.IsNullOrWhiteSpace(version) || string.IsNullOrWhiteSpace(url)) return null;
  return new(version, url, root.TryGetProperty("mandatory", out var required) && required.GetBoolean(), root.TryGetProperty("sha256", out var checksum) ? checksum.GetString() : null);
 }
 public static bool IsNewer(string candidate) { static Version P(string v) { var c=v.Split('+','-',2)[0].TrimStart('v','V'); return Version.TryParse(c,out var p)?p:new Version(0,0); } return P(candidate)>P(CurrentVersion); }
 public static void OpenDownload(HermesRelease release) => Process.Start(new ProcessStartInfo(release.DownloadUrl){UseShellExecute=true});
}