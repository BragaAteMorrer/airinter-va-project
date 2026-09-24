@extends('promethee::layout')
@section('title','Dispatch Desk')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/dispatch-desk.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
<div class="ops-header compact dispatch-heading">
    <div>
        <span class="eyebrow">OCC · DISPATCH DESK</span>
        <h1>Contrôle opérationnel en temps réel.</h1>
        <p>Vols, Hermès, télémétrie, alertes, OFP, FDM et communications cockpit sur une seule surface.</p>
    </div>
    <div class="dispatch-heading-actions">
        <span class="tag" id="dispatch-updated">Connexion au centre opérations…</span>
        <button class="button secondary" type="button" id="dispatch-refresh">Actualiser</button>
    </div>
</div>

<section class="control-strip dispatch-kpis">
    <article><span>Opérations</span><strong id="dispatch-count">—</strong><small>réservées ou actives</small></article>
    <article><span>En vol</span><strong id="dispatch-airborne">—</strong><small>télémétrie Hermès active</small></article>
    <article><span>Attention</span><strong id="dispatch-attention">—</strong><small>signal, message ou alerte</small></article>
    <article><span>ACK OPS</span><strong id="dispatch-ack">—</strong><small>messages cockpit à traiter</small></article>
</section>

<div class="dispatch-overview-grid">
    <section class="panel dispatch-board-panel">
        <div class="panel-heading">
            <div><span class="eyebrow">LIVE OPERATIONS</span><h2>Table des vols</h2></div>
            <label class="dispatch-filter">Filtrer <input id="dispatch-filter" type="search" placeholder="ITF, LFPO, pilote…"></label>
        </div>
        <div class="table-wrap dispatch-board-wrap">
            <table class="dispatch-board">
                <thead><tr><th>Vol</th><th>Phase</th><th>Appareil</th><th>Pilote</th><th>Signal</th><th>OPS</th></tr></thead>
                <tbody id="dispatch-rows"><tr><td colspan="6">Chargement des opérations…</td></tr></tbody>
            </table>
        </div>
    </section>

    <section class="panel dispatch-map-panel">
        <div class="panel-heading"><div><span class="eyebrow">SITUATION AIR</span><h2>Carte opérations</h2></div></div>
        <div id="dispatch-map" class="dispatch-map" aria-label="Carte des opérations Air Inter"></div>
        <p class="muted">Positions issues de la dernière télémétrie Hermès reçue par Prométhée.</p>
    </section>
</div>

