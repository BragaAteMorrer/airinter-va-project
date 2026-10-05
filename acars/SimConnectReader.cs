using System.Runtime.InteropServices;
using System.IO;
using System.Text;

namespace Promethee;
public record Sample(Guid SampleId, DateTimeOffset RecordedAt, double Lat, double Lon, double Altitude, double Agl, double Ias, double Gs, double Vs, double Heading, double Fuel, bool OnGround, double Bank, bool GearDown, double TouchdownVelocity, double Flaps, bool ThrustStable, double LocalizerDots, double GlideslopeDots, bool ParkingBrake,
    bool BeaconLight = false, bool LandingLight = false, bool Engine1Running = false, bool Engine2Running = false,
    bool Engine3Running = false, bool Engine4Running = false, bool SlewActive = false, double SimulationRate = 1, double Pitch = 0,
    double GForce = 1, bool OverspeedWarning = false, bool StallWarning = false,
    double Reverse1Percent = 0, double Reverse2Percent = 0, double Reverse3Percent = 0, double Reverse4Percent = 0);

/// <summary>
/// Current production connector for the Microsoft Flight Simulator SimConnect
/// family. It intentionally reports the generic family until an SDK-supported
/// version probe is implemented; it must not guess 2020 versus 2024.
/// </summary>
public sealed class SimConnectReader : ISimulatorConnector, IHermesEfbTransport
{
    private const uint MainDefinitionId = 1;
    private const uint MainRequestId = 1;
    private const uint TitleDefinitionId = 2;
    private const uint TitleRequestId = 2;
    private const uint ModelDefinitionId = 3;
    private const uint ModelRequestId = 3;
    private const uint TypeDefinitionId = 4;
    private const uint TypeRequestId = 4;
    private const uint SimConnectDataTypeString128 = 8;
    private const uint SimConnectDataTypeString256 = 9;
    private const uint SimConnectPeriodSecond = 4;
    private const uint PauseEventId = 1001;
    private const uint SimStopEventId = 1002;
    private const uint SimStartEventId = 1003;
    private const int RecvIdEvent = 4;
    private const int RecvIdSimObjectData = 8;
    // MSFS 2024 SDK SIMCONNECT_RECV_ID enum: COMM_BUS is entry 44.
    // Keep this value aligned with the native SDK enum: using CAMERA_DEFINITION_LIST (43)
    // silently drops EFB CommBus requests before they reach HermesEfbBridge.
    private const int RecvIdCommBus = 44;
    private const uint EfbRequestEventId = 2001;
    private const uint CommBusBroadcastJs = 1 << 0;

