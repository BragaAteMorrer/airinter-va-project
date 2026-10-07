namespace Promethee;

public sealed record FdmObservation(
    string Code,
    string Category,
    DateTimeOffset OccurredAt,
    string Message,
    string Severity = "info",
    double? Value = null,
    string? Unit = null,
    string? Phase = null,
    string? Status = null);

public sealed record FlightProfilePoint(
    DateTimeOffset RecordedAt,
    double Altitude,
    double Fuel,
    double GroundSpeed);

public sealed record FlightReview(
    string PirepId,
    string Phase,
    bool ReadyToFile,
    double Distance,
    int AirborneMinutes,
    int BlockMinutes,
    double FuelUsed,
    double? LandingRate,
    string? Approach1000Status,
    string? Approach500Status,
    int BounceCount,
    int GoAroundCount,
    double? MaxBankDegrees,
    double FuelAdded,
    double? MaxSimulationRate,
    int PauseCount,
    int PausedSeconds,
    IReadOnlyList<FlightIssue> Issues,
    IReadOnlyList<FdmObservation> Observations,
    IReadOnlyList<PhaseEntry> Timeline,
    IReadOnlyList<FlightProfilePoint> Profile);

/// <summary>
/// Simulator-neutral Flight Data Monitoring. It records observations only:
/// scoring and company policy remain outside this class.
/// </summary>
public sealed class FlightDataMonitor
{
    private const double BankEnterDegrees = 35;
    private const double BankExitDegrees = 30;
    private const double StabilizedApproachGateFeet = 1000;
    private const double StabilizedApproachMaxDescentRate = -1000;
    private static readonly TimeSpan StabilizedApproachViolationDuration = TimeSpan.FromSeconds(4);
    private static readonly TimeSpan StabilizedApproachMaxSampleGap = TimeSpan.FromSeconds(2.5);
    private const double LoadFactorPositiveLimit = 2.5;
    private const double LoadFactorNegativeLimit = -1.0;
    private const double LoadFactorMaintenancePositiveLimit = 2.9;
    private const double LoadFactorMaintenanceNegativeLimit = -1.2;
    private const double SpeedUnder10kLimitKnots = 255;
    private const double SpeedUnder10kAltitudeFeet = 10000;
    private static readonly TimeSpan SpeedUnder10kViolationDuration = TimeSpan.FromSeconds(10);
    private static readonly TimeSpan SpeedUnder10kMaxSampleGap = TimeSpan.FromSeconds(2.5);

    private AircraftSnapshot? previous;
    private bool approach1000Recorded;
    private bool approach500Recorded;
    private bool bankExcursion;
    private double bankPeak;
    private DateTimeOffset bankStartedAt;
    private bool taxiSegment;
    private double taxiPeak;
    private DateTimeOffset taxiPeakAt;
    private string? taxiPhase;
    private bool pauseSegment;
    private DateTimeOffset pauseStartedAt;
    private string? pauseKind;
    private string? pausePhase;
    private bool approachDescentSegment;
    private bool approachDescentReported;
    private DateTimeOffset approachDescentStartedAt;
    private double approachDescentPeakRate;
    private string? approachDescentPhase;
    private bool loadFactorExceededReported;
    private bool loadFactorMaintenanceReported;
    private bool speedUnder10kSegment;
    private bool speedUnder10kReported;
    private DateTimeOffset speedUnder10kStartedAt;
    private double speedUnder10kPeak;
    private string? speedUnder10kPhase;

    public void Reset()
    {
        previous = null;
        approach1000Recorded = false;
        approach500Recorded = false;
        bankExcursion = false;
        bankPeak = 0;
        bankStartedAt = default;
        taxiSegment = false;
        taxiPeak = 0;
        taxiPeakAt = default;
        taxiPhase = null;
        pauseSegment = false;
        pauseStartedAt = default;
        pauseKind = null;
        pausePhase = null;
        approachDescentSegment = false;
        approachDescentReported = false;
        approachDescentStartedAt = default;
        approachDescentPeakRate = 0;
        approachDescentPhase = null;
        loadFactorExceededReported = false;
        loadFactorMaintenanceReported = false;
        speedUnder10kSegment = false;
        speedUnder10kReported = false;
        speedUnder10kStartedAt = default;
        speedUnder10kPeak = 0;
        speedUnder10kPhase = null;
    }

