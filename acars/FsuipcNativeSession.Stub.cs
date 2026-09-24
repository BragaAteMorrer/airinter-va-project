namespace Promethee;

/// <summary>
/// Non-Windows build stub. The production desktop target replaces this class
/// with FsuipcNativeSession.Windows.cs; tests inject IFsuipcSession fakes.
/// </summary>
internal sealed class FsuipcNativeSession : IFsuipcSession
{
    public bool IsOpen => false;

    public void Open() =>
        throw new PlatformNotSupportedException("FSUIPC est disponible uniquement dans la version Windows d'Hermès.");

    public FsuipcTelemetryFrame Read() =>
        throw new PlatformNotSupportedException("FSUIPC est disponible uniquement dans la version Windows d'Hermès.");

    public void Close() { }
    public void Dispose() { }
}
