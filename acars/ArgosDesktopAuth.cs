using System.Diagnostics;
using System.Net;
using System.Net.Http;
using System.Net.Http.Json;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;

namespace Promethee;

public sealed class ArgosDesktopAuth
{
    private const int CallbackPort = 47821;
    private const string DefaultIssuer = "https://argos.airinter-va.org";
    private readonly HttpClient http = new() { Timeout = TimeSpan.FromSeconds(20) };

    public async Task<JsonElement> SignInAsync(PhpVmsClient promethee, string prometheeServer)
    {
        var metadata = await LoadMetadataAsync();
        var storedRefreshToken = ArgosTokenStore.Load(metadata.ClientId);

        if (!string.IsNullOrWhiteSpace(storedRefreshToken)) {
            try {
                var refreshed = await ExchangeRefreshTokenAsync(metadata, storedRefreshToken);
                if (!string.IsNullOrWhiteSpace(refreshed.RefreshToken))
                    ArgosTokenStore.Save(metadata.ClientId, refreshed.RefreshToken);
                return await promethee.SignInWithArgos(prometheeServer, refreshed.AccessToken);
            } catch (Exception exception) {
                Trace.WriteLine($"Hermes Argos refresh failed: {exception}");
                ArgosTokenStore.Clear();
            }
        }

        var tokens = await InteractiveSignInAsync(metadata);
        if (!string.IsNullOrWhiteSpace(tokens.RefreshToken))
            ArgosTokenStore.Save(metadata.ClientId, tokens.RefreshToken);

        return await promethee.SignInWithArgos(prometheeServer, tokens.AccessToken);
    }

    public void ForgetSession() => ArgosTokenStore.Clear();

    private async Task<ArgosMetadata> LoadMetadataAsync()
    {
        var issuer = (Environment.GetEnvironmentVariable("HERMES_ARGOS_URL") ?? DefaultIssuer).TrimEnd('/');
        if (!Uri.TryCreate(issuer, UriKind.Absolute, out var issuerUri) || issuerUri.Scheme != Uri.UriSchemeHttps)
            throw new InvalidOperationException("L’adresse Argos configurée pour Hermès n’est pas une URL HTTPS valide.");

        var discovery = await GetJsonAsync(issuer + "/.well-known/openid-configuration", "configuration OpenID Argos");
        var client = await GetJsonAsync(issuer + "/.well-known/hermes-client", "configuration du client Hermès");

        var discoveredIssuer = RequiredString(discovery, "issuer");
        var clientIssuer = RequiredString(client, "issuer");
        if (!SameIssuer(issuer, discoveredIssuer) || !SameIssuer(issuer, clientIssuer))
            throw new InvalidOperationException("Argos a renvoyé un issuer OIDC inattendu.");

        var redirect = RequiredString(client, "redirect_uri");
        if (!string.Equals(redirect, $"http://127.0.0.1:{CallbackPort}/callback", StringComparison.Ordinal))
            throw new InvalidOperationException("Le callback OAuth Hermès publié par Argos ne correspond pas au callback local attendu.");

        var scope = RequiredString(client, "scope");
        if (!scope.Split(' ', StringSplitOptions.RemoveEmptyEntries).Contains("hermes:operate", StringComparer.Ordinal))
            throw new InvalidOperationException("Le client OAuth Hermès n’a pas le scope hermes:operate.");

        return new ArgosMetadata(
            issuer,
            RequiredString(client, "client_id"),
            redirect,
            scope,
            RequiredString(discovery, "authorization_endpoint"),
            RequiredString(discovery, "token_endpoint"),
            RequiredString(discovery, "jwks_uri"));
    }

