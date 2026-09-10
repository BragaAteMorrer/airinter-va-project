using System.Net.Http.Json;
using System.Text.Json;
namespace Promethee;
public sealed class PhpVmsClient
{
    private readonly HttpClient http=new(new HttpClientHandler{AllowAutoRedirect=false}){Timeout=TimeSpan.FromSeconds(20)};
    private string key="";
    public string Server {get;private set;}="";
    public bool Connected=>key.Length>0;
    public void Configure(string server,string apiKey) {
        if(!Uri.TryCreate(server,UriKind.Absolute,out var uri) || !(uri.Scheme=="https" || (uri.Scheme=="http" && uri.IsLoopback))
            || !string.IsNullOrEmpty(uri.UserInfo) || !string.IsNullOrEmpty(uri.Query) || !string.IsNullOrEmpty(uri.Fragment))
            throw new InvalidOperationException("Utiliser l’URL HTTPS de la compagnie (HTTP autorisé uniquement en local).");
        if(apiKey.Length<10 || apiKey.Length>200) throw new InvalidOperationException("Clé API invalide.");
        Server=uri.AbsoluteUri.TrimEnd('/');key=apiKey;
    }
    public async Task<JsonElement> Send(string path,object? body=null) {
        if(!Connected) throw new InvalidOperationException("Connectez votre compte.");
        using var request=new HttpRequestMessage(body is null?HttpMethod.Get:HttpMethod.Post,Server+"/api/"+path);
        request.Headers.Add("X-API-Key",key);
        request.Headers.Add("Accept","application/json");
        if(body is not null)request.Content=JsonContent.Create(body);
        using var response=await http.SendAsync(request);
        // Never reflect a remote error body: legacy phpVMS may include the API key in its error text.
        if(!response.IsSuccessStatusCode) throw new InvalidOperationException("phpVMS : erreur HTTP "+(int)response.StatusCode+". Vérifier le compte, les droits et les données du vol.");
        var json=await response.Content.ReadFromJsonAsync<JsonElement>();
        return json.TryGetProperty("data",out var data)?data:json;
    }
}
