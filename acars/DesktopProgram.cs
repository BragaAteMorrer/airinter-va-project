using System.Text.Json;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Threading;
using Promethee;

namespace PrometheeDesktop;

public static class DesktopProgram
{
    [STAThread] public static void Main(string[] args)
    {
        if (args.Length == 2 && args[0].Equals("--set-server", StringComparison.OrdinalIgnoreCase)) {
            try { ServerConfiguration.Set(args[1]); MessageBox.Show("Serveur ACARS enregistré pour tous les pilotes.", "Prométhée ACARS"); }
            catch (Exception e) { MessageBox.Show(e.Message + "\n\nLancez cette commande en administrateur.", "Prométhée ACARS", MessageBoxButton.OK, MessageBoxImage.Error); }
            return;
        }
        new Application().Run(new AcarsWindow());
    }
}

public sealed class AcarsWindow : Window
{
    private readonly PhpVmsClient client = new();
    private readonly SimConnectReader sim = new();
    private readonly FlightRecorder recorder = new();
    private readonly TelemetryService telemetry;
    private readonly TextBox server = new() { IsReadOnly = true, MinWidth = 320 };
    private readonly TextBox login = new();
    private readonly PasswordBox password = new();
    private readonly TextBox pirepId = new();
    private readonly TextBox flightNumber = new();
    private readonly TextBox airlineId = new();
    private readonly TextBox aircraftId = new();
    private readonly TextBox departure = new();
    private readonly TextBox arrival = new();
    private readonly TextBox output = new() { IsReadOnly = true, AcceptsReturn = true, TextWrapping = TextWrapping.Wrap, VerticalScrollBarVisibility = ScrollBarVisibility.Auto };
    private readonly TextBlock status = new() { Text = "Initialisation SimConnect…" };
    private readonly ComboBox theme = new() { Width = 180, ItemsSource = new[] { "Moderne", "Années 2000", "Minitel" }, SelectedIndex = 0 };
    private bool ticking;

    public AcarsWindow()
    {
        server.Text = ServerConfiguration.Get() ?? "Non configuré par l'administrateur";
        telemetry = new(sim, recorder, client);
        Title = "Prométhée ACARS — Air Inter"; Width = 1080; Height = 720; MinWidth = 800; MinHeight = 560;
        Content = BuildUi(); theme.SelectionChanged += (_, _) => ApplyTheme(); ApplyTheme();
        var timer = new DispatcherTimer { Interval = TimeSpan.FromSeconds(1) };
        timer.Tick += async (_, _) => await RefreshAsync(); timer.Start();
        Closed += (_, _) => sim.Dispose();
    }

