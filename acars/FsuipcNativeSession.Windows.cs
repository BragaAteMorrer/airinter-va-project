using FSUIPC;

namespace Promethee;

/// <summary>
/// Windows implementation backed by Paul Henty's FSUIPCClientDLL. Hermès never
/// redistributes FSUIPC itself: the pilot installs the version appropriate to
/// FS2004, FSX or Prepar3D and this client opens its free IPC interface.
/// </summary>
internal sealed class FsuipcNativeSession : IFsuipcSession
{
    private const double FeetPerMeter = 3.28083989501312;
    private const double KnotsPerMeterPerSecond = 1.9438444924406;
    private const double FeetPerMinutePerMeterPerSecond = 196.8503937008;
    private const double TwoPow32 = 4294967296d;
    private const double TwoPow16 = 65536d;

    private readonly Offset<int> groundSpeed = new(0x02B4);
    private readonly Offset<int> indicatedAirspeed = new(0x02BC);
    private readonly Offset<int> verticalSpeed = new(0x02C8);
    private readonly Offset<long> latitude = new(0x0560);
    private readonly Offset<long> longitude = new(0x0568);
    private readonly Offset<long> altitude = new(0x0570);
    private readonly Offset<int> groundAltitude = new(0x0020);
    private readonly Offset<int> pitch = new(0x0578);
    private readonly Offset<int> bank = new(0x057C);
    private readonly Offset<uint> heading = new(0x0580);
    private readonly Offset<ushort> onGround = new(0x0366);
    private readonly Offset<ushort> parkingBrake = new(0x0BC8);
    private readonly Offset<int> flaps = new(0x0BDC);
    private readonly Offset<int> gear = new(0x0BE8);
    private readonly Offset<int> spoilersArmed = new(0x0BCC);
    private readonly Offset<ushort> lights = new(0x0D0C);
    private readonly Offset<uint> fuelWeight = new(0x126C);
    private readonly Offset<ushort> engineCount = new(0x0AEC);
    private readonly Offset<ushort> engine1 = new(0x0894);
    private readonly Offset<ushort> engine2 = new(0x092C);
    private readonly Offset<ushort> engine3 = new(0x09C4);
    private readonly Offset<ushort> engine4 = new(0x0A5C);
    private readonly Offset<short> slew = new(0x05DC);
    private readonly Offset<string> aircraftTitle = new(0x3D00, 256);
    private readonly Offset<double> grossWeight = new(0x30C0);
    private readonly Offset<byte> seatBeltSign = new(0x341D);
    private readonly Offset<ushort> transponderCode = new(0x0354);
    private readonly Offset<int> autopilotMaster = new(0x07BC);
    private readonly Offset<ushort> pauseIndicator = new(0x0264);
    private readonly Offset<ushort> simulationRate = new(0x0C1A);
    private readonly Offset<string> aircraftIcao = new(0x0B26, 32);
    private readonly Offset<string> atcModel = new(0x3500, 23);
    private readonly Offset<string> atcType = new(0x3160, 23);
    private readonly Offset<double> throttle1 = new(0x3AE8);
    private readonly Offset<double> throttle2 = new(0x3A28);

    private double? previousThrottle1;
    private double? previousThrottle2;

    public bool IsOpen { get; private set; }

    public void Open()
    {
        if (IsOpen) return;
        FSUIPCConnection.Open();
        IsOpen = true;
    }

