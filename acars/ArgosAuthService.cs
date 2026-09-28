using System.Diagnostics;
using System.Net;
using System.Net.Http.Json;
using System.Net.Sockets;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;

namespace Promethee;

public sealed record ArgosIdentityConfiguration(
    string Issuer,
    string ClientId,
    string RedirectUri,
    string Scope,
    string PkceMethod,
    bool Configured);

public sealed class ArgosAuthService
{
    private const int CallbackPort = 47821;
    private readonly HttpClient http = new() { Timeout = TimeSpan.FromSeconds(20) };
    private readonly PhpVmsClient promethee;
    private readonly string refreshTokenPath;

    public ArgosAuthService(PhpVmsClient promethee)
    {
        this.promethee = promethee;
        var root = Path.Combine(
            Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
            "AirInter",
            "Promethee");
        Directory.CreateDirectory(root);
        refreshTokenPath = Path.Combine(root, "argos-refresh.bin");
        http.DefaultRequestHeaders.UserAgent.ParseAdd("Hermes-ACARS/Argos");
    }

    public async Task<JsonElement> SignInInteractiveAsync(string prometheeServer, CancellationToken cancellationToken = default)
    {
        var configuration = await LoadConfigurationAsync(prometheeServer, cancellationToken);
        EnsureConfigured(configuration);

        var state = Base64Url(RandomNumberGenerator.GetBytes(32));
        var nonce = Base64Url(RandomNumberGenerator.GetBytes(32));
        var verifier = Base64Url(RandomNumberGenerator.GetBytes(64));
        var challenge = Base64Url(SHA256.HashData(Encoding.ASCII.GetBytes(verifier)));

        using var listener = new TcpListener(IPAddress.Loopback, CallbackPort);
        listener.Start(1);

        var authorizationUrl = BuildAuthorizeUrl(configuration, state, nonce, challenge);
        Process.Start(new ProcessStartInfo(authorizationUrl) { UseShellExecute = true });

        var callback = await WaitForCallbackAsync(listener, cancellationToken);
        if (!callback.TryGetValue("state", out var returnedState) || !CryptographicOperations.FixedTimeEquals(
                Encoding.UTF8.GetBytes(state),
                Encoding.UTF8.GetBytes(returnedState ?? string.Empty))) {
            throw new InvalidOperationException("La réponse Argos ne correspond pas à la session Hermès ouverte.");
        }

        if (callback.TryGetValue("error", out var oauthError) && !string.IsNullOrWhiteSpace(oauthError)) {
            throw new InvalidOperationException("Argos a refusé la connexion : " + oauthError + ".");
        }

        if (!callback.TryGetValue("code", out var code) || string.IsNullOrWhiteSpace(code)) {
            throw new InvalidOperationException("Argos n’a pas renvoyé de code d’autorisation.");
        }

        var tokens = await ExchangeCodeAsync(configuration, code, verifier, cancellationToken);
        var user = await promethee.SignInWithArgos(prometheeServer, tokens.AccessToken);

        if (!string.IsNullOrWhiteSpace(tokens.RefreshToken))
            SaveRefreshToken(tokens.RefreshToken);

        return user;
    }

    public async Task<JsonElement?> TryRestoreAsync(string prometheeServer, CancellationToken cancellationToken = default)
    {
        var refreshToken = LoadRefreshToken();
        if (string.IsNullOrWhiteSpace(refreshToken)) return null;

        try {
            var configuration = await LoadConfigurationAsync(prometheeServer, cancellationToken);
            EnsureConfigured(configuration);
            var tokens = await RefreshAsync(configuration, refreshToken, cancellationToken);
            var user = await promethee.SignInWithArgos(prometheeServer, tokens.AccessToken);

            if (!string.IsNullOrWhiteSpace(tokens.RefreshToken))
                SaveRefreshToken(tokens.RefreshToken);

            return user;
        } catch (Exception exception) when (exception is HttpRequestException or TaskCanceledException or InvalidOperationException) {
            Trace.WriteLine($"Argos silent restore failed: {exception}");
            return null;
        }
    }

    public void ForgetRefreshToken()
    {
        try {
            if (File.Exists(refreshTokenPath)) File.Delete(refreshTokenPath);
        } catch (IOException) { }
        catch (UnauthorizedAccessException) { }
    }

