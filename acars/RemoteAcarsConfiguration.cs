using System.Text.Json;

namespace Promethee;

/// <summary>Validated subset of the remote, declarative Promethee ACARS policy.</summary>
public sealed record RemoteAcarsConfiguration(
    int PositionIntervalSeconds,
    bool AllowSimulationRate,
    bool AutoSendAllowed,
    bool ConfirmationRequired,
    string? MinimumVersion,
    string? LatestVersion,
    Uri? ReleaseNotesUri,
    Uri? DownloadUri)
{
    public static RemoteAcarsConfiguration Default { get; } = new(
        PositionIntervalSeconds: 15,
        AllowSimulationRate: false,
        AutoSendAllowed: false,
        ConfirmationRequired: true,
        MinimumVersion: null,
        LatestVersion: null,
        ReleaseNotesUri: null,
        DownloadUri: null);

    public static RemoteAcarsConfiguration Parse(JsonElement json)
    {
        var live = RequiredObject(json, "live");
        var tracking = RequiredObject(json, "tracking");
        var pirep = RequiredObject(json, "pirep");
        var client = RequiredObject(json, "client");

        return new(
            RequiredInt(live, "position_interval_seconds", 5, 300),
            RequiredBoolean(tracking, "allow_simulation_rate"),
            RequiredBoolean(pirep, "auto_send_allowed"),
            RequiredBoolean(pirep, "confirmation_required"),
            OptionalVersion(client, "minimum_version"),
            OptionalVersion(client, "latest_version"),
            OptionalHttpsUri(client, "release_notes_url"),
            OptionalHttpsUri(client, "download_url"));
    }

    private static JsonElement RequiredObject(JsonElement parent, string name) =>
        parent.TryGetProperty(name, out var value) && value.ValueKind == JsonValueKind.Object
            ? value : throw new InvalidOperationException("Configuration ACARS distante invalide.");

    private static int RequiredInt(JsonElement parent, string name, int min, int max) =>
        parent.TryGetProperty(name, out var value) && value.TryGetInt32(out var number) && number >= min && number <= max
            ? number : throw new InvalidOperationException("Configuration ACARS distante invalide.");

    private static bool RequiredBoolean(JsonElement parent, string name) =>
        parent.TryGetProperty(name, out var value) && (value.ValueKind is JsonValueKind.True or JsonValueKind.False)
            ? value.GetBoolean() : throw new InvalidOperationException("Configuration ACARS distante invalide.");

    private static string? OptionalVersion(JsonElement parent, string name)
    {
        if (!parent.TryGetProperty(name, out var value) || value.ValueKind == JsonValueKind.Null) return null;
        var version = value.GetString();
        return Version.TryParse(version, out var parsed) && parsed.Major >= 0 ? version : null;
    }

    private static Uri? OptionalHttpsUri(JsonElement parent, string name)
    {
        if (!parent.TryGetProperty(name, out var value) || value.ValueKind == JsonValueKind.Null) return null;
        return Uri.TryCreate(value.GetString(), UriKind.Absolute, out var uri) && uri.Scheme == Uri.UriSchemeHttps
            ? uri : null;
    }
}
