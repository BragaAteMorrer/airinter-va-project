namespace Promethee;

public sealed class TelemetryService(ISimulatorConnector sim, FlightRecorder recorder, PhpVmsClient client)
{
    public async Task Tick()
    {
        sim.Poll();
        if (sim.LatestSnapshot is not null) recorder.Capture(sim.LatestSnapshot);
        if (client.Connected) {
            try { await SendPending(client, recorder); } catch { /* queued locally until the next successful sync */ }
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
                    var telemetryPath = string.IsNullOrWhiteSpace(flight.OperationId)\n                    ? $"promethee/pireps/{Uri.EscapeDataString(flight.PirepId)}/telemetry"\n                    : $"v1/operations/{Uri.EscapeDataString(flight.OperationId)}/telemetry";\n                await client.Send(telemetryPath, new {
                        samples = pending.Select(x => new {
                            sample_id=x.Sample.SampleId, recorded_at=x.Sample.RecordedAt, lat=x.Sample.Lat, lon=x.Sample.Lon,
                            altitude_msl=x.Sample.Altitude, agl=x.Sample.Agl, ias=x.Sample.Ias, gs=x.Sample.Gs,
                            vs=x.Sample.Vs, heading=x.Sample.Heading, fuel=x.Sample.Fuel, bank=x.Sample.Bank,
                            on_ground=x.Sample.OnGround, gear_down=x.Sample.GearDown, landing_flaps=x.Sample.Flaps > 0,
                            thrust_stable=x.Sample.ThrustStable, phase=flight.Phase
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