    private IntPtr handle; private readonly Dispatch callback;
    private string? aircraftTitle;
    private string? aircraftModel;
    private string? aircraftType;
    private double? previousThrottle1;
    private double? previousThrottle2;
    private uint pauseFlags;
    private bool simStopped;
    private bool simVarPaused;
    private readonly Dictionary<uint, StringBuilder> commBusBuffers = [];
    public Sample? Latest { get; private set; }
    public string Status { get; private set; } = "Simulateur non détecté";
    public event Action<Sample>? Received;
    public event Action<AircraftSnapshot>? SnapshotReceived;
    public event Action<string>? CommBusMessageReceived;
    public bool CommBusAvailable { get; private set; }
    public DateTimeOffset? LastCommBusRequestAt { get; private set; }
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
        ("LIGHT BEACON","bool"),("LIGHT LANDING","bool"),("GENERAL ENG COMBUSTION:1","bool"),("GENERAL ENG COMBUSTION:2","bool"),("GENERAL ENG COMBUSTION:3","bool"),("GENERAL ENG COMBUSTION:4","bool"),("IS SLEW ACTIVE","bool"),("SIMULATION RATE","number"),("PLANE PITCH DEGREES","degrees"),
        ("TOTAL WEIGHT","pounds"),("CABIN SEATBELTS ALERT SWITCH","bool"),("EXIT OPEN:0","percent"),("EXIT OPEN:1","percent"),("EXIT OPEN:2","percent"),("EXIT OPEN:3","percent"),("TRANSPONDER CODE:1","number"),("AUTOPILOT MASTER","bool"),("IS PAUSED","bool"),("GENERAL ENG THROTTLE LEVER POSITION:1","percent"),("GENERAL ENG THROTTLE LEVER POSITION:2","percent"),
        ("G FORCE","GForce"),("OVERSPEED WARNING","bool"),("STALL WARNING","bool"),
        ("TURB ENG REVERSE NOZZLE PERCENT:1","percent"),("TURB ENG REVERSE NOZZLE PERCENT:2","percent"),
        ("TURB ENG REVERSE NOZZLE PERCENT:3","percent"),("TURB ENG REVERSE NOZZLE PERCENT:4","percent"),
        ("LIGHT NAV","bool"),("LIGHT STROBE","bool"),("LIGHT TAXI","bool"),("LIGHT LOGO","bool"),("LIGHT WING","bool"),
        ("SPOILERS ARMED","bool"),("APU PCT RPM","Percent Over 100"),("ELECTRICAL MASTER BATTERY:0","bool"),("EXTERNAL POWER ON:1","bool"),
        ("SEA LEVEL PRESSURE","millibars"),("AMBIENT TEMPERATURE","celsius"),("AMBIENT WIND VELOCITY","knots"),("AMBIENT WIND DIRECTION","degrees")];
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
                Marshal.ThrowExceptionForHR(SimConnect_AddToDataDefinition(handle,TypeDefinitionId,"ATC TYPE","NULL",SimConnectDataTypeString128,0,uint.MaxValue));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,MainRequestId,MainDefinitionId,0,SimConnectPeriodSecond,0,0,0,0));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,TitleRequestId,TitleDefinitionId,0,SimConnectPeriodSecond,0,0,0,0));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,ModelRequestId,ModelDefinitionId,0,SimConnectPeriodSecond,0,0,0,0));
                Marshal.ThrowExceptionForHR(SimConnect_RequestDataOnSimObject(handle,TypeRequestId,TypeDefinitionId,0,SimConnectPeriodSecond,0,0,0,0));
                TrySubscribeSystemEvent(PauseEventId, "Pause_EX1");
                TrySubscribeSystemEvent(SimStopEventId, "SimStop");
                TrySubscribeSystemEvent(SimStartEventId, "SimStart");
                TryEnableCommBus();
                Status="MSFS détecté";
            }
            Marshal.ThrowExceptionForHR(SimConnect_CallDispatch(handle,callback,IntPtr.Zero));
            if (LatestSnapshot?.Paused == true)
                LatestSnapshot = LatestSnapshot with { SampleId = Guid.NewGuid(), RecordedAt = DateTimeOffset.UtcNow };
        } catch(DllNotFoundException ex){System.Diagnostics.Trace.WriteLine(ex);Status="Simulateur non détecté";Close();}
          catch(BadImageFormatException ex){System.Diagnostics.Trace.WriteLine(ex);Status="Simulateur non détecté";Close();}
          catch(Exception ex) when(ex is COMException or EntryPointNotFoundException){System.Diagnostics.Trace.WriteLine(ex);Status="Connexion au simulateur interrompue";Close();}
        if(LatestSnapshot is not null && LatestSnapshot.Paused != true && DateTimeOffset.UtcNow-LatestSnapshot.RecordedAt>TimeSpan.FromSeconds(15)) Status="Simulateur non détecté";
    }
    private void Receive(IntPtr data,uint length,IntPtr context)
    {
        var id=Marshal.ReadInt32(data,8); if(id==3){Status="Simulateur non détecté";Close();return;} if(id==1){Status="Connexion au simulateur interrompue";return;}
        if(id==RecvIdEvent){ReceiveSystemEvent(data,length);return;}
        if(id==RecvIdCommBus){ReceiveCommBus(data,length);return;}
        if(id!=RecvIdSimObjectData)return;
        var requestId=Marshal.ReadInt32(data,12);
        if(requestId==TitleRequestId){
            aircraftTitle=ReadFixedString(data,length,256);
            return;
        }
        if(requestId==ModelRequestId){
            aircraftModel=ReadFixedString(data,length,128);
            return;
        }
        if(requestId==TypeRequestId){
            aircraftType=ReadFixedString(data,length,128);
            return;
        }
        if(requestId!=MainRequestId||length<40+definitions.Length*8)return;
        var v=new double[definitions.Length];Marshal.Copy(IntPtr.Add(data,40),v,0,v.Length);if(v.Any(x=>!double.IsFinite(x))||Math.Abs(v[0])>90||Math.Abs(v[1])>180)return;
        bool? thrustStable = previousThrottle1.HasValue && previousThrottle2.HasValue
            ? Math.Abs(v[36] - previousThrottle1.Value) <= 1.5
              && Math.Abs(v[37] - previousThrottle2.Value) <= 1.5
            : null;
        previousThrottle1 = v[36];
        previousThrottle2 = v[37];

        Latest=new Sample(Guid.NewGuid(),DateTimeOffset.UtcNow,v[0],v[1],v[2],v[3],v[4],v[5],v[6],v[7],v[8],v[9]!=0,v[10],v[11]>=99,v[12],v[13],false,v[15],v[16],v[17]!=0,
            v[18]!=0,v[19]!=0,v[20]!=0,v[21]!=0,v[22]!=0,v[23]!=0,v[24]!=0,v[25],v[26],
            v[38],v[39]!=0,v[40]!=0,v[41],v[42],v[43],v[44]);
        LatestSnapshot=Latest.ToSnapshot() with {
            GrossWeight=v[27] > 0 ? v[27] : null,
            SeatBeltSign=v[28] != 0,
            DoorsOpen=v[29] > 0.5 || v[30] > 0.5 || v[31] > 0.5 || v[32] > 0.5,
            TransponderCode=NormalizeTransponder(v[33]),
            AutopilotEnabled=v[34] != 0,
            Paused=ResolvePaused(simVarPaused = v[35] != 0),
            PauseKind=ResolvePauseKind(simVarPaused),
            ThrustStable=thrustStable,
            NavigationLight=v[45] != 0,
            StrobeLight=v[46] != 0,
            TaxiLight=v[47] != 0,
            LogoLight=v[48] != 0,
            WingLight=v[49] != 0,
            SpoilersArmed=v[50] != 0,
            ApuRpmPercent=v[51] >= 0 ? v[51] : null,
            ApuRunning=v[51] >= 5,
            BatteryOn=v[52] != 0,
            ExternalPowerOn=v[53] != 0,
            QnhHpa=v[54] > 0 ? v[54] : null,
            OutsideAirTemperatureCelsius=v[55],
            WindSpeedKnots=v[56] >= 0 ? v[56] : null,
            WindDirectionDegrees=v[57],
            AutothrottleArmed=v[14] != 0,
            AircraftTitle=aircraftTitle,
            AircraftIcao=LooksLikeIcao(aircraftModel) ? aircraftModel : null,
            AircraftModel=aircraftType ?? aircraftModel
        };
        Status=LatestSnapshot.Paused == true
            ? "Connecté à MSFS — simulation en pause"
            : "Connecté à MSFS";
        Received?.Invoke(Latest); SnapshotReceived?.Invoke(LatestSnapshot);
    }

    private void TryEnableCommBus()
    {
        try {
            var result = SimConnect_SubscribeToCommBusEvent(handle, EfbRequestEventId, HermesEfbBridge.RequestEvent);
            if (result < 0) Marshal.ThrowExceptionForHR(result);
            CommBusAvailable = true;
        } catch (EntryPointNotFoundException) {
            // MSFS 2020 SimConnect does not expose the MSFS 2024 CommBus API.
            CommBusAvailable = false;
        } catch (Exception exception) {
            System.Diagnostics.Trace.WriteLine($"MSFS 2024 EFB CommBus unavailable: {exception}");
            CommBusAvailable = false;
        }
    }

    private void ReceiveCommBus(IntPtr data, uint length)
    {
        // SIMCONNECT_RECV (12 bytes) + LIST_TEMPLATE (16 bytes) +
        // uEventID (4 bytes), followed by the variable payload.
        if (length <= 32) return;
        var entryNumber = (uint)Marshal.ReadInt32(data, 20);
        var outOf = (uint)Marshal.ReadInt32(data, 24);
        var eventId = (uint)Marshal.ReadInt32(data, 28);
        if (eventId != EfbRequestEventId) return;

        var payloadLength = checked((int)length - 32);
        if (payloadLength <= 0) return;
        var bytes = new byte[payloadLength];
        Marshal.Copy(IntPtr.Add(data, 32), bytes, 0, payloadLength);
        var zero = Array.IndexOf(bytes, (byte)0);
        var payload = Encoding.UTF8.GetString(bytes, 0, zero >= 0 ? zero : bytes.Length);

        if (outOf <= 1) {
            LastCommBusRequestAt = DateTimeOffset.UtcNow;
            CommBusMessageReceived?.Invoke(payload);
            return;
        }

        if (!commBusBuffers.TryGetValue(eventId, out var buffer)) {
            buffer = new StringBuilder();
            commBusBuffers[eventId] = buffer;
        }
        buffer.Append(payload);
        if (entryNumber + 1 < outOf) return;

        commBusBuffers.Remove(eventId);
        LastCommBusRequestAt = DateTimeOffset.UtcNow;
        CommBusMessageReceived?.Invoke(buffer.ToString());
    }

    public bool TrySendCommBus(string eventName, string payload)
    {
        if (!CommBusAvailable || handle == IntPtr.Zero || string.IsNullOrWhiteSpace(eventName)) return false;
        try {
            var bytes = (uint)(Encoding.UTF8.GetByteCount(payload) + 1);
            var result = SimConnect_CallCommBusEvent(handle, eventName, CommBusBroadcastJs, bytes, payload);
            if (result < 0) Marshal.ThrowExceptionForHR(result);
            return true;
        } catch (Exception exception) when (exception is COMException or EntryPointNotFoundException) {
            System.Diagnostics.Trace.WriteLine($"MSFS 2024 EFB response failed: {exception}");
            CommBusAvailable = false;
            return false;
        }
    }

    private void ReceiveSystemEvent(IntPtr data, uint length)
    {
        if (length < 24) return;
        var eventId = (uint)Marshal.ReadInt32(data, 16);
        var eventData = (uint)Marshal.ReadInt32(data, 20);

        if (eventId == PauseEventId) {
            pauseFlags = eventData;
            if (eventData == 0) simVarPaused = false;
            RefreshPauseSnapshot();
            return;
        }
        if (eventId == SimStopEventId) {
            simStopped = true;
            RefreshPauseSnapshot();
            return;
        }
        if (eventId == SimStartEventId) {
            simStopped = false;
            RefreshPauseSnapshot();
        }
    }
    private void RefreshPauseSnapshot()
    {
        if (LatestSnapshot is null) return;
        var now = DateTimeOffset.UtcNow;
        LatestSnapshot = LatestSnapshot with {
            SampleId = Guid.NewGuid(),
            RecordedAt = now,
            Paused = ResolvePaused(simVarPaused),
            PauseKind = ResolvePauseKind(simVarPaused)
        };
        Status = LatestSnapshot.Paused == true
            ? "Connecté à MSFS — simulation en pause"
            : "Connecté à MSFS";
        SnapshotReceived?.Invoke(LatestSnapshot);
    }
    private bool ResolvePaused(bool simVarPaused) => simStopped || pauseFlags != 0 || simVarPaused;
    private string? ResolvePauseKind(bool simVarPaused)
    {
        if (simStopped) return "MENU_OR_DIALOG";
        var kinds = new List<string>();
        if ((pauseFlags & 4) != 0) kinds.Add("ACTIVE_PAUSE");
        if ((pauseFlags & 1) != 0) kinds.Add("FULL_PAUSE");
        if ((pauseFlags & 8) != 0) kinds.Add("SIM_PAUSE");
        if ((pauseFlags & 2) != 0) kinds.Add("FSX_LEGACY_PAUSE");
        if (kinds.Count == 0 && simVarPaused) kinds.Add("PAUSE");
        return kinds.Count == 0 ? null : string.Join("+", kinds);
    }
    private void TrySubscribeSystemEvent(uint eventId, string eventName)
    {
        try {
            var result = SimConnect_SubscribeToSystemEvent(handle, eventId, eventName);
            if (result < 0) Marshal.ThrowExceptionForHR(result);
        } catch (Exception exception) {
            System.Diagnostics.Trace.WriteLine($"SimConnect system event {eventName} unavailable: {exception}");
        }
    }
    private static int? NormalizeTransponder(double value){
        if(!double.IsFinite(value) || value < 0) return null;
        var code=(int)Math.Round(value);
        return code is >=0 and <=7777 ? code : null;
    }
    private static bool LooksLikeIcao(string? value){
        if(string.IsNullOrWhiteSpace(value)) return false;
        var normalized=value.Trim().ToUpperInvariant();
        return normalized.Length is >=2 and <=4 && normalized.All(char.IsLetterOrDigit);
    }
    private static string? ReadFixedString(IntPtr data,uint length,int size){
        if(length<40+size)return null;
        var value=Marshal.PtrToStringAnsi(IntPtr.Add(data,40),size)?.TrimEnd('\0').Trim();
        return string.IsNullOrWhiteSpace(value)?null:value;
    }
    private void Close(){
        if(handle!=IntPtr.Zero){SimConnect_Close(handle);handle=IntPtr.Zero;}
        Latest=null;LatestSnapshot=null;aircraftTitle=null;aircraftModel=null;aircraftType=null;
        previousThrottle1=null;previousThrottle2=null;pauseFlags=0;simStopped=false;simVarPaused=false;
        CommBusAvailable=false;LastCommBusRequestAt=null;commBusBuffers.Clear();
    } public void Dispose()=>Close();
    [UnmanagedFunctionPointer(CallingConvention.StdCall)] private delegate void Dispatch(IntPtr data,uint length,IntPtr context);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_Open(out IntPtr handle,string name,IntPtr window,uint message,IntPtr signal,uint index);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_Close(IntPtr handle);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_AddToDataDefinition(IntPtr handle,uint definition,string name,string unit,uint type,float epsilon,uint datum);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_RequestDataOnSimObject(IntPtr handle,uint request,uint definition,uint objectId,uint period,uint flags,uint origin,uint interval,uint limit);
    [DllImport("SimConnect.dll")] private static extern int SimConnect_CallDispatch(IntPtr handle,Dispatch callback,IntPtr context);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_SubscribeToSystemEvent(IntPtr handle,uint eventId,string systemEventName);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_SubscribeToCommBusEvent(IntPtr handle,uint eventId,string eventName);
    [DllImport("SimConnect.dll",CharSet=CharSet.Ansi)] private static extern int SimConnect_CallCommBusEvent(IntPtr handle,string eventName,uint broadcastTo,uint bufferSize,string data);
}