    private UIElement BuildUi()
    {
        var tabs = new TabControl { Margin = new Thickness(16) };
        tabs.Items.Add(Tab("Connexion", Stack(
            Field("Serveur phpVMS (verrouillé par l'administrateur)", server), Field("Identifiant pilote ou e-mail", login), Field("Mot de passe", password),
            Button("Se connecter", LoginAsync))));
        tabs.Items.Add(Tab("Préparer le vol", Stack(
            Field("Compagnie", airlineId), Field("Numéro de vol", flightNumber), Field("Avion (ID phpVMS)", aircraftId),
            Field("Départ", departure), Field("Arrivée", arrival), Button("Pré-déposer le PIREP", PrefileAsync),
            Button("Rechercher mes réservations", BidsAsync))));
        tabs.Items.Add(Tab("ACARS", Stack(
            Field("ID PIREP", pirepId), Button("Démarrer l'enregistrement", Start), Button("Pause", Pause), Button("Reprendre", Resume),
            Button("Synchroniser maintenant", SyncAsync), Button("Afficher le rapport final", Report), Button("Déposer le PIREP", FileAsync))));
        tabs.Items.Add(Tab("Historique", Stack(Button("Afficher l'historique", HistoryAsync), Button("Afficher le diagnostic", DiagnosticsAsync))));
        var root = new DockPanel();
        var top = new DockPanel { Margin = new Thickness(16, 12, 16, 0) }; DockPanel.SetDock(theme, Dock.Right); top.Children.Add(theme); top.Children.Add(status);
        DockPanel.SetDock(top, Dock.Top); root.Children.Add(top);
        DockPanel.SetDock(output, Dock.Bottom); output.Height = 190; output.Margin = new Thickness(16); root.Children.Add(output); root.Children.Add(tabs);
        return root;
    }
    private static TabItem Tab(string title, UIElement content) => new() { Header = title, Content = new ScrollViewer { Content = content, Margin = new Thickness(12) } };
    private static StackPanel Stack(params UIElement[] children) { var p = new StackPanel { Margin = new Thickness(10) }; foreach (var x in children) p.Children.Add(x); return p; }
    private static UIElement Field(string label, Control control) => new StackPanel { Margin = new Thickness(0, 0, 0, 10), Children = { new TextBlock { Text = label }, control } };
    private static Button Button(string text, Func<Task> action) { var b = new Button { Content = text, Padding = new Thickness(12, 7, 12, 7), Margin = new Thickness(0, 4, 0, 4), HorizontalAlignment = HorizontalAlignment.Left }; b.Click += async (_, _) => await action(); return b; }
    private void Show(object value) => output.Text = value is string s ? s : JsonSerializer.Serialize(value, new JsonSerializerOptions { WriteIndented = true });
    private void ApplyTheme()
    {
        var name = theme.SelectedItem?.ToString();
        var (bg, fg, panel, accent, font) = name switch {
            "Minitel" => ("#000000", "#FFFFFF", "#001B38", "#FFFF00", "Consolas"),
            "Années 2000" => ("#B7C5D9", "#102040", "#EAF3FF", "#003399", "Tahoma"),
            _ => ("#091525", "#EDF5FF", "#13253B", "#FF4D55", "Segoe UI")
        };
        Background = Brush(bg); Foreground = Brush(fg); FontFamily = new System.Windows.Media.FontFamily(font);
        output.Background = Brush(panel); output.Foreground = Brush(fg); status.Foreground = Brush(accent);
    }
    private static System.Windows.Media.Brush Brush(string color) => new System.Windows.Media.SolidColorBrush((System.Windows.Media.Color)System.Windows.Media.ColorConverter.ConvertFromString(color));
    private async Task Guard(Func<Task> action) { try { await action(); } catch (Exception e) { Show(e.Message); } }
    private Task LoginAsync() => Guard(async () => {
        if (string.IsNullOrWhiteSpace(ServerConfiguration.Get())) throw new InvalidOperationException("Le serveur n'est pas configuré. Un administrateur doit exécuter set-server.ps1 une fois.");
        Show(await client.SignIn(server.Text.Trim(), login.Text.Trim(), password.Password));
    });
    private Task BidsAsync() => Guard(async () => Show(await client.Send("user/bids")));
    private Task PrefileAsync() => Guard(async () => {
        var result = await client.Send("pireps/prefile", new { airline_id=airlineId.Text.Trim(), flight_number=flightNumber.Text.Trim(), aircraft_id=aircraftId.Text.Trim(), dpt_airport_id=departure.Text.Trim(), arr_airport_id=arrival.Text.Trim(), source_name="Promethee ACARS" });
        if (result.TryGetProperty("id", out var id)) pirepId.Text = id.GetString() ?? ""; Show(result);
    });
    private Task Start() => Guard(() => { if (sim.Latest is null) throw new InvalidOperationException("Attendez la position MSFS."); recorder.Start(client.Server, pirepId.Text.Trim(), sim.Latest); Show("Enregistrement démarré."); return Task.CompletedTask; });
    private Task Pause() => Guard(() => { recorder.Pause(); Show("Enregistrement en pause."); return Task.CompletedTask; });
    private Task Resume() => Guard(() => { recorder.Resume(client.Server); Show("Enregistrement repris."); return Task.CompletedTask; });
    private Task SyncAsync() => Guard(async () => Show(new { sent = await TelemetryService.SendPending(client, recorder) }));
    private Task Report() => Guard(() => { var f = recorder.Flight ?? throw new InvalidOperationException("Aucun vol."); Show(new { f.Phase, f.Distance, f.FuelUsed, f.AirborneSeconds, f.LandingRate, f.Issues }); return Task.CompletedTask; });
    private Task HistoryAsync() => Guard(() => { Show(recorder.History); return Task.CompletedTask; });
    private Task DiagnosticsAsync() => Guard(() => { Show(new { connected=client.Connected, server=client.Server, simulator=sim.Status, latest=sim.Latest, flight=recorder.Flight, pending=recorder.Pending.Count + recorder.PendingEvents.Count }); return Task.CompletedTask; });
    private Task FileAsync() => Guard(async () => { await TelemetryService.SendPending(client, recorder); var f=recorder.Flight ?? throw new InvalidOperationException("Aucun vol."); await client.Send($"pireps/{Uri.EscapeDataString(f.PirepId)}/file", new { distance=Math.Round(f.Distance,2), flight_time=Math.Max(1,(int)Math.Round(f.AirborneSeconds/60)), fuel_used=Math.Round(f.FuelUsed), block_time=Math.Max(1,(int)Math.Round(((f.BlockOn ?? DateTimeOffset.UtcNow)-f.BlockOff!.Value).TotalMinutes)), block_off_time=f.BlockOff, block_on_time=f.BlockOn, created_at=f.BlockOn, landing_rate=f.LandingRate }); recorder.Complete(); Show("PIREP déposé."); });
    private async Task RefreshAsync() { if (ticking) return; ticking = true; try { await telemetry.Tick(); var f=recorder.Flight; status.Text = $"{sim.Status}   |   {(client.Connected ? client.Server : "phpVMS déconnecté")}   |   Phase : {f?.Phase ?? "-"}   |   Tampon : {recorder.Pending.Count + recorder.PendingEvents.Count}"; } finally { ticking = false; } }
}
