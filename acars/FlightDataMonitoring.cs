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
    IReadOnlyList<FlightIssue> Issues,
    IReadOnlyList<FdmObservation> Observations,
    IReadOnlyList<PhaseEntry> Timeline);

/// <summary>
/// Simulator-neutral Flight Data Monitoring. It records observations only:
/// scoring and company policy remain outside this class.
/// </summary>
public sealed class FlightDataMonitor
{
    private const double BankEnterDegrees = 35;
    private const double BankExitDegrees = 30;

    private AircraftSnapshot? previous;
    private bool approach1000Recorded;
    private bool approach500Recorded;
    private bool bankExcursion;
    private double bankPeak;
    private DateTimeOffset bankStartedAt;

    public void Reset()
    {
        previous = null;
        approach1000Recorded = false;
        approach500Recorded = false;
        bankExcursion = false;
        bankPeak = 0;
        bankStartedAt = default;
    }

    public void Restore(IEnumerable<FdmObservation>? existing)
    {
        Reset();
        if (existing is null) return;
        var codes = existing.Select(x => x.Code).ToHashSet(StringComparer.Ordinal);
        approach1000Recorded = codes.Any(x => x.StartsWith("APPROACH_1000_", StringComparison.Ordinal));
        approach500Recorded = codes.Any(x => x.StartsWith("APPROACH_500_", StringComparison.Ordinal));
    }

    public IReadOnlyList<FdmObservation> Process(
        AircraftSnapshot current,
        FlightPhase phase,
        IReadOnlyList<FlightEvent> flightEvents)
    {
        var result = new List<FdmObservation>();

        RecordApproachGate(current, phase, 1000, 1200, ref approach1000Recorded, result);
        RecordApproachGate(current, phase, 500, 1000, ref approach500Recorded, result);
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
        return result;
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
                case "FUEL_INCREASED":
                    result.Add(new("FUEL_ADDED", "fuel", fact.OccurredAt,
                        $"Carburant ajouté en vol : {fact.Value ?? 0:0} lb.", "attention",
                        fact.Value, "lb", phaseName));
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
