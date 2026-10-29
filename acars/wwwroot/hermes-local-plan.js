// Read local navigation plans without treating them as authoritative SimBrief OFPs.
// No local file is uploaded to Prométhée; only its reviewed route can enter the draft.
(() => {
  'use strict';
  const MAX_TEXT_LENGTH = 256 * 1024;
  const MAX_WAYPOINTS = 300;
  const MAX_ROUTE = 2000;
  const allowedId = value => {
    const id = String(value ?? '').trim().toUpperCase();
    return /^[A-Z0-9]{2,12}$/.test(id) ? id : null;
  };
  const airport = value => {
    const id = allowedId(value);
    return id && id.length <= 8 ? id : null;
  };
  const decimal = value => {
    const raw = String(value || '');
    return /^[-+]?\d+(?:\.\d+)?$/.test(raw) ? Number(raw) : NaN;
  };
  const safeRoute = value => {
    const route = String(value || '').trim().toUpperCase().replace(/\s+/g, ' ');
    if (!route || route.length > MAX_ROUTE || !/^[A-Z0-9 /.+-]+$/.test(route))
      throw new Error('Route absente, trop longue ou comportant des caractères non reconnus.');
    return route;
  };

  function buildRoute(waypoints, departure, arrival) {
    const inside = waypoints.filter((point, index) => {
      const ident = point.id;
      return ident !== departure && ident !== arrival
        && point.via !== 'ADEP' && point.via !== 'ADES';
    });
    if (inside.length > MAX_WAYPOINTS)
      throw new Error('Ce plan contient trop de points de navigation.');
    if (!inside.length) return 'DCT';
    const tokens = [inside[0].id];
    for (let i = 1; i < inside.length;) {
      const via = inside[i].via;
      if (via && !['DCT', 'DRCT'].includes(via) && allowedId(via)) {
        let end = i;
        while (end + 1 < inside.length && inside[end + 1].via === via) end++;
        tokens.push(via, inside[end].id);
        i = end + 1;
      } else {
        tokens.push('DCT', inside[i].id);
        i++;
      }
    }
    return safeRoute(tokens.join(' '));
  }

  function parseFms(raw) {
    const lines = raw.replace(/^\uFEFF/, '').split(/\r?\n/)
      .map(line => line.trim()).filter(Boolean);
    if (!/^[IA]$/i.test(lines[0] || ''))
      throw new Error('En-tête X-Plane manquant (I ou A).');
    const version = /^(\d+)\s+version$/i.exec(lines[1] || '')?.[1];
    if (!['1100', '3'].includes(version))
      throw new Error('Version FMS inconnue : seuls X-Plane v11/v12 (1100) et l’ancien format 3 sont acceptés.');

    let departure = null, arrival = null, waypointLines = [];
    if (version === '1100') {
      if (!/^CYCLE\s+\d{4}$/i.test(lines[2] || ''))
        throw new Error('Cycle AIRAC X-Plane absent ou invalide.');
      const header = lines.slice(3);
      departure = airport(header.find(line => /^ADEP\s/i.test(line))?.split(/\s+/)[1]);
      arrival = airport(header.find(line => /^ADES\s/i.test(line))?.split(/\s+/)[1]);
      const offset = header.findIndex(line => /^NUMENR\s/i.test(line));
      if (!departure || !arrival || offset < 0)
        throw new Error('Plan X-Plane sans ADEP, ADES ou NUMENR valide.');
      const count = Number(header[offset].split(/\s+/)[1]);
      if (!Number.isInteger(count) || count < 2 || count > MAX_WAYPOINTS + 2)
        throw new Error('Nombre de points NUMENR invalide.');
      waypointLines = header.slice(offset + 1);
      if (waypointLines.length !== count)
        throw new Error('Le nombre de points X-Plane ne correspond pas à NUMENR.');
    } else {
      // Legacy v3 has no airport headers; infer departure and arrival from the
      // first/last airport waypoints, while keeping all coordinates read-only.
      const count = Number(lines[3]);
      if (!Number.isInteger(count) || count < 2 || count > MAX_WAYPOINTS + 2)
        throw new Error('Nombre de points X-Plane v3 invalide.');
      waypointLines = lines.slice(4);
      if (waypointLines.length !== count)
        throw new Error('Nombre de points X-Plane v3 incohérent.');
    }

    const points = waypointLines.map(line => {
      const parts = line.split(/\s+/);
      const required = version === '1100' ? 6 : 5;
      if (parts.length !== required || !/^\d+$/.test(parts[0]))
        throw new Error('Ligne de navigation FMS mal formée.');
      const type = Number(parts[0]);
      const id = allowedId(parts[1]);
      const via = version === '1100' ? parts[2].toUpperCase() : 'DRCT';
      const altitude = decimal(parts[version === '1100' ? 3 : 2]);
      const latitude = decimal(parts[version === '1100' ? 4 : 3]);
      const longitude = decimal(parts[version === '1100' ? 5 : 4]);
      if (!id || !Number.isFinite(altitude)
          || !Number.isFinite(latitude) || !Number.isFinite(longitude)
          || Math.abs(latitude) > 90 || Math.abs(longitude) > 180)
        throw new Error('Coordonnées ou identifiant de point FMS invalides.');
      if (type === 28)
        throw new Error('Le fichier contient un point utilisateur par coordonnées. Importez-le via SimBrief pour éviter de perdre ce segment.');
      if (version === '1100' && !['ADEP', 'ADES', 'DRCT', 'DCT'].includes(via) && !allowedId(via))
        throw new Error('Voie aérienne X-Plane non reconnue.');
      return { type, id, via, latitude, longitude, altitude };
    });
    if (version === '3') {
      if (points[0].type !== 1 || points.at(-1).type !== 1)
        throw new Error('Les extrémités du plan FMS v3 doivent être des aéroports.');
      departure = airport(points[0].id);
      arrival = airport(points.at(-1).id);
    }
    if (!departure || !arrival) throw new Error('Aéroports de départ ou d’arrivée invalides.');
    return {
      format: 'X-Plane FMS ' + version, departure, arrival,
      route: buildRoute(points, departure, arrival),
      waypointCount: points.length,
      notes: 'SID/STAR et contraintes d’altitude doivent être vérifiées dans le briefing.'
    };
  }

  function descendants(node, name) {
    return Array.from(node?.getElementsByTagName('*') || [])
      .filter(element => String(element.localName || element.nodeName).toLowerCase() === name.toLowerCase());
  }
  const first = (node, name) => descendants(node, name)[0];
  const value = (node, name) => first(node, name)?.textContent?.trim() || '';

  function parseXml(raw) {
    if (/<\s*!(?:DOCTYPE|ENTITY)\b/i.test(raw))
      throw new Error('Les fichiers XML contenant DTD ou entités externes sont refusés.');
    if (typeof DOMParser === 'undefined')
      throw new Error('Le lecteur XML n’est pas disponible dans cette interface.');
    const doc = new DOMParser().parseFromString(raw, 'application/xml');
    if (doc.getElementsByTagName('parsererror').length || !doc.documentElement)
      throw new Error('XML invalide ou incomplet.');

    const msfs = descendants(doc, 'FlightPlan.FlightPlan')[0];
    if (msfs) {
      const departure = airport(value(msfs, 'DepartureID'));
      const arrival = airport(value(msfs, 'DestinationID'));
      if (!departure || !arrival)
        throw new Error('Le PLN MSFS ne renseigne pas le départ et la destination.');
      const rawPoints = descendants(msfs, 'ATCWaypoint');
      if (rawPoints.length > MAX_WAYPOINTS + 2)
        throw new Error('Trop de points dans le PLN.');
      const points = rawPoints.map(point => {
        const kind = value(point, 'ATCWaypointType').toLowerCase();
        if (kind === 'user')
          throw new Error('Ce PLN contient des coordonnées personnalisées non traduisibles en route ICAO.');
        const id = allowedId(value(point, 'ICAOIdent') || point.getAttribute('id'));
        if (!id) throw new Error('Un point PLN n’a pas d’identifiant utilisable.');
        return { id, via: value(point, 'ATCAirway').toUpperCase() || 'DRCT' };
      });
      const altitude = decimal(value(msfs, 'CruisingAlt'));
      return {
        format: 'MSFS PLN', departure, arrival,
        route: buildRoute(points, departure, arrival),
        level: Number.isFinite(altitude) && altitude >= 1000 && altitude <= 60000
          ? Math.round(altitude / 100) : null,
        waypointCount: points.length,
        notes: 'Vérifiez les procédures et les voies aériennes après import.'
      };
    }

    const garmin = descendants(doc, 'flight-plan')[0] || (
      String(doc.documentElement.localName || '').toLowerCase() === 'flight-plan'
        ? doc.documentElement : null);
    if (garmin && descendants(garmin, 'route-point').length) {
      const rawPoints = descendants(garmin, 'route-point');
      if (rawPoints.length < 2 || rawPoints.length > MAX_WAYPOINTS + 2)
        throw new Error('Nombre de points Garmin FPL invalide.');
      const points = rawPoints.map(point => {
        const id = allowedId(value(point, 'waypoint-identifier'));
        if (!id) throw new Error('Point Garmin FPL non identifié.');
        return { id, via: 'DRCT' };
      });
      const departure = airport(points[0].id);
      const arrival = airport(points.at(-1).id);
      if (!departure || !arrival)
        throw new Error('Le fichier Garmin FPL n’identifie pas les aéroports.');
      return {
        format: 'Garmin FPL', departure, arrival,
        route: buildRoute(points, departure, arrival),
        waypointCount: points.length,
        notes: 'Routes Garmin importées en segments directs ; validez les points sur la carte.'
      };
    }

    const ofp = descendants(doc, 'OFP')[0] || (
      String(doc.documentElement.localName || '').toUpperCase() === 'OFP'
        ? doc.documentElement : null);
    if (ofp) {
      const origin = first(ofp, 'origin'), destination = first(ofp, 'destination');
      const departure = airport(value(origin, 'icao_code'));
      const arrival = airport(value(destination, 'icao_code'));
      if (!departure || !arrival)
        throw new Error('Le fichier SimBrief XML ne contient pas les deux aéroports.');
      const general = first(ofp, 'general');
      return {
        format: 'SimBrief XML (route uniquement)', departure, arrival,
        route: safeRoute(value(general, 'route')),
        waypointCount: null,
        notes: 'Ce fichier local n’est pas lié au PIREP : récupérez l’OFP par le compte ou l’API SimBrief.'
      };
    }
    throw new Error('Format XML non reconnu (MSFS PLN, Garmin FPL ou SimBrief OFP uniquement).');
  }

  function parse(raw, filename) {
    if (typeof raw !== 'string' || !raw.trim() || raw.length > MAX_TEXT_LENGTH)
      throw new Error('Fichier vide ou trop volumineux (256 Ko maximum).');
    const extension = /\.([^.]+)$/.exec(String(filename || ''))?.[1]?.toLowerCase();
    if (extension === 'fms') return parseFms(raw);
    if (['pln', 'fpl', 'xml'].includes(extension)) return parseXml(raw);
    throw new Error('Extension de plan non prise en charge : .fms, .pln, .fpl ou .xml.');
  }

  function verifyAirports(plan, expected) {
    const departure = airport(expected?.departure);
    const arrival = airport(expected?.arrival);
    if (!departure || !arrival)
      throw new Error('Sélectionnez d’abord une opération avec un départ et une arrivée.');
    if (plan.departure !== departure || plan.arrival !== arrival)
      throw new Error('Ce plan concerne ' + plan.departure + ' → ' + plan.arrival
        + ', mais le vol réservé est ' + departure + ' → ' + arrival + '. Import refusé.');
    return plan;
  }

  window.HermesLocalPlan = { parse, verifyAirports };
})();
