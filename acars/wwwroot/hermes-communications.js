// Hermès Datalink / Air Inter Network extracted from app.js.
// Classic script on purpose: existing inline handlers keep the same global names.

function currentDatalinkOperation() {
  const activeFlight = lastStatus?.flight || lastStatus?.Flight;
  return selectedOperation?.operation_id || selectedOperation?.operationId || selectedOperation?.id
    || activeFlight?.operationId || activeFlight?.OperationId || null;
}

function datalinkRead(object, camel, pascal = camel) {
  return object?.[camel] ?? object?.[pascal] ?? null;
}

function renderDatalink(snapshot) {
  lastDatalinkSnapshot = snapshot || null;
  const operationId = snapshot ? datalinkRead(snapshot, 'operationId', 'OperationId') : currentDatalinkOperation();
  const messages = snapshot ? (datalinkRead(snapshot, 'messages', 'Messages') || []) : [];
  const syncState = String(snapshot ? datalinkRead(snapshot, 'syncState', 'SyncState') || 'LOCAL' : 'STANDBY').toUpperCase();
  const pendingOutbound = Number(snapshot ? datalinkRead(snapshot, 'pendingOutbound', 'PendingOutbound') || 0 : 0);
  const pendingReads = Number(snapshot ? datalinkRead(snapshot, 'pendingReads', 'PendingReads') || 0 : 0);
  const pendingAcks = Number(snapshot ? datalinkRead(snapshot, 'pendingAcks', 'PendingAcks') || 0 : 0);
  const unreadCount = Number(snapshot ? datalinkRead(snapshot, 'unreadCount', 'UnreadCount') || 0 : 0);
  const requiredAcks = Number(snapshot ? datalinkRead(snapshot, 'pendingRequiredAcks', 'PendingRequiredAcks') || 0 : 0);
  const lastSync = snapshot ? datalinkRead(snapshot, 'lastSuccessfulSyncAt', 'LastSuccessfulSyncAt') : null;
  const error = snapshot ? datalinkRead(snapshot, 'error', 'Error') : null;

  setText($('#datalinkOperation'), operationId || '—');
  setText($('#datalinkLastSync'), lastSync ? new Date(lastSync).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined }) : '—');
  setText($('#datalinkPending'), String(pendingOutbound + pendingReads + pendingAcks));
  const state = $('#datalinkState');
  if (state) {
    state.textContent = syncState;
    state.classList.toggle('ready', syncState === 'SYNCED');
  }
  const badge = $('#datalinkBadge');
  if (badge) {
    const attention = Math.max(unreadCount, requiredAcks);
    badge.hidden = attention <= 0;
    badge.textContent = String(attention);
  }

  const list = $('#datalinkMessages');
  list.replaceChildren();
  if (!operationId) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Sélectionnez une opération Air Inter.';
    list.append(empty);
  } else if (!messages.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = syncState === 'OFFLINE' ? 'Aucun message local. Prométhée est hors ligne.' : 'Aucun message datalink pour cette opération.';
    list.append(empty);
  } else {
    messages.forEach(message => {
      const id = datalinkRead(message, 'id', 'Id');
      const direction = String(datalinkRead(message, 'direction', 'Direction') || '');
      const priority = String(datalinkRead(message, 'priority', 'Priority') || 'ROUTINE');
      const category = String(datalinkRead(message, 'category', 'Category') || 'OPS');
      const sender = datalinkRead(message, 'senderLabel', 'SenderLabel') || (direction === 'OPS_TO_COCKPIT' ? 'AIR INTER OPS' : 'COCKPIT');
      const body = datalinkRead(message, 'body', 'Body') || '';
      const createdAt = datalinkRead(message, 'createdAt', 'CreatedAt');
      const requiresAck = Boolean(datalinkRead(message, 'requiresAck', 'RequiresAck'));
      const readAt = datalinkRead(message, 'readAt', 'ReadAt');
      const acknowledgedAt = datalinkRead(message, 'acknowledgedAt', 'AcknowledgedAt');
      const status = String(datalinkRead(message, 'status', 'Status') || 'SENT');
      const localPending = Boolean(datalinkRead(message, 'localPending', 'LocalPending'));

      const item = document.createElement('article');
      item.className = 'datalink-message ' + (direction === 'OPS_TO_COCKPIT' ? 'incoming' : 'outgoing') + ' priority-' + priority.toLowerCase();
      const header = document.createElement('header');
      const meta = document.createElement('div');
      const source = document.createElement('strong');
      source.textContent = sender;
      const tags = document.createElement('span');
      const time = createdAt ? new Date(createdAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined }) : '—';
      tags.textContent = category + ' · ' + priority + ' · ' + time;
      meta.append(source, tags);
      const statusNode = document.createElement('em');
      statusNode.textContent = localPending ? 'QUEUED' : (acknowledgedAt ? 'ACK' : status);
      header.append(meta, statusNode);
      const text = document.createElement('p');
      text.textContent = body;
      const actions = document.createElement('div');
      actions.className = 'datalink-message-actions';

      if (direction === 'OPS_TO_COCKPIT' && !readAt && !acknowledgedAt) {
        const read = document.createElement('button');
        read.type = 'button';
        read.textContent = status === 'READ_QUEUED' ? 'Lecture en file' : 'Marquer lu';
        read.disabled = status === 'READ_QUEUED';
        read.onclick = () => readDatalink(id);
        actions.append(read);
      }
      if (direction === 'OPS_TO_COCKPIT' && requiresAck && !acknowledgedAt) {
        const ack = document.createElement('button');
        ack.type = 'button';
        ack.textContent = status === 'ACK_QUEUED' ? 'ACK en file' : 'ACK';
        ack.disabled = status === 'ACK_QUEUED';
        ack.onclick = () => acknowledgeDatalink(id);
        actions.append(ack);
      }
      if (direction === 'OPS_TO_COCKPIT' && !localPending) {
        const reply = document.createElement('button');
        reply.type = 'button';
        reply.textContent = 'Répondre';
        reply.onclick = () => prepareDatalinkReply(id, sender);
        actions.append(reply);
      }

      item.append(header, text);
      if (actions.childElementCount) item.append(actions);
      list.append(item);
    });
  }

  const form = $('#datalinkForm');
  if (form) form.querySelector('button[type="submit"]').disabled = !operationId;
  showMessage('#datalinkMessage', error || '', Boolean(error && syncState !== 'OFFLINE'));
}

