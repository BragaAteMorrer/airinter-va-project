const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../acars/wwwroot');
const source = fs.readFileSync(path.join(root, 'hermes-local-plan.js'), 'utf8');
const html = fs.readFileSync(path.join(root, 'index.html'), 'utf8');

// Minimal deterministic XML DOM for testing parser behavior in Node without
// adding a runtime dependency to the Windows ACARS / GitHub CI.
class Node {
  constructor(name, attributes = {}) {
    this.localName = name.split(':').at(-1);
    this.nodeName = name;
    this.attributes = attributes;
    this.children = [];
    this.content = '';
  }
  get textContent() {
    return this.content + this.children.map(child => child.textContent).join('');
  }
  getAttribute(key) { return this.attributes[key] || null; }
  getElementsByTagName(name) {
    const list = [];
    for (const child of this.children) {
      if (name === '*' || child.nodeName === name) list.push(child);
      list.push(...child.getElementsByTagName(name));
    }
    return list;
  }
}
class DOMParser {
  parseFromString(xml) {
    const document = new Node('#document');
    const stack = [document];
    for (const token of xml.match(/<[^>]+>|[^<]+/g) || []) {
      if (token.startsWith('<?') || token.startsWith('<!--')) continue;
      if (token.startsWith('</')) { stack.pop(); continue; }
      if (token.startsWith('<')) {
        const match = /^<([a-zA-Z0-9.:-]+)([^>]*)>/.exec(token);
        if (!match) throw new Error('Malformed fixture XML');
        const attrs = {};
        for (const pair of match[2].matchAll(/([a-zA-Z-]+)="([^"]*)"/g))
          attrs[pair[1]] = pair[2];
        const node = new Node(match[1], attrs);
        stack.at(-1).children.push(node);
        if (!token.endsWith('/>')) stack.push(node);
      } else stack.at(-1).content += token;
    }
    document.documentElement = document.children[0];
    return document;
  }
}
const window = {};
vm.runInNewContext(source, { window, DOMParser });
const { parse, verifyAirports } = window.HermesLocalPlan;
assert.ok(parse && verifyAirports);

const fms = [
  'I', '1100 Version', 'CYCLE 2510', 'ADEP LFPO', 'ADES EIDW', 'NUMENR 5',
  '1 LFPO ADEP 291.0 48.7233 2.3794',
  '11 LGL DRCT 20000.0 48.7890 2.0000',
  '11 SOMAX UN859 30000.0 49.1000 0.0000',
  '11 DUB UN859 20000.0 53.4213 -6.2700',
  '1 EIDW ADES 250.0 53.4213 -6.2700'
].join('\n');
const parsedFms = parse(fms, 'LFPOEIDW.fms');
assert.equal(parsedFms.format, 'X-Plane FMS 1100');
assert.equal(parsedFms.route, 'LGL UN859 DUB');
assert.equal(parsedFms.departure, 'LFPO');
assert.equal(parsedFms.arrival, 'EIDW');
assert.equal(parsedFms.waypointCount, 5);
verifyAirports(parsedFms, { departure: 'LFPO', arrival: 'EIDW' });
assert.throws(() => verifyAirports(parsedFms, { departure: 'LFPO', arrival: 'EGLL' }), /Import refusé/);
assert.throws(() => parse(fms.replace('NUMENR 5', 'NUMENR 6'), 'x.fms'), /NUMENR/);
assert.throws(() => parse(fms.replace('11 SOMAX', '28 SOMAX'), 'x.fms'), /coordonnées/);
assert.throws(() => parse(fms.replace('48.7890', '481.7890'), 'x.fms'), /Coordonnées/);

const legacy = ['I', '3 version', '1', '3',
  '1 LFPO 290.0 48.72 2.38', '11 LGL 20000.0 48.78 2.00', '1 EIDW 250.0 53.42 -6.27'].join('\n');
assert.equal(parse(legacy, 'old.fms').route, 'LGL');
assert.equal(parse(legacy, 'old.fms').format, 'X-Plane FMS 3');

const pln = '<?xml version="1.0"?><SimBase.Document><FlightPlan.FlightPlan>'
  + '<DepartureID>LFPO</DepartureID><DestinationID>EIDW</DestinationID><CruisingAlt>34000</CruisingAlt>'
  + '<ATCWaypoint id="LFPO"><ICAO><ICAOIdent>LFPO</ICAOIdent></ICAO></ATCWaypoint>'
  + '<ATCWaypoint id="LGL"><ICAO><ICAOIdent>LGL</ICAOIdent></ICAO></ATCWaypoint>'
  + '<ATCWaypoint id="EIDW"><ICAO><ICAOIdent>EIDW</ICAOIdent></ICAO></ATCWaypoint>'
  + '</FlightPlan.FlightPlan></SimBase.Document>';
assert.equal(parse(pln, 'plan.pln').route, 'LGL');
assert.equal(parse(pln, 'plan.pln').level, 340);

const garmin = '<flight-plan><route>'
  + '<route-point><waypoint-identifier>LFPO</waypoint-identifier></route-point>'
  + '<route-point><waypoint-identifier>LGL</waypoint-identifier></route-point>'
  + '<route-point><waypoint-identifier>EIDW</waypoint-identifier></route-point>'
  + '</route></flight-plan>';
assert.equal(parse(garmin, 'garmin.fpl').route, 'LGL');
assert.equal(parse(garmin, 'garmin.fpl').format, 'Garmin FPL');

const simbrief = '<OFP><origin><icao_code>LFPO</icao_code></origin>'
  + '<destination><icao_code>EIDW</icao_code></destination>'
  + '<general><route>LGL UN859 DUB</route></general></OFP>';
assert.equal(parse(simbrief, 'simbrief.xml').route, 'LGL UN859 DUB');
assert.match(parse(simbrief, 'simbrief.xml').format, /route uniquement/);
assert.throws(() => parse('<!DOCTYPE foo><OFP/>', 'xml.xml'), /entités externes/);
assert.throws(() => parse('garbage', 'plan.txt'), /Extension/);

assert.match(html, /accept=".fms,.pln,.fpl,.xml"/);
assert.ok(html.indexOf('hermes-local-plan.js') < html.indexOf('hermes-local-plan-ui.js'));
assert.ok(html.indexOf('hermes-local-plan-ui.js') > html.indexOf('/app.js'));
assert.equal((html.match(/id="planBox"/g) || []).length, 1);
console.log('Hermès local FPL: X-Plane, MSFS, Garmin, SimBrief and validation tests OK');
