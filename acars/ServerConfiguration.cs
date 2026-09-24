namespace Promethee;

/// <summary>Resolves the Air Inter Prométhée endpoint; overrides are reserved to managed environments.</summary>
public static class ServerConfiguration
{
    public const string DefaultServer = "https://promethee.airinter-va.org";

    public static string Get()
    {
        // A legacy registry value must never silently redirect a pilot to a retired server.
        var overrideUrl = Environment.GetEnvironmentVariable("PROMETHEE_ACARS_SERVER");
        return IsValid(overrideUrl) ? overrideUrl!.TrimEnd('/') : DefaultServer;
    }

    public static string Source() => IsValid(Environment.GetEnvironmentVariable("PROMETHEE_ACARS_SERVER"))
        ? "variable d’environnement PROMETHEE_ACARS_SERVER" : "configuration de production intégrée";

    public static bool IsValid(string? server) => Uri.TryCreate(server, UriKind.Absolute, out var uri)
        && uri.Scheme == Uri.UriSchemeHttps && string.IsNullOrEmpty(uri.UserInfo)
        && string.IsNullOrEmpty(uri.Query) && string.IsNullOrEmpty(uri.Fragment);
}