    public void Restore(IEnumerable<FdmObservation>? existing)
    {
        Reset();
        if (existing is null) return;
        var codes = existing.Select(x => x.Code).ToHashSet(StringComparer.Ordinal);
        approach1000Recorded = codes.Any(x => x.StartsWith("APPROACH_1000_", StringComparison.Ordinal));
        approach500Recorded = codes.Any(x => x.StartsWith("APPROACH_500_", StringComparison.Ordinal));
        loadFactorMaintenanceReported = codes.Contains("LOAD_FACTOR_MAINTENANCE");
        loadFactorExceededReported = loadFactorMaintenanceReported || codes.Contains("LOAD_FACTOR_EXCEEDED");
    }

    public IReadOnlyList<FdmObservation> Process(
        AircraftSnapshot current,
        FlightPhase phase,
        IReadOnlyList<FlightEvent> flightEvents)
    {
        var result = new List<FdmObservation>();

        RecordPause(current, phase, result);
        RecordTaxiSpeed(current, phase, result);
        RecordApproachGate(current, phase, 1000, 1200, ref approach1000Recorded, result);
        RecordApproachGate(current, phase, 500, 1000, ref approach500Recorded, result);
        RecordStabilizedApproachDescentRate(current, phase, result);
        RecordLoadFactor(current, phase, result);
        RecordSpeedUnder10k(current, phase, result);
        RecordBankExcursion(current, phase, result);
        RecordFlightEvents(flightEvents, phase, result);

        previous = current;
        return result;
    }

    public IReadOnlyList<FdmObservation> Flush(AircraftSnapshot? current, FlightPhase phase)
    {
        var result = new List<FdmObservation>();
        if (bankExcursion && current is not null)
            CloseBankExcursion(current, phase, result);
        if (taxiSegment && current is not null)
            CloseTaxiSegment(current, result);
        if (pauseSegment && current is not null)
            ClosePause(current, result);
        if (approachDescentSegment && current is not null)
            CloseStabilizedApproachDescentRate(current, result);
        if (speedUnder10kSegment && current is not null)
            CloseSpeedUnder10k(current, result);
        return result;
    }

    private void RecordPause(AircraftSnapshot current, FlightPhase phase, List<FdmObservation> result)
    {
        if (current.Paused == true)
        {
            if (!pauseSegment)
            {
                pauseSegment = true;
                pauseStartedAt = current.RecordedAt;
                pauseKind = current.PauseKind;
                pausePhase = FlightTrackingEngine.ToExternalPhase(phase);
            }
            else if (!string.IsNullOrWhiteSpace(current.PauseKind))
            {
                pauseKind = current.PauseKind;
            }
            return;
        }

        if (current.Paused == false && pauseSegment)
            ClosePause(current, result);
    }

    private void ClosePause(AircraftSnapshot current, List<FdmObservation> result)
    {
        var duration = Math.Max(0, (current.RecordedAt - pauseStartedAt).TotalSeconds);
        var kind = string.IsNullOrWhiteSpace(pauseKind) ? "PAUSE" : pauseKind;
        var label = kind switch {
            "ACTIVE_PAUSE" => "Active Pause",
            "MENU_OR_DIALOG" => "Menu / dialogue simulateur",
            "FULL_PAUSE" => "Pause complète",
            "SIM_PAUSE" => "Pause simulation",
            _ => kind.Replace('_', ' ')
        };

        result.Add(new(
            "PAUSE",
            "simulator",
            pauseStartedAt,
            $"Pause détectée ({label}) pendant environ {duration:0} s.",
            "attention",
            Math.Round(duration),
            "s",
            pausePhase,
            kind));

        pauseSegment = false;
        pauseStartedAt = default;
        pauseKind = null;
        pausePhase = null;
    }

    private void RecordTaxiSpeed(AircraftSnapshot current, FlightPhase phase, List<FdmObservation> result)
    {
        var taxi = phase is FlightPhase.Pushback or FlightPhase.TaxiOut or FlightPhase.TaxiIn;
        if (taxi && current.OnGround == true && current.GroundSpeedKnots is { } speed)
        {
            if (!taxiSegment)
            {
                taxiSegment = true;
                taxiPeak = speed;
                taxiPeakAt = current.RecordedAt;
                taxiPhase = FlightTrackingEngine.ToExternalPhase(phase);
            }
            else if (speed > taxiPeak)
            {
                taxiPeak = speed;
                taxiPeakAt = current.RecordedAt;
            }
            return;
        }

        if (taxiSegment)
            CloseTaxiSegment(current, result);
    }

    private void CloseTaxiSegment(AircraftSnapshot current, List<FdmObservation> result)
    {
        result.Add(new(
            "TAXI_SPEED_MAX",
            "ground",
            taxiPeakAt,
            $"Vitesse maximale observée pendant le roulage : {taxiPeak:0.0} kt.",
            "info",
            Math.Round(taxiPeak, 1),
            "kt",
            taxiPhase));

        taxiSegment = false;
        taxiPeak = 0;
        taxiPeakAt = default;
        taxiPhase = null;
    }

