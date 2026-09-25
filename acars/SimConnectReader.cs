using System.Runtime.InteropServices;
using System.IO;

namespace Promethee;
public record Sample(Guid SampleId, DateTimeOffset RecordedAt, double Lat, double Lon, double Altitude, double Agl, double Ias, double Gs, double Vs, double Heading, double Fuel, bool OnGround, double Bank, bool GearDown, double TouchdownVelocity, double Flaps, bool ThrustStable, double LocalizerDots, double GlideslopeDots, bool ParkingBrake,
    bool BeaconLight = false, bool LandingLight = false, bool Engine1Running = false, bool Engine2Running = false,
    bool Engine3Running = false, bool Engine4Running = false, bool SlewActive = false, double SimulationRate = 1, double Pitch = 0);

/// <summary>
/// Current production connector for the Microsoft Flight Simulator SimConnect
/// family. It intentionally reports the generic family until an SDK-supported
/// version probe is implemented; it must not guess 2020 versus 2024.
/// </summary>
public sealed class SimConnectReader : ISimulatorConnector
{
    private const uint MainDefinitionId = 1;
    private const uint MainRequestId = 1;
    private const uint TitleDefinitionId = 2;
    private const uint TitleRequestId = 2;
    private const uint ModelDefinitionId = 3;
    private const uint ModelRequestId = 3;
    private const uint SimConnectDataTypeString128 = 8;
    private const uint SimConnectDataTypeString256 = 9;
    private const uint SimConnectPeriodSecond = 4;