    private async Task<ArgosIdentityConfiguration> LoadConfigurationAsync(string prometheeServer, CancellationToken cancellationToken)
    {
        using var response = await http.GetAsync(
            prometheeServer.TrimEnd('/') + "/api/v1/hermes/identity-configuration",
            cancellationToken);

        if (!response.IsSuccessStatusCode)
            throw new InvalidOperationException("Prométhée ne publie pas encore la configuration Argos d’Hermès.");

        using var document = JsonDocument.Parse(await response.Content.ReadAsStringAsync(cancellationToken));
        var root = document.RootElement;
        var data = root.ValueKind == JsonValueKind.Object && root.TryGetProperty("data", out var wrapped)
            ? wrapped
            : root;

        return new ArgosIdentityConfiguration(
            Read(data, "issuer"),
            Read(data, "client_id"),
            Read(data, "redirect_uri"),
            Read(data, "scope"),
            Read(data, "pkce_method"),
            data.TryGetProperty("configured", out var configured) && configured.ValueKind == JsonValueKind.True);
    }

    private static void EnsureConfigured(ArgosIdentityConfiguration configuration)
    {
        if (!configuration.Configured || string.IsNullOrWhiteSpace(configuration.ClientId))
            throw new InvalidOperationException("Le Client ID Hermès n’est pas encore configuré sur Prométhée.");
        if (!Uri.TryCreate(configuration.Issuer, UriKind.Absolute, out var issuer) || issuer.Scheme != Uri.UriSchemeHttps)
            throw new InvalidOperationException("L’URL Argos publiée par Prométhée est invalide.");
        if (!string.Equals(configuration.PkceMethod, "S256", StringComparison.Ordinal))
            throw new InvalidOperationException("Argos doit imposer PKCE S256 pour Hermès.");
        if (!string.Equals(configuration.RedirectUri, "http://127.0.0.1:47821/callback", StringComparison.Ordinal))
            throw new InvalidOperationException("Le callback Hermès Argos ne correspond pas au callback local officiel.");
    }

    private static string BuildAuthorizeUrl(
        ArgosIdentityConfiguration configuration,
        string state,
        string nonce,
        string challenge)
    {
        var query = new Dictionary<string, string> {
            ["client_id"] = configuration.ClientId,
            ["redirect_uri"] = configuration.RedirectUri,
            ["response_type"] = "code",
            ["scope"] = configuration.Scope,
            ["state"] = state,
            ["nonce"] = nonce,
            ["code_challenge"] = challenge,
            ["code_challenge_method"] = "S256",
        };

        return configuration.Issuer.TrimEnd('/') + "/oauth/authorize?" +
            string.Join("&", query.Select(pair =>
                Uri.EscapeDataString(pair.Key) + "=" + Uri.EscapeDataString(pair.Value)));
    }

    private async Task<ArgosTokens> ExchangeCodeAsync(
        ArgosIdentityConfiguration configuration,
        string code,
        string verifier,
        CancellationToken cancellationToken)
    {
        var form = new Dictionary<string, string> {
            ["grant_type"] = "authorization_code",
            ["client_id"] = configuration.ClientId,
            ["redirect_uri"] = configuration.RedirectUri,
            ["code"] = code,
            ["code_verifier"] = verifier,
        };

        return await RequestTokensAsync(configuration.Issuer, form, cancellationToken);
    }

    private async Task<ArgosTokens> RefreshAsync(
        ArgosIdentityConfiguration configuration,
        string refreshToken,
        CancellationToken cancellationToken)
    {
        var form = new Dictionary<string, string> {
            ["grant_type"] = "refresh_token",
            ["client_id"] = configuration.ClientId,
            ["refresh_token"] = refreshToken,
            ["scope"] = configuration.Scope,
        };

        return await RequestTokensAsync(configuration.Issuer, form, cancellationToken);
    }

    private async Task<ArgosTokens> RequestTokensAsync(
        string issuer,
        IReadOnlyDictionary<string, string> values,
        CancellationToken cancellationToken)
    {
        using var response = await http.PostAsync(
            issuer.TrimEnd('/') + "/oauth/token",
            new FormUrlEncodedContent(values),
            cancellationToken);

        var body = await response.Content.ReadAsStringAsync(cancellationToken);
        if (!response.IsSuccessStatusCode) {
            Trace.WriteLine($"Argos token endpoint returned {(int)response.StatusCode}: {body}");
            throw new InvalidOperationException("Argos n’a pas pu finaliser la session Hermès.");
        }

        using var document = JsonDocument.Parse(body);
        var root = document.RootElement;
        var accessToken = Read(root, "access_token");
        var refreshToken = root.TryGetProperty("refresh_token", out var refresh) && refresh.ValueKind == JsonValueKind.String
            ? refresh.GetString() ?? string.Empty
            : string.Empty;
        var idToken = root.TryGetProperty("id_token", out var id) && id.ValueKind == JsonValueKind.String
            ? id.GetString() ?? string.Empty
            : string.Empty;

        if (string.IsNullOrWhiteSpace(accessToken) || string.IsNullOrWhiteSpace(idToken))
            throw new InvalidOperationException("La réponse OIDC Argos est incomplète.");

        return new ArgosTokens(accessToken, refreshToken, idToken);
    }