    public FsuipcTelemetryFrame Read()
    {
        if (!IsOpen) throw new InvalidOperationException("La session FSUIPC n'est pas ouverte.");

        FSUIPCConnection.Process();

        var latitudeDegrees = latitude.Value * 90d / (10001750d * TwoPow16 * TwoPow16);
        var longitudeDegrees = longitude.Value * 360d / (TwoPow16 * TwoPow16 * TwoPow16 * TwoPow16);
        var altitudeMeters = altitude.Value / (TwoPow16 * TwoPow16);
        var groundAltitudeMeters = groundAltitude.Value / 256d;
        var altitudeFeet = altitudeMeters * FeetPerMeter;
        var aglFeet = Math.Max(0, (altitudeMeters - groundAltitudeMeters) * FeetPerMeter);
        var iasKnots = Math.Max(0, indicatedAirspeed.Value / 128d);
        var gsKnots = Math.Max(0, groundSpeed.Value / TwoPow16 * KnotsPerMeterPerSecond);
        var vsFeetPerMinute = verticalSpeed.Value / 256d * FeetPerMinutePerMeterPerSecond;

        var count = Math.Clamp((int) engineCount.Value, 1, 4);
        var engineStates = new[] {
            engine1.Value != 0,
            engine2.Value != 0,
            engine3.Value != 0,
            engine4.Value != 0
        }.Take(count).ToArray();

        var lightBits = lights.Value;
        var title = aircraftTitle.Value?.Trim('\0', ' ');
        var icao = FirstNonBlank(aircraftIcao.Value, atcModel.Value);
        var model = FirstNonBlank(atcType.Value, atcModel.Value);
        var simRate = simulationRate.Value > 0 ? simulationRate.Value / 256d : (double?)null;
        var paused = (pauseIndicator.Value & 0x7) != 0;
        var throttleOne = throttle1.Value;
        var throttleTwo = throttle2.Value;
        bool? thrustStable = previousThrottle1.HasValue && previousThrottle2.HasValue
            ? Math.Abs(throttleOne - previousThrottle1.Value) <= 1.5
              && Math.Abs(throttleTwo - previousThrottle2.Value) <= 1.5
            : null;
        previousThrottle1 = throttleOne;
        previousThrottle2 = throttleTwo;

        return new(
            DateTimeOffset.UtcNow,
            latitudeDegrees,
            longitudeDegrees,
            altitudeFeet,
            aglFeet,
            iasKnots,
            gsKnots,
            vsFeetPerMinute,
            Normalize360(heading.Value * 360d / TwoPow32),
            NormalizeSigned(pitch.Value * 360d / TwoPow32),
            NormalizeSigned(bank.Value * 360d / TwoPow32),
            fuelWeight.Value,
            onGround.Value != 0,
            parkingBrake.Value >= 16384,
            gear.Value >= 16000,
            Math.Clamp(flaps.Value * 100d / 16383d, 0, 100),
            engineStates,
            slew.Value != 0,
            (lightBits & (1 << 1)) != 0,
            (lightBits & (1 << 0)) != 0,
            (lightBits & (1 << 4)) != 0,
            (lightBits & (1 << 2)) != 0,
            (lightBits & (1 << 3)) != 0,
            spoilersArmed.Value != 0,
            string.IsNullOrWhiteSpace(title) ? null : title,
            GrossWeightPounds: double.IsFinite(grossWeight.Value) && grossWeight.Value > 0 ? grossWeight.Value : null,
            SeatBeltSign: seatBeltSign.Value != 0,
            DoorsOpen: null,
            TransponderCode: DecodeBcd4(transponderCode.Value),
            AutopilotEnabled: autopilotMaster.Value != 0,
            Paused: paused,
            SimulationRate: simRate,
            ThrustStable: thrustStable,
            AircraftIcao: icao,
            AircraftModel: model);
    }

    public void Close()
    {
        if (!IsOpen) return;
        try { FSUIPCConnection.Close(); }
        finally {
            IsOpen = false;
            previousThrottle1 = null;
            previousThrottle2 = null;
        }
    }

    public void Dispose() => Close();

    private static string? FirstNonBlank(params string?[] values)
    {
        foreach (var value in values) {
            var normalized = value?.Trim('\0', ' ');
            if (!string.IsNullOrWhiteSpace(normalized)) return normalized;
        }
        return null;
    }

    private static int DecodeBcd4(ushort value)
    {
        var d1 = (value >> 12) & 0xF;
        var d2 = (value >> 8) & 0xF;
        var d3 = (value >> 4) & 0xF;
        var d4 = value & 0xF;
        if (d1 > 9 || d2 > 9 || d3 > 9 || d4 > 9) return value;
        return d1 * 1000 + d2 * 100 + d3 * 10 + d4;
    }

    private static double Normalize360(double value)
    {
        var result = value % 360d;
        return result < 0 ? result + 360d : result;
    }

    private static double NormalizeSigned(double value)
    {
        var normalized = Normalize360(value);
        return normalized > 180d ? normalized - 360d : normalized;
    }
}
