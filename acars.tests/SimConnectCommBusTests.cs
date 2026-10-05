using System.Reflection;
using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class SimConnectCommBusTests
{
    [Fact]
    public void CommBus_receive_id_matches_msfs_2024_sdk()
    {
        var field = typeof(SimConnectReader).GetField(
            "RecvIdCommBus",
            BindingFlags.Static | BindingFlags.NonPublic);

        Assert.NotNull(field);
        Assert.Equal(44, (int)field.GetRawConstantValue()!);
    }
}