function refreshDatalink() {
  const operationId = currentDatalinkOperation();
  if (!connected || !operationId || datalinkRefreshing) {
    if (!operationId) renderDatalink(null);
    return;
  }
  datalinkRefreshing = true;
  try {
    const snapshot = await call('/api/datalink?operation=' + encodeURIComponent(operationId));
    renderDatalink(snapshot);
  } catch (error) {
    showMessage('#datalinkMessage', friendlyError(error), true);
  } finally {
    datalinkRefreshing = false;
  }
}

function readDatalink(messageId) {
  const operationId = currentDatalinkOperation();
  if (!operationId) return;
  try {
    const snapshot = await call('/api/datalink/read?operation=' + encodeURIComponent(operationId), { message_id: messageId });
    renderDatalink(snapshot);
  } catch (error) {
    showMessage('#datalinkMessage', friendlyError(error), true);
  }
}

function acknowledgeDatalink(messageId) {
  const operationId = currentDatalinkOperation();
  if (!operationId) return;
  try {
    const snapshot = await call('/api/datalink/ack?operation=' + encodeURIComponent(operationId), { message_id: messageId });
    renderDatalink(snapshot);
  } catch (error) {
    showMessage('#datalinkMessage', friendlyError(error), true);
  }
}

function prepareDatalinkReply(messageId, sender) {
  const form = $('#datalinkForm');
  form.elements.reply_to.value = messageId || '';
  $('#datalinkCancelReplyBtn').hidden = false;
  form.elements.body.placeholder = 'Réponse à ' + (sender || 'OPS') + '…';
  form.elements.body.focus();
}

function networkRead(object, snake, camel = snake) {
  return object?.[snake] ?? object?.[camel] ?? null;
}

function renderNetwork(payload) {
  const data = unwrap(payload) || {};
  const crews = Array.isArray(data.crews) ? data.crews : [];
  const operationId = currentDatalinkOperation();
  const generatedAt = data.generated_at || data.generatedAt;
  const count = Number(data.online_count ?? data.onlineCount ?? crews.length);

  setText($('#networkCount'), String(count));
  setText($('#networkOperation'), operationId || '—');
  setText($('#networkUpdated'), generatedAt
    ? new Date(generatedAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined })
    : '—');

  const state = $('#networkState');
  if (state) {
    state.textContent = count > 0 ? 'ONLINE' : 'STANDBY';
    state.classList.toggle('ready', count > 0);
  }

  const badge = $('#networkBadge');
  if (badge) {
    badge.hidden = count <= 0;
    badge.textContent = String(count);
  }

  const list = $('#networkCrews');
  if (!list) return;
  list.replaceChildren();

  if (!crews.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Aucun équipage Hermès connecté actuellement.';
    list.append(empty);
    return;
  }

  crews.forEach(crew => {
    const pilot = crew.pilot || {};
    const flight = crew.flight || {};
    const aircraft = crew.aircraft || {};
    const item = document.createElement('article');
    item.className = 'network-crew';

    const heading = document.createElement('div');
    heading.className = 'network-crew-heading';
    const title = document.createElement('strong');
    title.textContent = [pilot.ident || 'PILOT', flight.ident].filter(Boolean).join(' · ');
    const route = document.createElement('span');
    route.textContent = [flight.departure, flight.arrival].filter(Boolean).join(' → ') || 'Opération Air Inter';
    heading.append(title, route);

    const details = document.createElement('p');
    const phase = crew.phase || 'STANDBY';
    const simulator = String(crew.simulator || 'unknown').toUpperCase();
    const plane = [aircraft.registration, aircraft.icao].filter(Boolean).join(' · ') || 'Appareil non affecté';
    const version = crew.hermes_version || crew.hermesVersion || 'version inconnue';
    const age = Number(crew.age_seconds ?? crew.ageSeconds ?? 0);
    details.textContent = `${plane} · ${phase} · ${simulator} · Hermès ${version} · signal ${age}s`;

    item.append(heading, details);
    list.append(item);
  });
}

function refreshNetwork() {
  if (!connected || networkRefreshing) return;
  networkRefreshing = true;
  try {
    const operationId = currentDatalinkOperation();
    const path = '/api/network' + (operationId ? '?operation=' + encodeURIComponent(operationId) : '');
    const network = await call(path);
    renderNetwork(network);
    showMessage('#networkMessage', '');
  } catch (error) {
    showMessage('#networkMessage', friendlyError(error), true);
  } finally {
    networkRefreshing = false;
  }
}