    private async Task<ArgosTokens> InteractiveSignInAsync(ArgosMetadata metadata)
    {
        var state = RandomBase64Url(32);
        var nonce = RandomBase64Url(32);
        var verifier = RandomBase64Url(64);
        var challenge = Base64Url(SHA256.HashData(Encoding.ASCII.GetBytes(verifier)));

        var listener = new System.Net.Sockets.TcpListener(IPAddress.Loopback, CallbackPort);
        try {
            listener.Start();
        } catch (System.Net.Sockets.SocketException exception) {
            throw new InvalidOperationException($"Hermès ne peut pas ouvrir le callback Argos sur 127.0.0.1:{CallbackPort}. Fermez l’autre application qui utilise ce port puis réessayez.", exception);
        }

        var authorizationUrl = metadata.AuthorizationEndpoint + "?" + FormQuery(new Dictionary<string, string> {
            ["client_id"] = metadata.ClientId,
            ["redirect_uri"] = metadata.RedirectUri,
            ["response_type"] = "code",
            ["scope"] = metadata.Scope,
            ["state"] = state,
            ["nonce"] = nonce,
            ["code_challenge"] = challenge,
            ["code_challenge_method"] = "S256",
        });

        try {
            Process.Start(new ProcessStartInfo(authorizationUrl) { UseShellExecute = true });
        } catch (Exception exception) {
            throw new InvalidOperationException("Hermès n’a pas pu ouvrir votre navigateur pour la connexion Argos.", exception);
        }

        Dictionary<string, string> callback;
        try {
            using var tcpClient = await listener.AcceptTcpClientAsync().WaitAsync(TimeSpan.FromMinutes(5));
            callback = await ReadCallbackAsync(tcpClient);
        } finally {
            listener.Stop();
        }

        if (!callback.TryGetValue("state", out var returnedState) || !CryptographicOperations.FixedTimeEquals(
                Encoding.UTF8.GetBytes(state), Encoding.UTF8.GetBytes(returnedState)))
            throw new InvalidOperationException("Argos a renvoyé un état OAuth invalide. La connexion a été annulée.");

        if (callback.TryGetValue("error", out var oauthError))
            throw new InvalidOperationException("Argos a refusé la connexion : " + oauthError + ".");

        if (!callback.TryGetValue("code", out var code) || string.IsNullOrWhiteSpace(code))
            throw new InvalidOperationException("Argos n’a pas renvoyé de code d’autorisation.");

        var tokens = await ExchangeAuthorizationCodeAsync(metadata, code, verifier);
        if (string.IsNullOrWhiteSpace(tokens.IdToken))
            throw new InvalidOperationException("Argos n’a pas renvoyé l’ID Token OIDC attendu.");

        await ValidateIdTokenAsync(metadata, tokens.IdToken, nonce);
        return tokens;
    }

    private async Task<ArgosTokens> ExchangeAuthorizationCodeAsync(ArgosMetadata metadata, string code, string verifier)
    {
        return await ExchangeAsync(metadata.TokenEndpoint, new Dictionary<string, string> {
            ["grant_type"] = "authorization_code",
            ["client_id"] = metadata.ClientId,
            ["redirect_uri"] = metadata.RedirectUri,
            ["code"] = code,
            ["code_verifier"] = verifier,
        });
    }

    private async Task<ArgosTokens> ExchangeRefreshTokenAsync(ArgosMetadata metadata, string refreshToken)
    {
        return await ExchangeAsync(metadata.TokenEndpoint, new Dictionary<string, string> {
            ["grant_type"] = "refresh_token",
            ["client_id"] = metadata.ClientId,
            ["refresh_token"] = refreshToken,
        });
    }

    private async Task<ArgosTokens> ExchangeAsync(string endpoint, Dictionary<string, string> form)
    {
        using var response = await http.PostAsync(endpoint, new FormUrlEncodedContent(form));
        var content = await response.Content.ReadAsStringAsync();
        if (!response.IsSuccessStatusCode) {
            var description = SafeOAuthError(content);
            throw new InvalidOperationException(description ?? $"Argos a refusé l’échange OAuth (HTTP {(int)response.StatusCode}).");
        }

        using var document = JsonDocument.Parse(content);
        var root = document.RootElement;
        var accessToken = RequiredString(root, "access_token");
        var refreshToken = root.TryGetProperty("refresh_token", out var refresh) && refresh.ValueKind == JsonValueKind.String
            ? refresh.GetString() : null;
        var idToken = root.TryGetProperty("id_token", out var id) && id.ValueKind == JsonValueKind.String
            ? id.GetString() : null;
        return new ArgosTokens(accessToken, refreshToken, idToken);
    }

