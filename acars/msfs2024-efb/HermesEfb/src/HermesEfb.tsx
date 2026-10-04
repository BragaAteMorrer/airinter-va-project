import {
  App,
  AppBootMode,
  AppInstallProps,
  AppSuspendMode,
  AppView,
  AppViewProps,
  Efb,
  RequiredProps,
  TVNode,
} from "@efb/efb-api";
import { FSComponent, VNode } from "@microsoft/msfs-sdk";

import "./HermesEfb.scss";

declare const BASE_URL: string;

interface CommBusListener {
  on(name: string, callback: (payload: string) => void): void;
  callSimConnect(name: string, payload: string): Promise<unknown>;
}
declare function RegisterCommBusListener(callback?: () => void): CommBusListener;

type HermesEnvelope = {
  version?: number;
  requestId?: string;
  ok?: boolean;
  error?: string;
  generatedAt?: string;
  context?: {
    operationId?: string | null;
    pirepId?: string | null;
    flightIdent?: string | null;
    departure?: string | null;
    arrival?: string | null;
    alternate?: string | null;
    route?: string | null;
    aircraftRegistration?: string | null;
    aircraftIcao?: string | null;
    aircraftModel?: string | null;
    passengers?: number | null;
    flightLevel?: number | null;
    costIndex?: string | null;
    blockFuel?: number | null;
    estimatedTimeEnroute?: number | null;
    ofpSource?: string | null;
    dispatchStatus?: string | null;
  } | null;
  state?: {
    hermesConnected?: boolean;
    simulator?: {
      id?: string | null;
      linkState?: string | null;
      connector?: string | null;
      name?: string | null;
      commBusAvailable?: boolean;
    } | null;
    flight?: {
      phase?: string | null;
      recording?: boolean;
      distance?: number;
      fuelUsed?: number;
      pausedSeconds?: number;
    } | null;
    telemetry?: {
      altitude?: number | null;
      agl?: number | null;
      ias?: number | null;
      gs?: number | null;
      heading?: number | null;
      vs?: number | null;
      fuel?: number | null;
      onGround?: boolean | null;
      paused?: boolean | null;
    } | null;
    review?: {
      readyToFile?: boolean;
      distance?: number;
      airborneMinutes?: number;
      blockMinutes?: number;
      fuelUsed?: number;
      landingRate?: number | null;
      pauseCount?: number;
      pausedSeconds?: number;
    } | null;
    pending?: number;
    recoveryAvailable?: boolean;
    warning?: string | null;
  } | null;
};

const REQUEST_EVENT = "AIRINTER_HERMES_EFB_REQUEST";
const STATE_EVENT = "AIRINTER_HERMES_EFB_STATE";

class HermesEfbView extends AppView<RequiredProps<AppViewProps, "bus">> {
  private readonly bridgeState = FSComponent.createRef<HTMLSpanElement>();
  private readonly operation = FSComponent.createRef<HTMLSpanElement>();
  private readonly route = FSComponent.createRef<HTMLSpanElement>();
  private readonly aircraft = FSComponent.createRef<HTMLSpanElement>();
  private readonly phase = FSComponent.createRef<HTMLSpanElement>();
  private readonly dispatch = FSComponent.createRef<HTMLSpanElement>();
  private readonly altitude = FSComponent.createRef<HTMLSpanElement>();
  private readonly speed = FSComponent.createRef<HTMLSpanElement>();
  private readonly fuel = FSComponent.createRef<HTMLSpanElement>();
  private readonly pax = FSComponent.createRef<HTMLSpanElement>();
  private readonly flightLevel = FSComponent.createRef<HTMLSpanElement>();
  private readonly costIndex = FSComponent.createRef<HTMLSpanElement>();
  private readonly pending = FSComponent.createRef<HTMLSpanElement>();
  private readonly warning = FSComponent.createRef<HTMLDivElement>();
  private readonly syncTime = FSComponent.createRef<HTMLSpanElement>();

  private listener?: CommBusListener;
  private timer?: number;
  private lastStateAt = 0;

  public onAfterRender(node: VNode): void {
    super.onAfterRender(node);
    this.connectBridge();
    this.startPolling();
  }

  public onResume(): void {
    super.onResume();
    this.startPolling();
  }

  public onPause(): void {
    this.stopPolling();
    super.onPause();
  }

  public onClose(): void {
    this.stopPolling();
    super.onClose();
  }

  public destroy(): void {
    this.stopPolling();
    super.destroy();
  }

  private connectBridge(): void {
    if (this.listener) return;
    try {
      this.listener = RegisterCommBusListener(() => this.requestState());
      this.listener.on(STATE_EVENT, payload => this.receiveState(payload));
      this.set(this.bridgeState, "Connexion Hermès…");
      this.requestState();
    } catch {
      this.set(this.bridgeState, "CommBus indisponible");
    }
  }

  private startPolling(): void {
    this.stopPolling();
    this.requestState();
    this.timer = window.setInterval(() => this.requestState(), 1500);
  }

  private stopPolling(): void {
    if (this.timer !== undefined) window.clearInterval(this.timer);
    this.timer = undefined;
  }

  private requestState(): void {
    if (!this.listener) return;
    const requestId = `efb-${Date.now()}-${Math.round(Math.random() * 100000)}`;
    void this.listener.callSimConnect(
      REQUEST_EVENT,
      JSON.stringify({ version: 1, action: "state", requestId })
    ).catch(() => this.set(this.bridgeState, "Hermès non joignable"));
  }