<section class="panel dispatch-detail" id="dispatch-detail">
    <div class="dispatch-empty" id="dispatch-empty">
        <strong>Sélectionnez une opération.</strong>
        <span>La fiche dispatcher affichera ici le vol, l’OFP, le suivi, le FDM et le Datalink.</span>
    </div>
    <div id="dispatch-workspace" hidden>
        <div class="dispatch-operation-header">
            <div>
                <span class="eyebrow" id="dispatch-operation-id">OPÉRATION</span>
                <h2 id="dispatch-operation-title">—</h2>
                <p id="dispatch-operation-meta">—</p>
            </div>
            <div class="dispatch-operation-state">
                <span class="dispatch-state" id="dispatch-operation-status">—</span>
                <span class="tag" id="dispatch-operation-signal">—</span>
            </div>
        </div>

        <nav class="dispatch-tabs" aria-label="Détails opération">
            @foreach(['overview'=>'Overview','aircraft'=>'Aircraft','ofp'=>'OFP','route'=>'Route','weather'=>'Weather','track'=>'Track','fdm'=>'FDM','sop'=>'SOP','messages'=>'Messages','timeline'=>'Timeline'] as $key=>$label)
            <button type="button" data-dispatch-tab="{{ $key }}" @class(['selected'=>$key==='overview'])>{{ strtoupper($label) }}</button>
            @endforeach
        </nav>
        <div id="dispatch-tab-content" class="dispatch-tab-content"></div>
    </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
    const feedUrl = @json(route('admin.promethee.dispatch.feed'));
    const operationBase = @json(url('/admin/promethee/dispatch/operations'));
    const datalinkSendUrl = @json(route('admin.promethee.datalink.messages.send'));
    const datalinkAckBase = @json(url('/admin/promethee/datalink/messages'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const state = { operations: [], selected: null, detail: null, tab: 'overview', timer: null, map: null, layer: null };

    const rows = document.querySelector('#dispatch-rows');
    const updated = document.querySelector('#dispatch-updated');
    const filter = document.querySelector('#dispatch-filter');
    const workspace = document.querySelector('#dispatch-workspace');
    const empty = document.querySelector('#dispatch-empty');
    const content = document.querySelector('#dispatch-tab-content');

    const esc = value => String(value ?? '—').replace(/[&<>"']/g, char => ({
        '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'
    }[char]));

    const number = (value, suffix='') => value === null || value === undefined || value === ''
        ? '—'
        : Math.round(Number(value)).toLocaleString('fr-FR') + suffix;

    const time = value => value
        ? new Date(value).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})
        : '—';

    const dateTime = value => value
        ? new Date(value).toLocaleString('fr-FR', {day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit', second:'2-digit'})
        : '—';

    const badge = (text, kind='neutral') => '<span class="dispatch-badge ' + esc(kind) + '">' + esc(text) + '</span>';

    function statusKind(status) {
        if (status === 'IN_PROGRESS') return 'ok';
        if (status === 'PAUSED' || status === 'READY') return 'warn';
        return 'neutral';
    }

    function signalKind(signal) {
        if (signal === 'LIVE') return 'ok';
        if (signal === 'STALE' || signal === 'WAITING') return 'warn';
        if (signal === 'LOST' || signal === 'NO_SIGNAL') return 'danger';
        return 'neutral';
    }

    function initMap() {
        const node = document.querySelector('#dispatch-map');
        if (!node || !window.L || state.map) return;
        state.map = L.map(node, {scrollWheelZoom:true, minZoom:2, maxZoom:12}).setView([46.6, 2.5], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap'}).addTo(state.map);
        state.layer = L.layerGroup().addTo(state.map);
    }

    function renderMap(operations) {
        initMap();
        if (!state.map || !state.layer) return;
        state.layer.clearLayers();
        const points = [];
        operations.forEach(op => {
            const lat = op.live?.lat, lon = op.live?.lon;
            if (lat === null || lat === undefined || lon === null || lon === undefined) return;
            const marker = L.marker([lat, lon], {
                icon: L.divIcon({className:'aircraft-marker dispatch-aircraft-marker', html:'✈', iconSize:[24,24]})
            }).addTo(state.layer);
            marker.bindPopup(
                '<strong>' + esc(op.flight?.ident) + '</strong><br>' +
                esc(op.flight?.departure) + ' → ' + esc(op.flight?.arrival) + '<br>' +
                esc(op.phase || op.status) + ' · ' + number(op.live?.altitude, ' ft')
            );
            marker.on('click', () => selectOperation(op.operation_id));
            points.push([lat, lon]);
        });
        if (points.length) state.map.fitBounds(points, {padding:[30,30], maxZoom:7});
    }

    function operationMatches(op, query) {
        if (!query) return true;
        const haystack = [
            op.operation_id, op.flight?.ident, op.flight?.departure, op.flight?.arrival,
            op.pilot?.ident, op.pilot?.name, op.aircraft?.registration, op.aircraft?.icao,
            op.phase, op.status
        ].join(' ').toLowerCase();
        return haystack.includes(query.toLowerCase());
    }

    function renderBoard() {
        const query = filter.value.trim();
        const operations = state.operations.filter(op => operationMatches(op, query));

        rows.innerHTML = operations.length ? operations.map(op => {
            const alertCount = (op.alerts || []).length;
            const ack = op.datalink?.pending_ops_ack || 0;
            const selected = state.selected === op.operation_id ? ' selected' : '';
            return '<tr class="dispatch-row' + selected + '" data-operation="' + esc(op.operation_id) + '">' +
                '<td><strong>' + esc(op.flight?.ident) + '</strong><br><span class="muted">' +
                    esc(op.flight?.departure) + ' → ' + esc(op.flight?.arrival) + '</span></td>' +
                '<td>' + badge(op.phase || op.status, statusKind(op.status)) + '</td>' +
                '<td><strong>' + esc(op.aircraft?.registration || 'À affecter') + '</strong><br><span class="muted">' + esc(op.aircraft?.icao || '') + '</span></td>' +
                '<td><strong>' + esc(op.pilot?.ident) + '</strong><br><span class="muted">' + esc(op.pilot?.name) + '</span></td>' +
                '<td>' + badge(op.signal?.state || 'WAITING', signalKind(op.signal?.state)) + '<br><span class="muted">' +
                    (op.signal?.age_seconds === null || op.signal?.age_seconds === undefined ? '—' : esc(op.signal.age_seconds + ' s')) + '</span></td>' +
                '<td>' + (alertCount ? badge(alertCount + ' alerte' + (alertCount > 1 ? 's' : ''), 'danger') : badge('RAS', 'ok')) +
                    (ack ? '<br>' + badge(ack + ' ACK', 'warn') : '') + '</td>' +
            '</tr>';
        }).join('') : '<tr><td colspan="6">Aucune opération ne correspond au filtre.</td></tr>';

        rows.querySelectorAll('[data-operation]').forEach(row => {
            row.addEventListener('click', () => selectOperation(row.dataset.operation));
        });

        renderMap(operations);
    }

    async function refreshBoard() {
        try {
            const response = await fetch(feedUrl, {headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const payload = await response.json();
            const data = payload.data || {};
            state.operations = data.operations || [];
            document.querySelector('#dispatch-count').textContent = data.summary?.operations ?? 0;
            document.querySelector('#dispatch-airborne').textContent = data.summary?.airborne ?? 0;
            document.querySelector('#dispatch-attention').textContent = data.summary?.attention ?? 0;
            document.querySelector('#dispatch-ack').textContent = data.summary?.pending_ops_ack ?? 0;
            updated.textContent = 'MAJ ' + time(data.updated_at);
            renderBoard();

            if (state.selected) {
                const stillExists = state.operations.some(op => op.operation_id === state.selected);
                if (stillExists) await refreshDetail(false);
            }
        } catch (error) {
            updated.textContent = 'Flux OCC indisponible';
            rows.innerHTML = '<tr><td colspan="6">Impossible de charger le Dispatch Desk.</td></tr>';
        }
    }

    async function selectOperation(operationId) {
        state.selected = operationId;
        state.tab = 'overview';
        renderBoard();
        await refreshDetail(true);
    }

    async function refreshDetail(showLoading=true) {
        if (!state.selected) return;
        if (showLoading) {
            empty.hidden = true;
            workspace.hidden = false;
            content.innerHTML = '<div class="dispatch-loading">Chargement de l’opération…</div>';
        }

        try {
            const response = await fetch(operationBase + '/' + encodeURIComponent(state.selected), {headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const payload = await response.json();
            state.detail = payload.data;
            renderOperationHeader();
            renderTab();
        } catch (error) {
            content.innerHTML = '<div class="notice error">Impossible de charger cette opération.</div>';
        }
    }

    function renderOperationHeader() {
        const op = state.detail?.operation || {};
        document.querySelector('#dispatch-operation-id').textContent = op.operation_id || 'OPÉRATION';
        document.querySelector('#dispatch-operation-title').textContent =
            (op.flight?.ident || '—') + ' · ' + (op.flight?.departure || '—') + ' → ' + (op.flight?.arrival || '—');
        document.querySelector('#dispatch-operation-meta').textContent =
            (op.pilot?.ident || '—') + ' · ' + (op.pilot?.name || '—') + ' · ' + (op.aircraft?.registration || 'appareil non affecté');
        const status = document.querySelector('#dispatch-operation-status');
        status.textContent = op.phase || op.status || '—';
        status.className = 'dispatch-state ' + statusKind(op.status);
        const signal = document.querySelector('#dispatch-operation-signal');
        signal.textContent = 'HERMÈS ' + (op.signal?.state || 'WAITING');
    }

    function definitionList(rows) {
        return '<dl class="dispatch-definition">' + rows.map(([label,value]) =>
            '<div><dt>' + esc(label) + '</dt><dd>' + (value ?? '—') + '</dd></div>'
        ).join('') + '</dl>';
    }

    function renderOverview() {
        const d = state.detail, op = d.operation || {};
        const alerts = op.alerts || [];
        const checks = d.overview?.preflight_checks || [];
        return '<div class="dispatch-card-grid">' +
            '<article class="dispatch-card"><span>Phase</span><strong>' + esc(op.phase || op.status) + '</strong><small>' + esc(op.signal?.state || 'WAITING') + '</small></article>' +
            '<article class="dispatch-card"><span>Altitude</span><strong>' + number(op.live?.altitude, ' ft') + '</strong><small>GS ' + number(op.live?.gs, ' kt') + '</small></article>' +
            '<article class="dispatch-card"><span>Carburant transmis</span><strong>' + number(op.live?.fuel) + '</strong><small>unité simulateur</small></article>' +
            '<article class="dispatch-card"><span>Destination</span><strong>' + esc(op.flight?.arrival) + '</strong><small>ETA ' + time(op.live?.eta) + ' · ' + number(op.live?.remaining_nm, ' NM') + '</small></article>' +
        '</div>' +
        '<div class="dispatch-two-columns">' +
            '<section><h3>Alertes opérationnelles</h3>' +
                (alerts.length ? '<div class="dispatch-alert-list">' + alerts.map(a => '<div class="dispatch-alert ' + esc(a.level || 'warning') + '"><strong>' + esc(a.label) + '</strong></div>').join('') + '</div>' : '<p class="muted">Aucune alerte active.</p>') +
            '</section>' +
            '<section><h3>Préparation serveur</h3><div class="dispatch-check-list">' +
                checks.map(c => '<div class="' + (c.ready ? 'ready' : 'pending') + '"><span>' + (c.ready ? '✓' : '○') + '</span><strong>' + esc(c.label) + '</strong></div>').join('') +
            '</div></section>' +
        '</div>';
    }

    function renderAircraft() {
        const a = state.detail?.aircraft;
        if (!a) return '<div class="dispatch-empty-inline">Aucun appareil affecté à cette opération.</div>';
        const maintenance = a.maintenance;
        return definitionList([
            ['Immatriculation', '<strong>' + esc(a.registration) + '</strong>'],
            ['Type', esc(a.icao || a.name)],
            ['Sous-flotte', esc(a.subfleet)],
            ['Compagnie', esc(a.airline)],
            ['Position phpVMS', esc(a.airport)],
            ['État / statut', esc(a.state) + ' / ' + esc(a.status)],
            ['Fuel onboard enregistré', number(a.fuel_onboard)],
            ['Temps cellule enregistré', number(a.flight_time)],
            ['Maintenance', maintenance ? 'Données maintenance disponibles' : 'Aucune alerte maintenance chargée'],
        ]);
    }

    function renderOfp() {
        const o = state.detail?.ofp || {};
        if (!o.available) return '<div class="dispatch-empty-inline">Aucun OFP SimBrief n’est encore lié à cette opération.</div>';
        return definitionList([
            ['OFP ID', esc(o.id)],
            ['Référence', esc(o.reference)],
            ['Dernière mise à jour', dateTime(o.updated_at)],
            ['Appareil', esc(o.aircraft_id)],
            ['Fuel planifié', number(o.planned_fuel)],
        ]);
    }

    function renderRoute() {
        const r = state.detail?.route || {};
        return definitionList([
            ['Départ', '<strong>' + esc(r.departure) + '</strong> · ' + esc(r.departure_name)],
            ['Arrivée', '<strong>' + esc(r.arrival) + '</strong> · ' + esc(r.arrival_name)],
            ['Dégagement', esc(r.alternate)],
            ['Niveau', esc(r.level)],
            ['Route', '<code class="dispatch-route-code">' + esc(r.route || 'Non renseignée') + '</code>'],
            ['Distance restante', number(r.remaining_nm, ' NM')],
            ['ETA calculée', time(r.eta)],
        ]);
    }

    function renderWeather() {
        const w = state.detail?.weather || {};
        const messages = w.messages || [];
        return '<div class="dispatch-action-bar">' +
            '<button type="button" class="button secondary" data-quick="weather">Préparer météo</button>' +
            '<button type="button" class="button secondary" data-quick="runway">Préparer piste</button>' +
        '</div>' +
        '<p class="muted">' + esc(w.note) + '</p>' +
        '<div class="dispatch-message-list">' +
            (messages.length ? messages.map(renderMessage).join('') : '<div class="dispatch-empty-inline">Aucun message WEATHER sur cette opération.</div>') +
        '</div>';
    }

    function renderTrack() {
        const t = state.detail?.track || {};
        const latest = t.latest || {};
        return '<div class="dispatch-card-grid">' +
            '<article class="dispatch-card"><span>Échantillons</span><strong>' + esc(t.sample_count ?? 0) + '</strong><small>archive Hermès</small></article>' +
            '<article class="dispatch-card"><span>Position</span><strong>' + esc(latest.lat ?? '—') + ' / ' + esc(latest.lon ?? '—') + '</strong><small>' + dateTime(latest.recorded_at) + '</small></article>' +
            '<article class="dispatch-card"><span>Cap</span><strong>' + number(latest.heading, '°') + '</strong><small>VS ' + number(latest.vs, ' ft/min') + '</small></article>' +
            '<article class="dispatch-card"><span>Vitesse</span><strong>' + number(latest.gs, ' kt') + '</strong><small>IAS ' + number(latest.ias, ' kt') + '</small></article>' +
        '</div><p class="muted">La trace cartographique est affichée sur la carte OCC principale. ' + esc((t.points || []).length) + ' points représentatifs sont disponibles dans le flux détail.</p>';
    }

    function renderFdm() {
        const f = state.detail?.fdm || {};
        const quality = f.data_quality || {};
        const events = [...(f.safety?.events || []), ...(f.operations?.events || [])];
        return '<div class="dispatch-card-grid">' +
            '<article class="dispatch-card"><span>Qualité données</span><strong>' + esc(quality.samples ?? 0) + '</strong><small>échantillons</small></article>' +
            '<article class="dispatch-card"><span>Safety</span><strong>' + esc(f.safety?.status || '—') + '</strong><small>analyse factuelle</small></article>' +
            '<article class="dispatch-card"><span>Operations</span><strong>' + esc(f.operations?.status || '—') + '</strong><small>approche / exploitation</small></article>' +
        '</div>' +
        (events.length ? '<div class="dispatch-alert-list">' + events.map(e => '<div class="dispatch-alert ' + esc(e.level || 'info') + '"><strong>' + esc(e.label) + '</strong></div>').join('') + '</div>' : '<p class="muted">Aucun événement FDM évalué pour le moment.</p>');
    }

    function renderSop() {
        const s = state.detail?.sop || {};
        return '<div class="dispatch-empty-inline"><strong>' + esc(s.status) + '</strong><br>' + esc(s.message) + '</div>';
    }

    function renderMessage(message) {
        const incoming = message.direction === 'COCKPIT_TO_OPS';
        const needsAck = incoming && message.requires_ack && !message.acknowledged_at;
        return '<article class="dispatch-message ' + (incoming ? 'incoming' : 'outgoing') + '">' +
            '<header><strong>' + esc(message.sender_label) + '</strong><span>' + esc(message.category) + ' · ' + esc(message.priority) + ' · ' + time(message.created_at) + '</span></header>' +
            '<p>' + esc(message.body) + '</p>' +
            '<footer>' + badge(message.status || 'SENT', message.status === 'ACKNOWLEDGED' ? 'ok' : 'neutral') +
                (needsAck ? '<button type="button" class="button compact" data-ack="' + esc(message.id) + '">ACK OPS</button>' : '') +
            '</footer>' +
        '</article>';
    }

    function composeBox() {
        return '<form id="dispatch-compose" class="dispatch-compose">' +
            '<div class="dispatch-quick-actions">' +
                '<button type="button" class="button secondary compact" data-quick="fuel">Demander fuel remaining</button>' +
                '<button type="button" class="button secondary compact" data-quick="weather">Météo / METAR</button>' +
                '<button type="button" class="button secondary compact" data-quick="runway">Piste / exploitation</button>' +
            '</div>' +
            '<div class="dispatch-compose-meta">' +
                '<label>Catégorie<select name="category"><option>DISPATCH</option><option>WEATHER</option><option>OPS</option><option>SYSTEM</option></select></label>' +
                '<label>Priorité<select name="priority"><option value="NORMAL">ROUTINE</option><option value="HIGH">IMPORTANT</option><option value="URGENT">URGENT</option></select></label>' +
                '<label class="inline-check"><input type="checkbox" name="requires_ack" checked> ACK cockpit requis</label>' +
            '</div>' +
            '<label>Message<textarea name="body" rows="4" maxlength="2000" required placeholder="Message opérationnel au cockpit…"></textarea></label>' +
            '<div class="dispatch-compose-footer"><span class="muted" id="dispatch-compose-status"></span><button class="button" type="submit">Envoyer au cockpit</button></div>' +
        '</form>';
    }

    function renderMessages() {
        const messages = state.detail?.messages?.messages || [];
        return composeBox() + '<div class="dispatch-message-list">' +
            (messages.length ? messages.slice().reverse().map(renderMessage).join('') : '<div class="dispatch-empty-inline">Aucun échange Datalink.</div>') +
        '</div>';
    }

    function renderTimeline() {
        const timeline = state.detail?.timeline || [];
        return '<div class="dispatch-timeline">' +
            (timeline.length ? timeline.slice().reverse().map(event =>
                '<article><time>' + dateTime(event.at) + '</time><div><strong>' + esc(event.type) + '</strong><p>' + esc(event.label) + '</p></div></article>'
            ).join('') : '<div class="dispatch-empty-inline">Aucun événement chronologique.</div>') +
        '</div>';
    }

    function renderTab() {
        document.querySelectorAll('[data-dispatch-tab]').forEach(button => {
            button.classList.toggle('selected', button.dataset.dispatchTab === state.tab);
        });

        const renderers = {
            overview:renderOverview, aircraft:renderAircraft, ofp:renderOfp, route:renderRoute,
            weather:renderWeather, track:renderTrack, fdm:renderFdm, sop:renderSop,
            messages:renderMessages, timeline:renderTimeline
        };
        content.innerHTML = (renderers[state.tab] || renderOverview)();
        bindDetailActions();
    }

    function quickTemplate(kind) {
        const form = document.querySelector('#dispatch-compose');
        if (!form) {
            state.tab = 'messages';
            renderTab();
            return setTimeout(() => quickTemplate(kind), 0);
        }
        const body = form.elements.body;
        const category = form.elements.category;
        const priority = form.elements.priority;
        const ack = form.elements.requires_ack;
        if (kind === 'fuel') {
            category.value = 'DISPATCH'; priority.value = 'HIGH'; ack.checked = true;
            body.value = 'REQUEST FUEL REMAINING — Please report current fuel remaining.';
        } else if (kind === 'weather') {
            category.value = 'WEATHER'; priority.value = 'NORMAL'; ack.checked = false;
            body.value = 'WX UPDATE — ';
        } else if (kind === 'runway') {
            category.value = 'OPS'; priority.value = 'HIGH'; ack.checked = true;
            body.value = 'OPS UPDATE — Runway / operational information: ';
        }
        body.focus();
        body.setSelectionRange(body.value.length, body.value.length);
    }

    async function sendMessage(form) {
        const status = document.querySelector('#dispatch-compose-status');
        status.textContent = 'Envoi…';
        const body = {
            operation: state.selected,
            body: form.elements.body.value,
            category: form.elements.category.value,
            priority: form.elements.priority.value,
            requires_ack: form.elements.requires_ack.checked
        };
        const response = await fetch(datalinkSendUrl, {
            method:'POST',
            headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:JSON.stringify(body)
        });
        if (!response.ok) {
            status.textContent = 'Échec de l’envoi.';
            return;
        }
        status.textContent = 'Message transmis à Hermès.';
        form.elements.body.value = '';
        await refreshDetail(false);
        await refreshBoard();
    }

    async function acknowledge(messageId) {
        const response = await fetch(datalinkAckBase + '/' + encodeURIComponent(messageId) + '/ack', {
            method:'POST',
            headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:JSON.stringify({operation:state.selected})
        });
        if (!response.ok) return;
        await refreshDetail(false);
        await refreshBoard();
    }

    function bindDetailActions() {
        content.querySelectorAll('[data-quick]').forEach(button => {
            button.addEventListener('click', () => quickTemplate(button.dataset.quick));
        });
        content.querySelectorAll('[data-ack]').forEach(button => {
            button.addEventListener('click', () => acknowledge(button.dataset.ack));
        });
        const form = document.querySelector('#dispatch-compose');
        if (form) form.addEventListener('submit', event => {
            event.preventDefault();
            sendMessage(form);
        });
    }

    document.querySelectorAll('[data-dispatch-tab]').forEach(button => {
        button.addEventListener('click', () => {
            state.tab = button.dataset.dispatchTab;
            renderTab();
        });
    });
    document.querySelector('#dispatch-refresh').addEventListener('click', refreshBoard);
    filter.addEventListener('input', renderBoard);

    initMap();
    refreshBoard();
    state.timer = setInterval(refreshBoard, 5000);
})();
</script>
@endsection
