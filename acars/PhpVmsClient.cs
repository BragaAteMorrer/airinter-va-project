using System.Net.Http.Json;
using System.Net.Http;
using System.Text.Json;

namespace Promethee;

public sealed class PhpVmsClient
{
    private readonly HttpClient http = new(new HttpClientHandler { AllowAutoRedirect = false }) { Timeout = TimeSpan.FromSeconds(20) };
    private string credential = "";
    private CredentialKind credentialKind;

    public string Server { get; private set; } = "";
    public bool Connected => credential.Length > 0;
    public string LoginEndpoint => string.IsNullOrWhiteSpace(Server) ? "" : Server + "/api/acars/session";

    public PhpVmsClient() => http.DefaultRequestHeaders.UserAgent.ParseAdd("Promethee-ACARS/2.1");

    public void ConfigureApiKey(string server, string apiKey)
    {
        Server = ValidateServer(server);
        if (apiKey.Length < 10 || apiKey.Length > 200) throw new InvalidOperationException("Clé API invalide.");
        credential = apiKey;
        credentialKind = CredentialKind.ApiKey;
    }

    public async Task<JsonElement> SignIn(string server, string login, string password)
    {
        var validatedServer = ValidateServer(server);
        Server = validatedServer;
        if (string.IsNullOrWhiteSpace(login) || string.IsNullOrWhiteSpace(password))
            throw new InvalidOperationException("Saisissez votre identifiant et votre mot de passe.");
        try {
            using var request = new HttpRequestMessage(HttpMethod.Post, validatedServer + "/api/acars/session") { Content = JsonContent.Create(new { login, password }) };
            request.Headers.Add("Accept", "application/json");
            using var response = await http.SendAsync(request);
            if (!response.IsSuccessStatusCode) {
                var responseBody = await response.Content.ReadAsStringAsync();
                var serverMessage = SafeServerMessage(responseBody);
                var redirect = response.Headers.Location?.ToString();
                System.Diagnostics.Trace.WriteLine($"ACARS login {validatedServer}/api/acars/session returned {(int)response.StatusCode}: {responseBody}");
                throw new InvalidOperationException(LoginMessage(response.StatusCode, serverMessage, redirect));
            }
            var json = await response.Content.ReadFromJsonAsync<JsonElement>();
            var data = json.TryGetProperty("data", out var wrapped) ? wrapped : json;
            if (!data.TryGetProperty("access_token", out var token) || string.IsNullOrWhiteSpace(token.GetString()))
                throw new InvalidOperationException("Impossible de se connecter au serveur Prométhée.");
            Server = validatedServer;
            credential = token.GetString()!;
            credentialKind = CredentialKind.Bearer;
            return await Send("user");
        } catch (HttpRequestException ex) {
            System.Diagnostics.Trace.WriteLine($"ACARS login transport failure: {ex}");
            throw new InvalidOperationException($"Connexion réseau impossible vers {validatedServer} ({ex.GetType().Name}: {ex.Message}).");
        } catch (TaskCanceledException) {
            System.Diagnostics.Trace.WriteLine($"ACARS login timeout vers {validatedServer}");
            throw new InvalidOperationException($"Délai dépassé en contactant {validatedServer}/api/acars/session.");
        } catch (JsonException ex) {
            System.Diagnostics.Trace.WriteLine($"ACARS login invalid JSON from {validatedServer}: {ex}");
            throw new InvalidOperationException($"Le serveur {validatedServer} a répondu, mais pas avec le JSON attendu par Hermès.");
        }
    }