    private void RecordApproachGate(
        AircraftSnapshot current,
        FlightPhase phase,
        double gateFeet,
        double verticalSpeedLimit,
        ref bool alreadyRecorded,
        List<FdmObservation> result)
    {
        if (alreadyRecorded || previous is null || current.OnGround != false) return;
        if (phase is not (FlightPhase.Approach or FlightPhase.Final)) return;

        var previousAgl = previous.AltitudeAglFeet;
        var currentAgl = current.AltitudeAglFeet;
        if (previousAgl is null || currentAgl is null) return;
        if (!(previousAgl > gateFeet && currentAgl <= gateFeet)) return;
        if ((current.VerticalSpeedFeetPerMinute ?? 0) >= 0) return;

        alreadyRecorded = true;

        var requiredKnown = current.GearDown is not null
            && current.FlapsPercent is not null
            && current.VerticalSpeedFeetPerMinute is not null
            && current.BankDegrees is not null;

        var prefix = $"APPROACH_{gateFeet:0}_";
        if (!requiredKnown) {
            result.Add(new(
                prefix + "UNKNOWN",
                "approach",
                current.RecordedAt,
                $"{gateFeet:0} ft AGL : stabilité non déterminable avec la télémétrie disponible.",
                "info",
                currentAgl,
                "ft AGL",
                FlightTrackingEngine.ToExternalPhase(phase),
                "UNKNOWN"));
            return;
        }

        var failures = new List<string>();
        if (current.GearDown != true) failures.Add("train non sorti");
        if ((current.FlapsPercent ?? 0) <= 0) failures.Add("volets non configurés");
        if (Math.Abs(current.VerticalSpeedFeetPerMinute!.Value) > verticalSpeedLimit)
            failures.Add($"VS {current.VerticalSpeedFeetPerMinute.Value:0} ft/min");
        if (Math.Abs(current.BankDegrees!.Value) > 30)
            failures.Add($"bank {Math.Abs(current.BankDegrees.Value):0}°");

        var stable = failures.Count == 0;
        result.Add(new(
            prefix + (stable ? "STABLE" : "UNSTABLE"),
            "approach",
            current.RecordedAt,
            stable
                ? $"{gateFeet:0} ft AGL : critères génériques disponibles stables (train, volets, VS, bank)."
                : $"{gateFeet:0} ft AGL : {string.Join(", ", failures)}.",
            stable ? "info" : "attention",
            current.VerticalSpeedFeetPerMinute,
            "ft/min",
            FlightTrackingEngine.ToExternalPhase(phase),
            stable ? "STABLE" : "UNSTABLE"));
    }

    private void RecordStabilizedApproachDescentRate(
        AircraftSnapshot current,
        FlightPhase phase,
        List<FdmObservation> result)
    {
        if (approachDescentSegment
            && previous is not null
            && current.RecordedAt - previous.RecordedAt > StabilizedApproachMaxSampleGap)
            ResetStabilizedApproachDescentRate();

        var agl = current.AltitudeAglFeet;
        var verticalSpeed = current.VerticalSpeedFeetPerMinute;
        var inApproachWindow = phase is FlightPhase.Approach or FlightPhase.Final or FlightPhase.Landing
            && current.OnGround == false
            && current.Paused != true
            && agl is > 0 and <= StabilizedApproachGateFeet
            && verticalSpeed is not null;

        var violating = inApproachWindow
            && verticalSpeed!.Value < StabilizedApproachMaxDescentRate;
        if (!violating)
        {
            if (approachDescentSegment)
                CloseStabilizedApproachDescentRate(current, result);
            return;
        }

        if (!approachDescentSegment)
        {
            approachDescentSegment = true;
            approachDescentReported = false;
            approachDescentStartedAt = current.RecordedAt;
            approachDescentPeakRate = verticalSpeed!.Value;
            approachDescentPhase = FlightTrackingEngine.ToExternalPhase(phase);
            return;
        }

        approachDescentPeakRate = Math.Min(approachDescentPeakRate, verticalSpeed!.Value);
        if (!approachDescentReported
            && current.RecordedAt - approachDescentStartedAt >= StabilizedApproachViolationDuration)
        {
            AddStabilizedApproachDescentObservation(result);
            approachDescentReported = true;
        }
    }

