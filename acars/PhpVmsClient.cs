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
        if (string.IsNullOrWhiteSpace(login) || string.IsNullOrWhiteSpace(password))
            throw new InvalidOperationException("Saisissez votre identifiant et votre mot de passe.");
        try {
            using var request = new HttpRequestMessage(HttpMethod.Post, validatedServer + "/api/acars/session") { Content = JsonContent.Create(new { login, password }) };
            request.Headers.Add("Accept", "application/json");
            using var response = await http.SendAsync(request);
            if (!response.IsSuccessStatusCode) throw new InvalidOperationException(LoginMessage(response.StatusCode));
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
            throw new InvalidOperationException("Impossible de se connecter au serveur Prométhée.");
        } catch (TaskCanceledException ex) {
            System.Diagnostics.Trace.WriteLine($"ACARS login timeout: {ex}");
            throw new InvalidOperationException("Impossible de se connecter au serveur Prométhée.");
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
                throw new InvalidOperationException(response.StatusCode switch {
                    System.Net.HttpStatusCode.Unauthorized => "Votre session a expiré. Connectez-vous à nouveau.",
                    System.Net.HttpStatusCode.Forbidden => "Votre compte ne permet pas cette opération.",
                    System.Net.HttpStatusCode.NotFound => "La réservation ou le vol demandé n’existe plus.",
                    System.Net.HttpStatusCode.UnprocessableEntity => "Les informations du PIREP sont incomplètes ou non valides.",
                    _ => $"Prométhée a refusé la demande (HTTP {(int)response.StatusCode})."
                });
            }
            var json = await response.Content.ReadFromJsonAsync<JsonElement>();
            return json.TryGetProperty("data", out var data) ? data : json;
        } catch (HttpRequestException ex) {
            System.Diagnostics.Trace.WriteLine($"ACARS API transport failure: {ex}");
            throw new InvalidOperationException("Impossible de se connecter au serveur Prométhée.");
        } catch (TaskCanceledException ex) {
            System.Diagnostics.Trace.WriteLine($"ACARS API timeout: {ex}");
            throw new InvalidOperationException("Impossible de se connecter au serveur Prométhée.");
        }
    }

    private static string LoginMessage(System.Net.HttpStatusCode status) => status switch {
        System.Net.HttpStatusCode.Unauthorized => "Identifiant ou mot de passe incorrect.",
        System.Net.HttpStatusCode.TooManyRequests => "Trop de tentatives. Attendez une minute avant de réessayer.",
        _ => "Impossible de se connecter au serveur Prométhée."
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
