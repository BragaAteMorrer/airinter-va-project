using System.Text.Json;
using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class RemoteAcarsConfigurationTests
{
    [Fact]
    public void Valid_declarative_configuration_is_accepted()
    {
        using var document = JsonDocument.Parse("""
            {
              "live":{"position_interval_seconds":20},
              "tracking":{"allow_simulation_rate":false},
              "pirep":{"auto_send_allowed":true,"confirmation_required":true},
              "client":{"minimum_version":"2.1.0","latest_version":"2.2.0",
                        "release_notes_url":"https://promethee.airinter-va.org/notes",
                        "download_url":"https://promethee.airinter-va.org/download"}
            }
            """);

        var config = RemoteAcarsConfiguration.Parse(document.RootElement);

        Assert.Equal(20, config.PositionIntervalSeconds);
        Assert.True(config.AutoSendAllowed);
        Assert.Equal("2.2.0", config.LatestVersion);
        Assert.Equal("https", config.DownloadUri?.Scheme);
    }

    [Fact]
    public void Unsafe_interval_is_rejected()
    {
        using var document = JsonDocument.Parse("""
            {
              "live":{"position_interval_seconds":1},
              "tracking":{"allow_simulation_rate":false},
              "pirep":{"auto_send_allowed":false,"confirmation_required":true},
              "client":{}
            }
            """);

        Assert.Throws<InvalidOperationException>(() => RemoteAcarsConfiguration.Parse(document.RootElement));
    }
}