    private void CloseStabilizedApproachDescentRate(AircraftSnapshot current, List<FdmObservation> result)
    {
        if (!approachDescentReported
            && current.RecordedAt - approachDescentStartedAt >= StabilizedApproachViolationDuration)
            AddStabilizedApproachDescentObservation(result);

        ResetStabilizedApproachDescentRate();
    }

    private void ResetStabilizedApproachDescentRate()
    {
        approachDescentSegment = false;
        approachDescentReported = false;
        approachDescentStartedAt = default;
        approachDescentPeakRate = 0;
        approachDescentPhase = null;
    }

    private void AddStabilizedApproachDescentObservation(List<FdmObservation> result)
    {
        result.Add(new(
            "APPROACH_DESCENT_RATE_UNSTABLE",
            "approach",
            approachDescentStartedAt,
            $"Approche non stabilisée : VS inférieure à {StabilizedApproachMaxDescentRate:0} ft/min pendant au moins {StabilizedApproachViolationDuration.TotalSeconds:0} s entre {StabilizedApproachGateFeet:0} ft AGL et le toucher (pic {approachDescentPeakRate:0} ft/min).",
            "warning",
            Math.Round(approachDescentPeakRate),
            "ft/min",
            approachDescentPhase,
            "UNSTABLE"));
    }


    private void RecordSpeedUnder10k(
        AircraftSnapshot current,
        FlightPhase phase,
        List<FdmObservation> result)
    {
        if (speedUnder10kSegment
            && previous is not null
            && current.RecordedAt - previous.RecordedAt > SpeedUnder10kMaxSampleGap)
            ResetSpeedUnder10k();

        var altitude = current.AltitudeMslFeet;
        var indicatedAirspeed = current.IndicatedAirspeedKnots;
        var violating = current.OnGround == false
            && current.Paused != true
            && altitude is not null
            && altitude.Value < SpeedUnder10kAltitudeFeet
            && indicatedAirspeed is not null
            && indicatedAirspeed.Value > SpeedUnder10kLimitKnots;

        if (!violating)
        {
            if (speedUnder10kSegment)
                CloseSpeedUnder10k(current, result);
            return;
        }

        if (!speedUnder10kSegment)
        {
            speedUnder10kSegment = true;
            speedUnder10kReported = false;
            speedUnder10kStartedAt = current.RecordedAt;
            speedUnder10kPeak = indicatedAirspeed!.Value;
            speedUnder10kPhase = FlightTrackingEngine.ToExternalPhase(phase);
            return;
        }

        speedUnder10kPeak = Math.Max(speedUnder10kPeak, indicatedAirspeed!.Value);
        if (!speedUnder10kReported
            && current.RecordedAt - speedUnder10kStartedAt >= SpeedUnder10kViolationDuration)
        {
            AddSpeedUnder10kObservation(result);
            speedUnder10kReported = true;
        }
    }

    private void CloseSpeedUnder10k(AircraftSnapshot current, List<FdmObservation> result)
    {
        if (!speedUnder10kReported
            && current.RecordedAt - speedUnder10kStartedAt >= SpeedUnder10kViolationDuration)
            AddSpeedUnder10kObservation(result);

        ResetSpeedUnder10k();
    }

    private void ResetSpeedUnder10k()
    {
        speedUnder10kSegment = false;
        speedUnder10kReported = false;
        speedUnder10kStartedAt = default;
        speedUnder10kPeak = 0;
        speedUnder10kPhase = null;
    }

    private void AddSpeedUnder10kObservation(List<FdmObservation> result)
    {
        result.Add(new(
            "SPEED_UNDER_10K",
            "speed",
            speedUnder10kStartedAt,
            $"Survitesse sous 10 000 ft : IAS supérieure à {SpeedUnder10kLimitKnots:0} kt pendant au moins {SpeedUnder10kViolationDuration.TotalSeconds:0} s (pic {speedUnder10kPeak:0.0} kt).",
            "attention",
            Math.Round(speedUnder10kPeak, 1),
            "kt",
            speedUnder10kPhase,
            "EXCEEDED"));
    }