    private static async Task<Dictionary<string, string?>> WaitForCallbackAsync(
        TcpListener listener,
        CancellationToken cancellationToken)
    {
        using var timeout = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);
        timeout.CancelAfter(TimeSpan.FromMinutes(3));

        using var tcp = await listener.AcceptTcpClientAsync(timeout.Token);
        await using var stream = tcp.GetStream();
        using var reader = new StreamReader(stream, Encoding.ASCII, false, 4096, leaveOpen: true);
        var requestLine = await reader.ReadLineAsync(timeout.Token);
        if (string.IsNullOrWhiteSpace(requestLine))
            throw new InvalidOperationException("Callback Argos vide.");

        var parts = requestLine.Split(' ');
        if (parts.Length < 2)
            throw new InvalidOperationException("Callback Argos invalide.");

        var target = new Uri("http://127.0.0.1:" + CallbackPort + parts[1]);
        if (!string.Equals(target.AbsolutePath, "/callback", StringComparison.Ordinal))
            throw new InvalidOperationException("Callback Argos inattendu.");

        var query = ParseQuery(target.Query);
        var html = "<!doctype html><html lang=\"fr\"><meta charset=\"utf-8\"><title>Hermès</title>" +
            "<body style=\"font-family:system-ui;padding:40px\"><h1>Hermès</h1>" +
            "<p>Connexion Argos reçue. Vous pouvez fermer cette fenêtre et revenir dans Hermès.</p></body></html>";
        var bytes = Encoding.UTF8.GetBytes(html);
        var headers = Encoding.ASCII.GetBytes(
            "HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\nContent-Length: " +
            bytes.Length + "\r\nConnection: close\r\n\r\n");
        await stream.WriteAsync(headers, timeout.Token);
        await stream.WriteAsync(bytes, timeout.Token);
        await stream.FlushAsync(timeout.Token);

        return query;
    }

    private static Dictionary<string, string?> ParseQuery(string query)
    {
        var values = new Dictionary<string, string?>(StringComparer.Ordinal);
        foreach (var part in query.TrimStart('?').Split('&', StringSplitOptions.RemoveEmptyEntries)) {
            var pair = part.Split('=', 2);
            var key = Uri.UnescapeDataString(pair[0].Replace("+", " "));
            var value = pair.Length > 1 ? Uri.UnescapeDataString(pair[1].Replace("+", " ")) : string.Empty;
            values[key] = value;
        }
        return values;
    }

    private void SaveRefreshToken(string refreshToken)
    {
#if WINDOWS
        var plain = Encoding.UTF8.GetBytes(refreshToken);
        var encrypted = ProtectedData.Protect(plain, null, DataProtectionScope.CurrentUser);
        File.WriteAllBytes(refreshTokenPath, encrypted);
#endif
    }

    private string? LoadRefreshToken()
    {
#if WINDOWS
        try {
            if (!File.Exists(refreshTokenPath)) return null;
            var encrypted = File.ReadAllBytes(refreshTokenPath);
            var plain = ProtectedData.Unprotect(encrypted, null, DataProtectionScope.CurrentUser);
            return Encoding.UTF8.GetString(plain);
        } catch (CryptographicException) {
            ForgetRefreshToken();
        } catch (IOException) { }
        catch (UnauthorizedAccessException) { }
#endif
        return null;
    }

    private static string Base64Url(byte[] value) =>
        Convert.ToBase64String(value).TrimEnd('=').Replace('+', '-').Replace('/', '_');

    private static string Read(JsonElement element, string property)
    {
        if (!element.TryGetProperty(property, out var value) || value.ValueKind != JsonValueKind.String)
            return string.Empty;
        return value.GetString() ?? string.Empty;
    }

    private sealed record ArgosTokens(string AccessToken, string RefreshToken, string IdToken);
}