    public async Task<JsonElement> Send(string path, object? body = null)
    {
        if (!Connected) throw new InvalidOperationException("Connectez-vous à votre compte pilote.");
        try {
            using var request = new HttpRequestMessage(body is null ? HttpMethod.Get : HttpMethod.Post, Server + "/api/" + path);
            if (credentialKind == CredentialKind.Bearer) request.Headers.Authorization = new("Bearer", credential);
            else request.Headers.Add("X-API-Key", credential);
            request.Headers.Add("Accept", "application/json");
            if (body is not null) request.Content = JsonContent.Create(body);
            using var response = await http.SendAsync(request);
            if (!response.IsSuccessStatusCode) {
                var responseBody = await response.Content.ReadAsStringAsync();
                System.Diagnostics.Trace.WriteLine($"ACARS API {path} returned {(int)response.StatusCode}: {responseBody}");
                var serverMessage = SafeServerMessage(responseBody);
                throw new InvalidOperationException(response.StatusCode switch {
                    System.Net.HttpStatusCode.Unauthorized => "Votre session a expiré. Connectez-vous à nouveau.",
                    System.Net.HttpStatusCode.Forbidden => serverMessage ?? "Votre compte ne permet pas cette opération.",
                    System.Net.HttpStatusCode.NotFound => serverMessage ?? "La réservation ou le vol demandé n’existe plus.",
                    System.Net.HttpStatusCode.UnprocessableEntity => serverMessage ?? "Les informations du PIREP sont incomplètes ou non valides.",
                    System.Net.HttpStatusCode.ServiceUnavailable => serverMessage ?? "Le service demandé est temporairement indisponible.",
                    _ => $"Prométhée a refusé la demande (HTTP {(int)response.StatusCode})."
                });
            }
            var json = await response.Content.ReadFromJsonAsync<JsonElement>();
            return UnwrapData(json);
        } catch (HttpRequestException ex) {
            System.Diagnostics.Trace.WriteLine($"ACARS API transport failure: {ex}");
            throw new InvalidOperationException("Impossible de se connecter au serveur Prométhée.");
        } catch (TaskCanceledException ex) {
            System.Diagnostics.Trace.WriteLine($"ACARS API timeout: {ex}");
            throw new InvalidOperationException("Impossible de se connecter au serveur Prométhée.");
        }
    }

    private static JsonElement UnwrapData(JsonElement json)
    {
        // phpVMS endpoints do not all return the same envelope: collection
        // endpoints may legitimately return a top-level JSON array. Calling
        // TryGetProperty on an Array throws InvalidOperationException.
        return json.ValueKind == JsonValueKind.Object && json.TryGetProperty("data", out var data)
            ? data
            : json;
    }

    private static string? SafeServerMessage(string responseBody)
    {
        try {
            using var document = JsonDocument.Parse(responseBody);
            var root = document.RootElement;
            string? message = root.TryGetProperty("message", out var direct) ? direct.GetString() : null;
            if (message is null && root.TryGetProperty("error", out var error) && error.ValueKind == JsonValueKind.Object
                && error.TryGetProperty("message", out var nested)) message = nested.GetString();
            message = message?.Trim();
            return string.IsNullOrWhiteSpace(message) || message.Length > 240 ? null : message;
        } catch (JsonException) {
            return null;
        }
    }

    private static string LoginMessage(System.Net.HttpStatusCode status, string? serverMessage, string? redirect) => status switch {
        System.Net.HttpStatusCode.BadRequest => serverMessage ?? "Prométhée a refusé la requête de connexion (HTTP 400).",
        System.Net.HttpStatusCode.Unauthorized => serverMessage ?? "Identifiant ou mot de passe incorrect.",
        System.Net.HttpStatusCode.Forbidden => serverMessage ?? "Compte pilote non autorisé à utiliser Hermès.",
        System.Net.HttpStatusCode.NotFound => "Endpoint Hermès introuvable sur Prométhée (HTTP 404 : /api/acars/session). Le serveur n’est probablement pas à jour.",
        System.Net.HttpStatusCode.MethodNotAllowed => "La route /api/acars/session existe mais refuse POST (HTTP 405). Vérifiez les routes API déployées.",
        System.Net.HttpStatusCode.TooManyRequests => "Trop de tentatives. Attendez une minute avant de réessayer.",
        >= System.Net.HttpStatusCode.InternalServerError => serverMessage ?? $"Erreur serveur Prométhée (HTTP {(int)status}). Consultez les logs Laravel.",
        _ when (int)status is >= 300 and < 400 => $"Prométhée a redirigé la connexion (HTTP {(int)status}) vers {redirect ?? "une autre URL"}. Hermès refuse les redirections d’authentification.",
        _ => serverMessage ?? $"Échec de connexion Prométhée (HTTP {(int)status})."
    };

    private static string ValidateServer(string server)
    {
        if (!Uri.TryCreate(server, UriKind.Absolute, out var uri)
            || uri.Scheme != Uri.UriSchemeHttps || !string.IsNullOrEmpty(uri.UserInfo)
            || !string.IsNullOrEmpty(uri.Query) || !string.IsNullOrEmpty(uri.Fragment))
            throw new InvalidOperationException("Utilisez une URL HTTPS valide.");
        return uri.AbsoluteUri.TrimEnd('/');
    }

    private enum CredentialKind { ApiKey, Bearer }
}