    private async Task ValidateIdTokenAsync(ArgosMetadata metadata, string jwt, string expectedNonce)
    {
        var segments = jwt.Split('.');
        if (segments.Length != 3) throw new InvalidOperationException("L’ID Token Argos est mal formé.");

        using var headerDoc = JsonDocument.Parse(Base64UrlDecode(segments[0]));
        using var payloadDoc = JsonDocument.Parse(Base64UrlDecode(segments[1]));
        var header = headerDoc.RootElement;
        var payload = payloadDoc.RootElement;

        if (RequiredString(header, "alg") != "RS256")
            throw new InvalidOperationException("Algorithme de signature Argos inattendu.");
        var kid = RequiredString(header, "kid");

        var jwks = await GetJsonAsync(metadata.JwksUri, "clés de signature Argos");
        JsonElement? key = null;
        if (jwks.TryGetProperty("keys", out var keys) && keys.ValueKind == JsonValueKind.Array) {
            foreach (var candidate in keys.EnumerateArray()) {
                if (candidate.TryGetProperty("kid", out var candidateKid)
                    && candidateKid.ValueKind == JsonValueKind.String
                    && string.Equals(candidateKid.GetString(), kid, StringComparison.Ordinal)) {
                    key = candidate.Clone();
                    break;
                }
            }
        }
        if (key is null) throw new InvalidOperationException("La clé de signature Argos est inconnue.");

        using var rsa = RSA.Create();
        rsa.ImportParameters(new RSAParameters {
            Modulus = Base64UrlDecode(RequiredString(key.Value, "n")),
            Exponent = Base64UrlDecode(RequiredString(key.Value, "e")),
        });

        var signed = Encoding.ASCII.GetBytes(segments[0] + "." + segments[1]);
        var signature = Base64UrlDecode(segments[2]);
        if (!rsa.VerifyData(signed, signature, HashAlgorithmName.SHA256, RSASignaturePadding.Pkcs1))
            throw new InvalidOperationException("La signature de l’identité Argos est invalide.");

        if (!SameIssuer(metadata.Issuer, RequiredString(payload, "iss")))
            throw new InvalidOperationException("L’issuer de l’identité Argos est invalide.");

        var audienceOk = payload.TryGetProperty("aud", out var audience) && (
            audience.ValueKind == JsonValueKind.String && audience.GetString() == metadata.ClientId
            || audience.ValueKind == JsonValueKind.Array && audience.EnumerateArray().Any(x => x.ValueKind == JsonValueKind.String && x.GetString() == metadata.ClientId)
        );
        if (!audienceOk) throw new InvalidOperationException("L’audience de l’identité Argos est invalide.");

        if (!payload.TryGetProperty("exp", out var exp) || !exp.TryGetInt64(out var expiry)
            || DateTimeOffset.FromUnixTimeSeconds(expiry) < DateTimeOffset.UtcNow.AddMinutes(-1))
            throw new InvalidOperationException("L’identité Argos a expiré.");

        if (!payload.TryGetProperty("nonce", out var nonce) || nonce.ValueKind != JsonValueKind.String
            || !string.Equals(nonce.GetString(), expectedNonce, StringComparison.Ordinal))
            throw new InvalidOperationException("Le nonce OIDC Argos est invalide.");

        if (!payload.TryGetProperty("sub", out var subject) || subject.ValueKind != JsonValueKind.String
            || string.IsNullOrWhiteSpace(subject.GetString()))
            throw new InvalidOperationException("L’identité Argos ne contient pas de sujet.");
    }

    private async Task<JsonElement> GetJsonAsync(string url, string label)
    {
        using var response = await http.GetAsync(url);
        if (!response.IsSuccessStatusCode)
            throw new InvalidOperationException($"Impossible de charger la {label} (HTTP {(int)response.StatusCode}).");
        var json = await response.Content.ReadFromJsonAsync<JsonElement>();
        return json.Clone();
    }

