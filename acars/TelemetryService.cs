namespace Promethee;

public sealed class TelemetryService(ISimulatorConnector sim, FlightRecorder recorder, PhpVmsClient client)
{
    private static readonly TimeSpan MaxRetryDelay = TimeSpan.FromSeconds(60);
    private DateTimeOffset nextSyncAttemptAt = DateTimeOffset.MinValue;
    private int consecutiveFailures;
    private bool? simulatorWasAvailable;
    private bool networkWasDegraded;

    public string SyncState { get; private set; } = "IDLE";
    public DateTimeOffset? LastSuccessfulSyncAt { get; private set; }
    public DateTimeOffset? NextSyncAttemptAt => nextSyncAttemptAt == DateTimeOffset.MinValue ? null : nextSyncAttemptAt;
    public string? LastSyncError { get; private set; }
    public int ConsecutiveFailures => consecutiveFailures;

    public async Task Tick()
    {
        sim.Poll();
        var now = DateTimeOffset.UtcNow;
        var simulatorAvailable = sim.LatestSnapshot is not null;
        if (recorder.Flight?.Recording == true) {
            if (simulatorWasAvailable == true && !simulatorAvailable)
                recorder.RecordLocalOperationalEvent("SIMULATOR_LOST", now);
            else if (simulatorWasAvailable == false && simulatorAvailable)
                recorder.RecordLocalOperationalEvent("SIMULATOR_RECOVERED", now);
        }
        simulatorWasAvailable = simulatorAvailable;
        if (sim.LatestSnapshot is not null) recorder.Capture(sim.LatestSnapshot);

        if (!client.Connected) {
            SyncState = "DISCONNECTED";
            return;
        }

        if (now < nextSyncAttemptAt) {
            SyncState = "RETRYING";
            return;
        }

        try {
            await SendPending(client, recorder);
            if (networkWasDegraded && recorder.Flight?.Recording == true)
                recorder.RecordLocalOperationalEvent("NETWORK_RECOVERED", now);
            networkWasDegraded = false;
            consecutiveFailures = 0;
            nextSyncAttemptAt = DateTimeOffset.MinValue;
            LastSyncError = null;
            LastSuccessfulSyncAt = now;
            SyncState = "ONLINE";
        } catch (Exception exception) {
            // Tracking is deliberately independent from the network. Failed
            // messages remain in FlightRecorder and will be retried at least once.
            if (!networkWasDegraded && recorder.Flight?.Recording == true)
                recorder.RecordLocalOperationalEvent("NETWORK_LOST", now);
            networkWasDegraded = true;
            consecutiveFailures++;
            var seconds = Math.Min(MaxRetryDelay.TotalSeconds, Math.Pow(2, Math.Min(consecutiveFailures, 6)));
            nextSyncAttemptAt = now.AddSeconds(seconds);
            LastSyncError = exception.Message;
            SyncState = "RETRYING";
        }
    }

    public async Task<int> SyncNow()
    {
        if (!client.Connected) throw new InvalidOperationException("Connectez-vous à Prométhée avant de synchroniser.");
        try {
            var sent = await SendPending(client, recorder);
            if (networkWasDegraded && recorder.Flight?.Recording == true)
                recorder.RecordLocalOperationalEvent("NETWORK_RECOVERED", DateTimeOffset.UtcNow);
            networkWasDegraded = false;
            consecutiveFailures = 0;
            nextSyncAttemptAt = DateTimeOffset.MinValue;
            LastSyncError = null;
            LastSuccessfulSyncAt = DateTimeOffset.UtcNow;
            SyncState = "ONLINE";
            return sent;
        } catch (Exception exception) {
            if (!networkWasDegraded && recorder.Flight?.Recording == true)
                recorder.RecordLocalOperationalEvent("NETWORK_LOST", DateTimeOffset.UtcNow);
            networkWasDegraded = true;
            consecutiveFailures++;
            var seconds = Math.Min(MaxRetryDelay.TotalSeconds, Math.Pow(2, Math.Min(consecutiveFailures, 6)));
            nextSyncAttemptAt = DateTimeOffset.UtcNow.AddSeconds(seconds);
            LastSyncError = exception.Message;
            SyncState = "RETRYING";
            throw;
        }
    }