    private IntPtr handle; private readonly Dispatch callback;
    private string? aircraftTitle;
    private string? aircraftModel;
    public Sample? Latest { get; private set; }
    public string Status { get; private set; } = "Simulateur non détecté";
    public event Action<Sample>? Received;
    public event Action<AircraftSnapshot>? SnapshotReceived;
    public AircraftSnapshot? LatestSnapshot { get; private set; }
    public SimulatorConnectionState ConnectionState => handle != IntPtr.Zero
        ? (Latest is null ? SimulatorConnectionState.Detected : SimulatorConnectionState.Connected)
        : SimulatorConnectionState.NotDetected;
    public SimulatorDescriptor Descriptor { get; } = new(
        SimulatorKind.MicrosoftFlightSimulator, "Microsoft Flight Simulator (SimConnect)", "simconnect",
        SimulatorCapabilities.Position | SimulatorCapabilities.FlightDynamics | SimulatorCapabilities.Fuel |
        SimulatorCapabilities.AircraftSystems | SimulatorCapabilities.Engines | SimulatorCapabilities.Lights |
        SimulatorCapabilities.SimulatorControls);
    private readonly (string Name,string Unit)[] definitions = [
        ("PLANE LATITUDE","degrees"),("PLANE LONGITUDE","degrees"),("PLANE ALTITUDE","feet"),("PLANE ALT ABOVE GROUND","feet"),("AIRSPEED INDICATED","knots"),("GROUND VELOCITY","knots"),("VERTICAL SPEED","feet per minute"),("PLANE HEADING DEGREES TRUE","degrees"),("FUEL TOTAL QUANTITY WEIGHT","pounds"),("SIM ON GROUND","bool"),("PLANE BANK DEGREES","degrees"),("GEAR TOTAL PCT EXTENDED","percent"),("PLANE TOUCHDOWN NORMAL VELOCITY","feet per second"),("FLAPS HANDLE PERCENT","percent"),("AUTOPILOT THROTTLE ARM","bool"),("NAV CDI:1","number"),("NAV GSI:1","number"),("BRAKE PARKING POSITION","bool"),
        ("LIGHT BEACON","bool"),("LIGHT LANDING","bool"),("GENERAL ENG COMBUSTION:1","bool"),("GENERAL ENG COMBUSTION:2","bool"),("GENERAL ENG COMBUSTION:3","bool"),("GENERAL ENG COMBUSTION:4","bool"),("IS SLEW ACTIVE","bool"),("SIMULATION RATE","number"),("PLANE PITCH DEGREES","degrees")];
    public SimConnectReader()
    {
        callback=Receive; var dll=Environment.GetEnvironmentVariable("PROMETHEE_SIMCONNECT_DLL");
        if (!string.IsNullOrWhiteSpace(dll) && System.IO.File.Exists(dll)) NativeLibrary.SetDllImportResolver(typeof(SimConnectReader).Assembly, (name,assembly,path) => name=="SimConnect.dll" ? NativeLibrary.Load(Path.GetFullPath(dll)) : IntPtr.Zero);
    }
    public void Poll()
    {
        try {
            if (handle==IntPtr.Zero) {
                var detected = SimulatorDetector.DetectRunning().FirstOrDefault(x => x.Kind == SimulatorKind.MicrosoftFlightSimulator);
                if (detected is not null) Status = detected.DisplayName + " détecté — connexion SimConnect…";
                var hr=SimConnect_Open(out handle,"Promethee Air Inter",IntPtr.Zero,0,IntPtr.Zero,0);
                if(hr<0){handle=IntPtr.Zero;Status=detected is null ? "Simulateur non détecté" : detected.DisplayName + " détecté — SimConnect indisponible";return;}
                foreach(var d in definitions)
                    Marshal.ThrowExceptionForHR(SimConnect_AddToDataDefinition(handle,MainDefinitionId,d.Name,d.Unit,4,0,uint.MaxValue));
                Marshal.ThrowExceptionForHR(SimConnect_AddToDataDefinition(handle,TitleDefinitionId,"TITLE","NULL",SimConnectDataTypeString256,0,uint.MaxValue));
                Marshal.ThrowExceptionForHR(SimConnect_AddToDataDefinition(handle,ModelDefinitionId,"ATC MODEL","NULL",SimConnectDataTypeString128,0,uint.MaxValue));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,MainRequestId,MainDefinitionId,0,SimConnectPeriodSecond,0,0,0,0));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,TitleRequestId,TitleDefinitionId,0,SimConnectPeriodSecond,0,0,0,0));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,ModelRequestId,ModelDefinitionId,0,SimConnectPeriodSecond,0,0,0,0));
                Status="MSFS détecté";
            }
            Marshal.ThrowExceptionForHR(SimConnect_CallDispatch(handle,callback,IntPtr.Zero));
        } catch(DllNotFoundException ex){System.Diagnostics.Trace.WriteLine(ex);Status="Simulateur non détecté";Close();}
          catch(BadImageFormatException ex){System.Diagnostics.Trace.WriteLine(ex);Status="Simulateur non détecté";Close();}
          catch(Exception ex) when(ex is COMException or EntryPointNotFoundException){System.Diagnostics.Trace.WriteLine(ex);Status="Connexion au simulateur interrompue";Close();}
        if(Latest is not null && DateTimeOffset.UtcNow-Latest.RecordedAt>TimeSpan.FromSeconds(15)) Status="Simulateur non détecté";
    }
    private void Receive(IntPtr data,uint length,IntPtr context)
    {
        var id=Marshal.ReadInt32(data,8); if(id==3){Status="Simulateur non détecté";Close();return;} if(id==1){Status="Connexion au simulateur interrompue";return;}
        if(id!=8)return;
        var requestId=Marshal.ReadInt32(data,12);
        if(requestId==TitleRequestId){
            aircraftTitle=ReadFixedString(data,length,256);
            return;
        }
        if(requestId==ModelRequestId){
            aircraftModel=ReadFixedString(data,length,128);
            return;
        }
        if(requestId!=MainRequestId||length<40+definitions.Length*8)return;
        var v=new double[definitions.Length];Marshal.Copy(IntPtr.Add(data,40),v,0,v.Length);if(v.Any(x=>!double.IsFinite(x))||Math.Abs(v[0])>90||Math.Abs(v[1])>180)return;
        Latest=new Sample(Guid.NewGuid(),DateTimeOffset.UtcNow,v[0],v[1],v[2],v[3],v[4],v[5],v[6],v[7],v[8],v[9]!=0,v[10],v[11]>=99,v[12],v[13],v[14]!=0,v[15],v[16],v[17]!=0,
            v[18]!=0,v[19]!=0,v[20]!=0,v[21]!=0,v[22]!=0,v[23]!=0,v[24]!=0,v[25],v[26]);
        LatestSnapshot=Latest.ToSnapshot() with { AircraftTitle=aircraftTitle, AircraftModel=aircraftModel };
        Status="Connecté à MSFS";Received?.Invoke(Latest); SnapshotReceived?.Invoke(LatestSnapshot);
    }
    private static string? ReadFixedString(IntPtr data,uint length,int size){
        if(length<40+size)return null;
        var value=Marshal.PtrToStringAnsi(IntPtr.Add(data,40),size)?.TrimEnd('\0').Trim();
        return string.IsNullOrWhiteSpace(value)?null:value;
    }
    private void Close(){
        if(handle!=IntPtr.Zero){SimConnect_Close(handle);handle=IntPtr.Zero;}
        Latest=null;LatestSnapshot=null;aircraftTitle=null;aircraftModel=null;
    } public void Dispose()=>Close();
    [UnmanagedFunctionPointer(CallingConvention.StdCall)] private delegate void Dispatch(IntPtr data,uint length,IntPtr context);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_Open(out IntPtr handle,string name,IntPtr window,uint message,IntPtr signal,uint index);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_Close(IntPtr handle);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_AddToDataDefinition(IntPtr handle,uint definition,string name,string unit,uint type,float epsilon,uint datum);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_RequestDataOnSimObject(IntPtr handle,uint request,uint definition,uint objectId,uint period,uint flags,uint origin,uint interval,uint limit);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_CallDispatch(IntPtr handle,Dispatch callback,IntPtr context);
}
