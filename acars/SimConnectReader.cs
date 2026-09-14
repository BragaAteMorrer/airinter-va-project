using System.Runtime.InteropServices;
using System.IO;

namespace Promethee;
public record Sample(Guid SampleId, DateTimeOffset RecordedAt, double Lat, double Lon, double Altitude,
    double Agl, double Ias, double Gs, double Vs, double Heading, double Fuel, bool OnGround, double Bank, bool GearDown, double TouchdownVelocity,
    double Flaps, bool ThrustStable, double LocalizerDots, double GlideslopeDots, bool ParkingBrake);

public sealed class SimConnectReader : IDisposable
{
    private IntPtr handle;
    private readonly Dispatch callback;
    public Sample? Latest { get; private set; }
    public string Status { get; private set; } = "Simulateur déconnecté";
    public event Action<Sample>? Received;
    private readonly (string Name,string Unit)[] definitions = [
        ("PLANE LATITUDE","degrees"),("PLANE LONGITUDE","degrees"),("PLANE ALTITUDE","feet"),
        ("PLANE ALT ABOVE GROUND","feet"),("AIRSPEED INDICATED","knots"),("GROUND VELOCITY","knots"),
        ("VERTICAL SPEED","feet per minute"),("PLANE HEADING DEGREES TRUE","degrees"),
        ("FUEL TOTAL QUANTITY WEIGHT","pounds"),("SIM ON GROUND","bool"),("PLANE BANK DEGREES","degrees"),
        ("GEAR TOTAL PCT EXTENDED","percent"),("PLANE TOUCHDOWN NORMAL VELOCITY","feet per second"),("FLAPS HANDLE PERCENT","percent"),("AUTOPILOT THROTTLE ARM","bool"),("NAV CDI:1","number"),("NAV GSI:1","number"),("BRAKE PARKING POSITION","bool")];
    public SimConnectReader()
    {
        callback = Receive;
        // Use an explicitly supplied SDK redistributable, or SimConnect.dll beside the executable.
        var dll = Environment.GetEnvironmentVariable("PROMETHEE_SIMCONNECT_DLL");
        if (!string.IsNullOrWhiteSpace(dll) && File.Exists(dll)) {
            NativeLibrary.SetDllImportResolver(typeof(SimConnectReader).Assembly,
                (name,assembly,path) => name=="SimConnect.dll" ? NativeLibrary.Load(Path.GetFullPath(dll)) : IntPtr.Zero);
        }
    }
    public void Poll()
    {
        try {
            if (handle == IntPtr.Zero) {
                var hr=SimConnect_Open(out handle,"Promethee Air Inter",IntPtr.Zero,0,IntPtr.Zero,0);
                if (hr < 0) { handle=IntPtr.Zero; Status="MSFS non connecté"; return; }
                foreach (var d in definitions)
                    Marshal.ThrowExceptionForHR(SimConnect_AddToDataDefinition(handle,1,d.Name,d.Unit,4,0,uint.MaxValue));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,1,1,0,4,0,0,0,0));
                Status="Connexion SimConnect établie";
            }
            Marshal.ThrowExceptionForHR(SimConnect_CallDispatch(handle,callback,IntPtr.Zero));
        } catch (DllNotFoundException) { Status="SimConnect.dll absent — configurer le chemin du SDK MSFS"; Close(); }
          catch (BadImageFormatException) { Status="SimConnect.dll doit être la version 64 bits"; Close(); }
          catch (Exception e) when(e is COMException or EntryPointNotFoundException) { Status="Connexion SimConnect interrompue"; Close(); }
        if (Latest is not null && DateTimeOffset.UtcNow-Latest.RecordedAt>TimeSpan.FromSeconds(15)) {
            Status="Télémétrie interrompue — en attente du simulateur";
        }
    }
    private void Receive(IntPtr data,uint length,IntPtr context)
    {
        var id=Marshal.ReadInt32(data,8);
        if (id==3) { Status="Simulateur fermé"; Close(); return; }
        if (id==1) { Status="SimConnect signale une erreur de définition"; return; }
        if (id!=8 || length<40+definitions.Length*8 || Marshal.ReadInt32(data,12)!=1) return;
        var v=new double[definitions.Length]; Marshal.Copy(IntPtr.Add(data,40),v,0,v.Length);
        if (v.Any(x=>!double.IsFinite(x)) || Math.Abs(v[0])>90 || Math.Abs(v[1])>180) return;
        Latest=new Sample(Guid.NewGuid(),DateTimeOffset.UtcNow,v[0],v[1],v[2],v[3],v[4],v[5],v[6],v[7],v[8],v[9]!=0,v[10],v[11]>=99,v[12],v[13],v[14]!=0,v[15],v[16],v[17]!=0);
        Status="Simulateur connecté";
        Received?.Invoke(Latest);
    }
    private void Close() { if(handle!=IntPtr.Zero){ SimConnect_Close(handle);handle=IntPtr.Zero; } }
    public void Dispose()=>Close();
    [UnmanagedFunctionPointer(CallingConvention.StdCall)] private delegate void Dispatch(IntPtr data,uint length,IntPtr context);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_Open(out IntPtr handle,string name,IntPtr window,uint message,IntPtr signal,uint index);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_Close(IntPtr handle);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_AddToDataDefinition(IntPtr handle,uint definition,string name,string unit,uint type,float epsilon,uint datum);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_RequestDataOnSimObject(IntPtr handle,uint request,uint definition,uint objectId,uint period,uint flags,uint origin,uint interval,uint limit);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_CallDispatch(IntPtr handle,Dispatch callback,IntPtr context);
}