    public static async Task<int> SendPending(PhpVmsClient client, FlightRecorder recorder)
    {
        await recorder.NetworkGate.WaitAsync();
        try {
            List<Envelope> pending;
            List<AcarsEvent> events;
            List<SopFactEnvelope> facts;
            FlightState? flight;
            lock (recorder.Gate) {
                flight = recorder.Flight;
                pending = recorder.Pending.Take(30).ToList();
                events = recorder.PendingEvents.Take(20).ToList();
                facts = recorder.PendingFacts.Take(50).ToList();
            }
            if (flight is null || (pending.Count == 0 && events.Count == 0 && facts.Count == 0)) return 0;
            if (pending.Count > 0) {
                try {
                    var telemetryPath = string.IsNullOrWhiteSpace(flight.OperationId)
                    ? $"promethee/pireps/{Uri.EscapeDataString(flight.PirepId)}/telemetry"
                    : $"v1/operations/{Uri.EscapeDataString(flight.OperationId)}/telemetry";
                await client.Send(telemetryPath, new {
                        samples = pending.Select(x => {
                        var raw = x.Snapshot;
                        return new {
                            sample_id=x.Sample.SampleId, recorded_at=x.Sample.RecordedAt, lat=x.Sample.Lat, lon=x.Sample.Lon,
                            altitude_msl=raw?.AltitudeMslFeet ?? x.Sample.Altitude,
                            agl=raw?.AltitudeAglFeet ?? x.Sample.Agl,
                            ias=raw?.IndicatedAirspeedKnots ?? x.Sample.Ias,
                            gs=raw?.GroundSpeedKnots ?? x.Sample.Gs,
                            vs=raw?.VerticalSpeedFeetPerMinute ?? x.Sample.Vs,
                            heading=raw?.HeadingDegrees ?? x.Sample.Heading,
                            track=raw?.TrackDegrees,
                            mach=raw?.Mach,
                            fuel=raw?.FuelWeight ?? x.Sample.Fuel,
                            gross_weight=raw?.GrossWeight,
                            qnh_hpa=raw?.QnhHpa,
                            oat_c=raw?.OutsideAirTemperatureCelsius,
                            wind_speed=raw?.WindSpeedKnots,
                            wind_direction=raw?.WindDirectionDegrees,
                            bank=raw?.BankDegrees,
                            pitch=raw?.PitchDegrees,
                            g_force=raw?.GForce,
                            overspeed_warning=raw?.OverspeedWarning,
                            stall_warning=raw?.StallWarning,
                            reverser_percent=raw?.ThrustReverserPercent,
                            on_ground=raw?.OnGround ?? x.Sample.OnGround,
                            parking_brake=raw?.ParkingBrake,
                            gear_down=raw?.GearDown,
                            flaps_percent=raw?.FlapsPercent,
                            spoilers_armed=raw?.SpoilersArmed,
                            engines_running=raw?.EnginesRunning,
                            beacon_light=raw?.BeaconLight,
                            navigation_light=raw?.NavigationLight,
                            strobe_light=raw?.StrobeLight,
                            landing_light=raw?.LandingLight,
                            taxi_light=raw?.TaxiLight,
                            seatbelt_sign=raw?.SeatBeltSign,
                            doors_open=raw?.DoorsOpen,
                            transponder_code=raw?.TransponderCode,
                            autopilot_enabled=raw?.AutopilotEnabled,
                            aircraft_title=raw?.AircraftTitle,
                            aircraft_icao=raw?.AircraftIcao,
                            aircraft_model=raw?.AircraftModel,
                            touchdown_rate=raw?.TouchdownVerticalSpeedFeetPerMinute,
                            landing_flaps=raw?.FlapsPercent is { } flaps ? flaps > 0 : (bool?)null,
                            thrust_stable=raw?.ThrustStable ?? (raw is null ? (bool?)x.Sample.ThrustStable : null),
                            localizer_dots=raw?.LocalizerDots ?? (raw is null ? (double?)x.Sample.LocalizerDots : null),
                            glideslope_dots=raw?.GlideslopeDots ?? (raw is null ? (double?)x.Sample.GlideslopeDots : null),
                            slew_active=raw?.SlewActive,
                            simulation_rate=raw?.SimulationRate,
                            paused=raw?.Paused,
                            pause_kind=raw?.PauseKind,
                            phase=x.Phase ?? flight.Phase
                        };
                    })
                    });
                } catch (InvalidOperationException) {
                    // The detailed archive is optional during a rolling server
                    // upgrade. Standard ACARS positions below must still flow.
                }
                await client.Send($"pireps/{Uri.EscapeDataString(flight.PirepId)}/acars/positions", new { positions = pending.Select(x => new {
                    id=x.Sample.SampleId, lat=x.Sample.Lat, lon=x.Sample.Lon, altitude_msl=x.Sample.Altitude, altitude_agl=x.Sample.Agl,
                    gs=x.Sample.Gs, ias=x.Sample.Ias, vs=x.Sample.Vs, heading=x.Sample.Heading, fuel=x.Sample.Fuel, sim_time=x.Sample.RecordedAt, created_at=x.Sample.RecordedAt }) });
                recorder.AcknowledgePositions(pending.Select(x => x.Sample.SampleId));
            }
            if (events.Count > 0) {
                await client.Send($"pireps/{Uri.EscapeDataString(flight.PirepId)}/acars/events", new { events = events.Select(x => new { id=x.EventId, @event=x.Name, lat=x.Lat, lon=x.Lon, created_at=x.OccurredAt }) });
                recorder.AcknowledgeEvents(events.Select(x => x.EventId));
            }
            if (facts.Count > 0 && !string.IsNullOrWhiteSpace(flight.OperationId)) {
                await client.Send($"v1/operations/{Uri.EscapeDataString(flight.OperationId)}/sop/facts", new {
                    facts = facts.Select(x => new {
                        fact_id = x.FactId,
                        code = x.Observation.Code,
                        category = x.Observation.Category,
                        occurred_at = x.Observation.OccurredAt,
                        message = x.Observation.Message,
                        source_severity = x.Observation.Severity,
                        value = x.Observation.Value,
                        unit = x.Observation.Unit,
                        phase = x.Observation.Phase,
                        status = x.Observation.Status
                    })
                });
                recorder.AcknowledgeFacts(facts.Select(x => x.FactId));
            }
            return pending.Count + events.Count + facts.Count;
        } finally { recorder.NetworkGate.Release(); }
    }
}