    private static async Task<Dictionary<string, string>> ReadCallbackAsync(System.Net.Sockets.TcpClient client)
    {
        using var stream = client.GetStream();
        using var reader = new StreamReader(stream, Encoding.ASCII, false, 4096, leaveOpen: true);
        var requestLine = await reader.ReadLineAsync();
        if (string.IsNullOrWhiteSpace(requestLine)) throw new InvalidOperationException("Callback Argos vide.");

        string? line;
        do { line = await reader.ReadLineAsync(); } while (line is not null && line.Length > 0);

        var parts = requestLine.Split(' ');
        if (parts.Length < 2) throw new InvalidOperationException("Callback Argos invalide.");
        var target = parts[1];
        var question = target.IndexOf('?');
        var path = question >= 0 ? target[..question] : target;
        if (!string.Equals(path, "/callback", StringComparison.Ordinal))
            throw new InvalidOperationException("Callback Argos inattendu.");

        var html = "<!doctype html><meta charset=\"utf-8\"><title>Hermès</title><body style=\"font-family:Segoe UI,sans-serif;padding:40px\"><h1>Hermès</h1><p>Connexion Argos reçue. Vous pouvez fermer cet onglet et revenir dans Hermès.</p></body>";
        var bytes = Encoding.UTF8.GetBytes(html);
        var headers = Encoding.ASCII.GetBytes($"HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\nContent-Length: {bytes.Length}\r\nConnection: close\r\nCache-Control: no-store\r\n\r\n");
        await stream.WriteAsync(headers);
        await stream.WriteAsync(bytes);
        await stream.FlushAsync();

        return ParseQuery(question >= 0 ? target[(question + 1)..] : "");
    }

    private static Dictionary<string, string> ParseQuery(string query)
    {
        var result = new Dictionary<string, string>(StringComparer.Ordinal);
        foreach (var item in query.Split('&', StringSplitOptions.RemoveEmptyEntries)) {
            var separator = item.IndexOf('=');
            var key = separator >= 0 ? item[..separator] : item;
            var value = separator >= 0 ? item[(separator + 1)..] : "";
            result[Uri.UnescapeDataString(key.Replace("+", " "))] = Uri.UnescapeDataString(value.Replace("+", " "));
        }
        return result;
    }

    private static string FormQuery(IReadOnlyDictionary<string, string> values) =>
        string.Join("&", values.Select(pair => Uri.EscapeDataString(pair.Key) + "=" + Uri.EscapeDataString(pair.Value)));

    private static string RandomBase64Url(int size)
    {
        var bytes = RandomNumberGenerator.GetBytes(size);
        return Base64Url(bytes);
    }

    private static string Base64Url(byte[] value) => Convert.ToBase64String(value).TrimEnd('=').Replace('+', '-').Replace('/', '_');

    private static byte[] Base64UrlDecode(string value)
    {
        var normalized = value.Replace('-', '+').Replace('_', '/');
        normalized += normalized.Length % 4 switch { 2 => "==", 3 => "=", _ => "" };
        return Convert.FromBase64String(normalized);
    }

    private static string RequiredString(JsonElement element, string property)
    {
        if (!element.TryGetProperty(property, out var value) || value.ValueKind != JsonValueKind.String || string.IsNullOrWhiteSpace(value.GetString()))
            throw new InvalidOperationException($"Argos n’a pas renvoyé le champ {property} attendu.");
        return value.GetString()!;
    }

    private static bool SameIssuer(string left, string right) =>
        string.Equals(left.TrimEnd('/'), right.TrimEnd('/'), StringComparison.Ordinal);

    private static string? SafeOAuthError(string content)
    {
        try {
            using var document = JsonDocument.Parse(content);
            var root = document.RootElement;
            if (root.TryGetProperty("error_description", out var description) && description.ValueKind == JsonValueKind.String)
                return description.GetString();
            if (root.TryGetProperty("error", out var error) && error.ValueKind == JsonValueKind.String)
                return error.GetString();
        } catch { }
        return null;
    }

    private sealed record ArgosMetadata(
        string Issuer,
        string ClientId,
        string RedirectUri,
        string Scope,
        string AuthorizationEndpoint,
        string TokenEndpoint,
        string JwksUri);

    private sealed record ArgosTokens(string AccessToken, string? RefreshToken, string? IdToken);
}