    private void RecordLoadFactor(AircraftSnapshot current, FlightPhase phase, List<FdmObservation> result)
    {
        if (current.OnGround != false || current.GForce is not { } g) return;

        var severe = g >= LoadFactorMaintenancePositiveLimit || g <= LoadFactorMaintenanceNegativeLimit;
        if (severe && !loadFactorMaintenanceReported)
        {
            result.Add(new(
                "LOAD_FACTOR_MAINTENANCE",
                "flight_dynamics",
                current.RecordedAt,
                $"Facteur de charge structurel : {g:0.00} g. Mise en maintenance requise.",
                "warning",
                Math.Round(g, 2),
                "g",
                FlightTrackingEngine.ToExternalPhase(phase),
                "MAINTENANCE_REQUIRED"));

            loadFactorMaintenanceReported = true;
            loadFactorExceededReported = true;
            return;
        }

        var exceeded = g >= LoadFactorPositiveLimit || g <= LoadFactorNegativeLimit;
        if (!exceeded || loadFactorExceededReported) return;

        result.Add(new(
            "LOAD_FACTOR_EXCEEDED",
            "flight_dynamics",
            current.RecordedAt,
            $"Facteur de charge hors enveloppe : {g:0.00} g.",
            "attention",
            Math.Round(g, 2),
            "g",
            FlightTrackingEngine.ToExternalPhase(phase),
            "EXCEEDED"));
        loadFactorExceededReported = true;
    }

    private void RecordBankExcursion(AircraftSnapshot current, FlightPhase phase, List<FdmObservation> result)
    {
        if (current.OnGround == true || current.BankDegrees is null) {
            if (bankExcursion) CloseBankExcursion(current, phase, result);
            return;
        }

        var bank = Math.Abs(current.BankDegrees.Value);
        if (!bankExcursion && bank >= BankEnterDegrees) {
            bankExcursion = true;
            bankPeak = bank;
            bankStartedAt = current.RecordedAt;
            return;
        }

        if (!bankExcursion) return;
        bankPeak = Math.Max(bankPeak, bank);
        if (bank <= BankExitDegrees)
            CloseBankExcursion(current, phase, result);
    }

    private void CloseBankExcursion(AircraftSnapshot current, FlightPhase phase, List<FdmObservation> result)
    {
        var duration = Math.Max(0, (current.RecordedAt - bankStartedAt).TotalSeconds);
        result.Add(new(
            "EXCESSIVE_BANK",
            "flight_controls",
            bankStartedAt,
            $"Bank supérieur à {BankEnterDegrees:0}° ; pic {bankPeak:0.0}° pendant environ {duration:0} s.",
            "attention",
            Math.Round(bankPeak, 1),
            "deg",
            FlightTrackingEngine.ToExternalPhase(phase)));
        bankExcursion = false;
        bankPeak = 0;
        bankStartedAt = default;
    }

    private static void RecordFlightEvents(
        IReadOnlyList<FlightEvent> flightEvents,
        FlightPhase phase,
        List<FdmObservation> result)
    {
        foreach (var fact in flightEvents)
        {
            var phaseName = FlightTrackingEngine.ToExternalPhase(phase);
            switch (fact.Type)
            {
                case "FUEL_INCREASED" when fact.Snapshot.OnGround == false:
                    result.Add(new("FUEL_ADDED", "fuel", fact.OccurredAt,
                        $"Carburant ajouté en vol : {fact.Value ?? 0:0} lb.", "attention",
                        fact.Value, "lb", phaseName));
                    break;
                case "FUEL_INCREASED":
                    // Defensive guard for events restored/emitted by an older
                    // tracker: ground refuelling is normal and must not surface
                    // as an FDM anomaly.
                    break;
                case "SLEW_ACTIVE":
                    result.Add(new("SLEW", "simulator", fact.OccurredAt,
                        "Mode slew détecté pendant l'enregistrement.", "warning", null, null, phaseName));
                    break;
                case "SIM_RATE_INCREASED":
                    result.Add(new("SIM_RATE", "simulator", fact.OccurredAt,
                        $"Vitesse simulation portée à x{fact.Value ?? 1:0.##}.", "attention",
                        fact.Value, "x", phaseName));
                    break;
                case "TOUCHDOWN":
                    result.Add(new("TOUCHDOWN", "landing", fact.OccurredAt,
                        $"Touchdown confirmé à {fact.Value ?? 0:0} ft/min.", "info",
                        fact.Value, "ft/min", phaseName));
                    break;
                case "BOUNCE_COUNT":
                    result.Add(new("BOUNCE", "landing", fact.OccurredAt,
                        $"{fact.Value ?? 0:0} rebond(s) détecté(s) avant le touchdown confirmé.", "attention",
                        fact.Value, "count", phaseName));
                    break;
                case "GO_AROUND":
                    result.Add(new("GO_AROUND", "approach", fact.OccurredAt,
                        "Remise de gaz détectée avant contact piste.", "info", null, null, phaseName));
                    break;
                case "TOUCH_AND_GO":
                    result.Add(new("TOUCH_AND_GO", "landing", fact.OccurredAt,
                        "Touch-and-go détecté ; aucun événement ON n'a été produit.", "info",
                        fact.Value, "touches", phaseName));
                    break;
            }
        }
    }
}