  private receiveState(payload: string): void {
    let envelope: HermesEnvelope;
    try {
      envelope = JSON.parse(payload) as HermesEnvelope;
    } catch {
      this.set(this.bridgeState, "Réponse Hermès invalide");
      return;
    }

    if (!envelope.ok) {
      this.set(this.bridgeState, envelope.error || "Hermès indisponible");
      return;
    }

    const context = envelope.context || {};
    const state = envelope.state || {};
    const telemetry = state.telemetry || {};
    const simulator = state.simulator || {};
    const review = state.review || {};
    const live = state.flight || {};

    this.lastStateAt = Date.now();
    this.set(this.bridgeState, state.hermesConnected ? "Hermès connecté" : "Hermès hors ligne");
    this.set(this.operation, context.flightIdent || context.operationId || "Aucune opération");
    this.set(this.route, context.departure && context.arrival
      ? `${context.departure} → ${context.arrival}`
      : "—");
    this.set(this.aircraft, [context.aircraftRegistration, context.aircraftIcao || context.aircraftModel].filter(Boolean).join(" · ") || "—");
    this.set(this.phase, live.phase || "STANDBY");
    this.set(this.dispatch, context.dispatchStatus || "—");
    this.set(this.altitude, this.number(telemetry.altitude, 0, " ft"));
    this.set(this.speed, this.number(telemetry.gs ?? telemetry.ias, 0, " kt"));
    this.set(this.fuel, this.number(telemetry.fuel, 0, " lb"));
    this.set(this.pax, context.passengers === null || context.passengers === undefined ? "—" : String(context.passengers));
    this.set(this.flightLevel, context.flightLevel ? `FL${String(context.flightLevel).padStart(3, "0")}` : "—");
    this.set(this.costIndex, context.costIndex || "—");
    this.set(this.pending, String(state.pending ?? 0));

    const messages: string[] = [];
    if (simulator.linkState && simulator.linkState !== "CONNECTED") messages.push("SIM " + simulator.linkState);
    if (state.recoveryAvailable) messages.push("RECOVERY DISPONIBLE");
    if (telemetry.paused) messages.push("SIM PAUSE");
    if (review.readyToFile) messages.push("PIREP PRÊT À DÉPOSER");
    if (state.warning) messages.push(state.warning);
    this.warning.instance.textContent = messages.join(" · ");
    this.warning.instance.hidden = messages.length === 0;

    const stamp = envelope.generatedAt ? new Date(envelope.generatedAt) : new Date();
    this.set(this.syncTime, stamp.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", second: "2-digit" }));
  }

  private set(ref: { instance: HTMLElement }, value: string): void {
    if (ref.instance) ref.instance.textContent = value;
  }

  private number(value: number | null | undefined, digits: number, suffix: string): string {
    return value === null || value === undefined || !Number.isFinite(value)
      ? "—"
      : `${value.toFixed(digits)}${suffix}`;
  }

  public render(): VNode {
    return (
      <div class="airinter-hermes-efb">
        <header class="efb-header">
          <div>
            <span class="eyebrow">AIR INTER · HERMÈS</span>
            <h1>Opération de vol</h1>
          </div>
          <div class="bridge-status">
            <strong ref={this.bridgeState}>Connexion Hermès…</strong>
            <small>MAJ <span ref={this.syncTime}>—</span></small>
          </div>
        </header>

        <div class="efb-warning" ref={this.warning} hidden></div>

        <section class="flight-hero">
          <div>
            <span class="label">VOL</span>
            <strong ref={this.operation}>Aucune opération</strong>
          </div>
          <div>
            <span class="label">ROUTE</span>
            <strong ref={this.route}>—</strong>
          </div>
          <div>
            <span class="label">APPAREIL</span>
            <strong ref={this.aircraft}>—</strong>
          </div>
        </section>

        <section class="metric-grid">
          <article><span>PHASE</span><strong ref={this.phase}>STANDBY</strong></article>
          <article><span>DISPATCH</span><strong ref={this.dispatch}>—</strong></article>
          <article><span>ALTITUDE</span><strong ref={this.altitude}>—</strong></article>
          <article><span>VITESSE SOL</span><strong ref={this.speed}>—</strong></article>
          <article><span>CARBURANT</span><strong ref={this.fuel}>—</strong></article>
          <article><span>SYNC EN ATTENTE</span><strong ref={this.pending}>0</strong></article>
        </section>

        <section class="ofp-strip">
          <div><span>PAX</span><strong ref={this.pax}>—</strong></div>
          <div><span>NIVEAU</span><strong ref={this.flightLevel}>—</strong></div>
          <div><span>CI</span><strong ref={this.costIndex}>—</strong></div>
        </section>

        <footer>
          <span>Lecture seule · les actions PIREP restent dans Hermès</span>
          <button onClick={() => this.requestState()}>Actualiser</button>
        </footer>
      </div>
    );
  }
}

class AirInterHermesApp extends App {
  public get name(): string {
    return "Air Inter Hermès";
  }

  public get icon(): string {
    return `${BASE_URL}/Assets/hermes-efb.svg`;
  }

  public BootMode = AppBootMode.COLD;
  public SuspendMode = AppSuspendMode.SLEEP;

  public async install(_props: AppInstallProps): Promise<void> {
    await Efb.loadJs("/JS/Services/CommBus.js");
    await Efb.loadCss(`${BASE_URL}/HermesEfb.css?v=${Date.now()}`);
  }

  public get compatibleAircraftModels(): string[] | undefined {
    return undefined;
  }

  public render(): TVNode<HermesEfbView> {
    return <HermesEfbView bus={this.bus} />;
  }
}

Efb.use(AirInterHermesApp);
