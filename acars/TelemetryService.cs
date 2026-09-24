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
            FlightState? flight;
            lock (recorder.Gate) {
                flight = recorder.Flight;
                pending = recorder.Pending.Take(30).ToList();
                events = recorder.PendingEvents.Take(20).ToList();
            }
            if (flight is null || (pending.Count == 0 && events.Count == 0)) return 0;
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
                            fuel=raw?.FuelWeight ?? x.Sample.Fuel,
                            bank=raw?.BankDegrees,
                            on_ground=raw?.OnGround ?? x.Sample.OnGround,
                            gear_down=raw?.GearDown,
                            landing_flaps=raw?.FlapsPercent is { } flaps ? flaps > 0 : (bool?)null,
                            thrust_stable=raw?.ThrustStable ?? (raw is null ? (bool?)x.Sample.ThrustStable : null),
                            phase=flight.Phase
                        };
                    })
                    });
                } catch (InvalidOperationException) {
                    // The detailed archive is optional during a rolling server
                    // upgrade. Standard ACARS positions below must still flow.
                }
                await client.Send($"pireps/{Uri.EscapeDataString(flight.PirepId)}/acars/positions", new { positions = pending.Select(x => new {
                    id=x.Sample.SampleId, lat=x.Sample.Lat, lon=x.Sample.Lon, altitude_msl=x.Sample.Altitude, altitude_agl=x.Sample.Agl,
                    gs=x.Sample.Gs, vs=x.Sample.Vs, heading=x.Sample.Heading, fuel=x.Sample.Fuel, sim_time=x.Sample.RecordedAt, created_at=x.Sample.RecordedAt }) });
                recorder.AcknowledgePositions(pending.Select(x => x.Sample.SampleId));
            }
            if (events.Count > 0) {
                await client.Send($"pireps/{Uri.EscapeDataString(flight.PirepId)}/acars/events", new { events = events.Select(x => new { id=x.EventId, @event=x.Name, lat=x.Lat, lon=x.Lon, created_at=x.OccurredAt }) });
                recorder.AcknowledgeEvents(events.Select(x => x.EventId));
            }
            return pending.Count + events.Count;
        } finally { recorder.NetworkGate.Release(); }
    }
}
