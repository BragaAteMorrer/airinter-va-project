namespace Promethee;

#if WINDOWS
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;

internal static class ArgosTokenStore
{
    private static readonly byte[] Entropy = Encoding.UTF8.GetBytes("Air Inter Hermes Argos refresh token v1");
    private static readonly string DirectoryPath = Path.Combine(
        Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
        "AirInter", "Hermes");
    private static readonly string FilePath = Path.Combine(DirectoryPath, "argos-token.dat");

    public static string? Load(string clientId)
    {
        try {
            if (!File.Exists(FilePath)) return null;
            var protectedBytes = File.ReadAllBytes(FilePath);
            var clearBytes = ProtectedData.Unprotect(protectedBytes, Entropy, DataProtectionScope.CurrentUser);
            var payload = JsonSerializer.Deserialize<StoredToken>(clearBytes);
            if (payload is null || !string.Equals(payload.ClientId, clientId, StringComparison.Ordinal)
                || string.IsNullOrWhiteSpace(payload.RefreshToken)) {
                Clear();
                return null;
            }
            return payload.RefreshToken;
        } catch {
            Clear();
            return null;
        }
    }

    public static void Save(string clientId, string refreshToken)
    {
        if (string.IsNullOrWhiteSpace(clientId) || string.IsNullOrWhiteSpace(refreshToken)) return;
        Directory.CreateDirectory(DirectoryPath);
        var clearBytes = JsonSerializer.SerializeToUtf8Bytes(new StoredToken(clientId, refreshToken));
        var protectedBytes = ProtectedData.Protect(clearBytes, Entropy, DataProtectionScope.CurrentUser);
        var temporary = FilePath + ".tmp";
        File.WriteAllBytes(temporary, protectedBytes);
        File.Move(temporary, FilePath, true);
    }

    public static void Clear()
    {
        try { if (File.Exists(FilePath)) File.Delete(FilePath); } catch { }
    }

    private sealed record StoredToken(string ClientId, string RefreshToken);
}
#else
internal static class ArgosTokenStore
{
    public static string? Load(string clientId) => null;
    public static void Save(string clientId, string refreshToken) { }
    public static void Clear() { }
}
#endif
