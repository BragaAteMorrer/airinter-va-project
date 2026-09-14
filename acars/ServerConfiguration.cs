using Microsoft.Win32;

namespace Promethee;

/// <summary>Machine-wide ACARS endpoint. HKLM writes require Windows administrator rights.</summary>
public static class ServerConfiguration
{
    private const string RegistryPath = @"SOFTWARE\AirInter\PrometheeACARS";
    private const string ServerValue = "Server";

    public static string? Get() => Registry.LocalMachine.OpenSubKey(RegistryPath, writable: false)?.GetValue(ServerValue) as string;

    public static void Set(string server)
    {
        if (!Uri.TryCreate(server, UriKind.Absolute, out var uri) || uri.Scheme != Uri.UriSchemeHttps || !string.IsNullOrEmpty(uri.UserInfo))
            throw new InvalidOperationException("L'URL doit être HTTPS, par exemple https://va.exemple.fr.");
        using var key = Registry.LocalMachine.CreateSubKey(RegistryPath, writable: true);
        key.SetValue(ServerValue, uri.AbsoluteUri.TrimEnd('/'), RegistryValueKind.String);
    }
}
