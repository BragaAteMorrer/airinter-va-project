using System.Net.Http.Json;
using System.Text.Json;

namespace Promethee;

public sealed class PhpVmsClient
{
    private readonly HttpClient http = new(new HttpClientHandler { AllowAutoRedirect = false }) { Timeout = TimeSpan.FromSeconds(20) };
    private string credential = "";
    private CredentialKind credentialKind;

    public string Server { get; private set; } = "";
    public bool Connected => credential.Length > 0;

    public PhpVmsClient() => http.DefaultRequestHeaders.UserAgent.ParseAdd("Promethee-ACARS/2.0");

    public void ConfigureApiKey(string server, string apiKey)
    {
        var validatedServer = ValidateServer(server);
        if (apiKey.Length < 10 || apiKey.Length > 200) throw new InvalidOperationException("Clé API invalide.");
        Server = validatedServer;
        credential = apiKey;
        credentialKind = CredentialKind.ApiKey;
    }

    public async Task<JsonElement> SignIn(string server, string login, string password)
    {
        var validatedServer = ValidateServer(server);
        if (string.IsNullOrWhiteSpace(login) || string.IsNullOrWhiteSpace(password))
            throw new InvalidOperationException("Saisissez votre identifiant phpVMS et votre mot de passe.");

        using var request = new HttpRequestMessage(HttpMethod.Post, validatedServer + "/api/acars/session") {
            Content = JsonContent.Create(new { login, password }),
        };
        request.Headers.Add("Accept", "application/json");
        using var response = await http.SendAsync(request);
        if (!response.IsSuccessStatusCode)
            throw new InvalidOperationException("Connexion refusée. Vérifiez vos identifiants et l'activation de l'accès ACARS.");

        var json = await response.Content.ReadFromJsonAsync<JsonElement>();
        var data = json.TryGetProperty("data", out var wrapped) ? wrapped : json;
        if (!data.TryGetProperty("access_token", out var token) || string.IsNullOrWhiteSpace(token.GetString()))
            throw new InvalidOperationException("Le serveur n'a pas fourni de session ACARS valide.");

        // The password never leaves this request and neither it nor the token is written to disk.
        Server = validatedServer;
        credential = token.GetString()!;
        credentialKind = CredentialKind.Bearer;
        return await Send("user");
    }

    public async Task<JsonElement> Send(string path, object? body = null)
    {
        if (!Connected) throw new InvalidOperationException("Connectez votre compte.");
        using var request = new HttpRequestMessage(body is null ? HttpMethod.Get : HttpMethod.Post, Server + "/api/" + path);
        if (credentialKind == CredentialKind.Bearer) request.Headers.Authorization = new("Bearer", credential);
        else request.Headers.Add("X-API-Key", credential);
        request.Headers.Add("Accept", "application/json");
        if (body is not null) request.Content = JsonContent.Create(body);
        using var response = await http.SendAsync(request);
        // Legacy phpVMS responses can include sensitive information. Do not reflect them in the local UI.
        if (!response.IsSuccessStatusCode)
            throw new InvalidOperationException("phpVMS : erreur HTTP " + (int)response.StatusCode + ". Vérifiez le compte, les droits et les données du vol.");
        var json = await response.Content.ReadFromJsonAsync<JsonElement>();
        return json.TryGetProperty("data", out var data) ? data : json;
    }

    private static string ValidateServer(string server)
    {
        if (!Uri.TryCreate(server, UriKind.Absolute, out var uri)
            || !(uri.Scheme == "https" || (uri.Scheme == "http" && uri.IsLoopback))
            || !string.IsNullOrEmpty(uri.UserInfo) || !string.IsNullOrEmpty(uri.Query) || !string.IsNullOrEmpty(uri.Fragment))
            throw new InvalidOperationException("Utilisez l'URL HTTPS de la compagnie (HTTP est autorisé uniquement en local).");
        return uri.AbsoluteUri.TrimEnd('/');
    }

    private enum CredentialKind { ApiKey, Bearer }
}
