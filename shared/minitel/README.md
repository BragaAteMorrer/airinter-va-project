# 3615 AIRINTER — Minitel Runtime (M0 + M1 + M2 + M3 + M4 + M5 + M6)

This directory contains the shared, framework-free foundation for the Air Inter Videotex experience used by Prométhée and Hermès.

M0 defines the framework-free terminal contract. M1 adds the shared 3615 AIRINTER shell, boot sequence, system pages and safety escape. Neither lot duplicates Prométhée/Hermès business logic.

## Invariants

- Logical display: 40 columns × 25 rows.
- Row 0 is reserved for the service/status line.
- Navigation is keyboard-first and must remain usable without a mouse.
- Page transitions are rendered in row-major order: left to right, then top to bottom.
- The runtime never stores phpVMS, SimBrief, PIREP, ACARS or authentication rules.
- Minitel mode is desktop-only. Mobile/coarse-pointer environments must receive a modern escape/fallback surface outside the terminal.
- prefers-reduced-motion bypasses progressive transmission.

## Historical command mapping

| Minitel action | PC key |
| --- | --- |
| ENVOI | Enter |
| CORRECTION | Backspace |
| ANNULATION | Escape |
| SOMMAIRE | Home |
| GUIDE | F1 |
| RÉPÉTITION | F2 |
| RETOUR | PageUp / ArrowUp |
| SUITE | PageDown / ArrowDown |
| CONNEXION/FIN | F10 |

Shift+F2 is an Air Inter extension: it requests a data refresh before a RÉPÉTITION replay. Plain F2 only replays the current screen buffer.

## Files

- runtime.js: 40×25 screen buffer, cells, input buffer, page/session state machine, keyboard mapping, transmission planning and desktop capability guard.
- renderer.js: dependency-free DOM renderer (1,000 cells) and keyboard controller.
- minitel-runtime.css: neutral Videotex rendering layer.
- runtime.test.cjs: Node smoke/contract tests.
- shell.js: 3615 AIRINTER shell, service row, boot sequence, GUIDE/FIN system pages, desktop/mobile safety layer and emergency escape.
- minitel-shell.css: CRT-style outer shell and modern fallback surface.
- shell.test.cjs: M1 safety/system-command tests.
- demo.html: standalone M1 manual acceptance demo.

## Running M0 tests

From the repository root:

    node --check shared/minitel/runtime.js
    node --check shared/minitel/renderer.js
    node --check shared/minitel/shell.js
    node shared/minitel/runtime.test.cjs
    node shared/minitel/shell.test.cjs

These commands are also executed by the repository Quality workflow.

## Acceptance criteria for M0

1. The logical screen cannot deviate from 40×25.
2. Text clips or wraps deterministically at column 40.
3. Typed input, CORRECTION and ANNULATION update the screen state.
4. Numeric menus can be completed without a mouse.
5. SOMMAIRE always returns to the configured home page.
6. RETOUR uses the navigation history.
7. RÉPÉTITION replays the current buffer without a business/data request.
8. Shift+F2 can be distinguished from plain RÉPÉTITION for future live refresh.
9. Progressive rendering follows left-to-right, top-to-bottom order.
10. Mobile/coarse-pointer environments are rejected by the capability guard.
11. All runtime tests pass in CI.

## M1 acceptance criteria

1. A real 3615 AIRINTER shell exists outside the 40×25 runtime surface.
2. Boot frames display VIDEOTEX, connection establishment and the selected product identity.
3. Row 0 always fits exactly 40 characters and exposes service/session state.
4. F1 opens the shared GUIDE page regardless of the current business page.
5. F10 opens a CONNEXION/FIN page instead of immediately destroying the session.
6. Ctrl+Alt+M is an emergency exit independent from terminal navigation.
7. A visible external exit button remains outside the renderer and is forced visible on runtime errors.
8. Mobile/coarse-pointer devices receive a modern touch-safe fallback rather than the terminal.
9. The shell never mutates the saved Minitel preference itself; Prométhée/Hermès decide how to persist device/session fallback.
10. Shell/runtime tests run in repository CI.

## M2 — Prométhée consultation

M2 mounts the shared runtime in Prométhée and adds an authenticated, read-only projection for:

- departures / movements;
- flight search and paginated schedule;
- routes;
- fleet;
- pilots;
- calendar;
- current pilot profile.

The browser client lives in `prometheus/public/promethee-assets/promethee-minitel.js`.
The Laravel projection lives in `modules/Promethee/Http/MinitelController.php`.
All M2 routes are GET-only; reservations, SimBrief, PIREP filing and other mutations remain out of scope until M3.

Public runtime copies under `prometheus/public/promethee-assets/minitel/` are checked against `shared/minitel/` by `tools/check_minitel_assets.cjs` so Prométhée cannot silently drift from the shared contract.

Mobile fallback is session-only: it temporarily presents the modern UI without deleting the saved desktop Minitel preference. An explicit desktop exit persists the modern era.

M3 can now add operational actions on top of this consultation surface without duplicating phpVMS business rules.


## M3 — Prométhée operations

M3 makes the terminal operational while preserving the existing Prométhée/phpVMS business rules.

The Minitel web-session facade delegates to `OperationsV1Controller` and
`AcarsSimBriefController`; it does not implement an independent reservation,
aircraft, dispatch, SimBrief or PIREP ruleset.

Supported keyboard-driven flows:

- qualified reservable-flight search;
- reservation through the existing `BidService` flow;
- current operation list and operation detail;
- eligible-aircraft listing and aircraft selection;
- operational briefing;
- dispatch/readiness checks;
- SimBrief account redirect and Pilot ID import;
- SimBrief company-key generation session and generated OFP import;
- PIREP prefile through the existing operation facade.

The ordinary M2 flight catalogue remains read-only. The M3 reservation search
uses `OperationsV1Controller::searchFlights()` so pilot/subfleet qualification
cannot be bypassed by selecting a catalogue entry.

All mutations use the authenticated web session, explicit HTTP verbs and CSRF.
No destructive action is triggered by rendering a screen.


## M4 — Hermès preparation

M4 enables the same shared 40×25 runtime inside the Hermès desktop/WebView UI.

Hermès now exposes `minitel` as an approved era. The terminal can be entered
from the display selector and is restored on the next launch. The external shell
escape remains independent from the ACARS workflow and returns Hermès to the
modern era.

The M4 preparation flow is keyboard-only and uses the existing Hermès bridge:

- secure pilot login through the existing local `/api/login` command;
- qualified operation list and reservable-flight search;
- reservation through Prométhée Operations V1;
- operation selection and refresh;
- aircraft eligibility and assignment;
- SimBrief readiness, account redirect, Pilot ID import, company API session and import;
- PIREP prefile;
- dispatch/readiness display;
- simulator/preflight status;
- transition to local ACARS recording only when both server readiness and local
  simulator safety checks are satisfied.

No operational rule is duplicated in the Minitel client. Remote mutations use
the same `/api/v1/operations/*` surface as the modern Hermès UI; local tracking
uses the same `/api/start` command.

M5 will extend the terminal after start with the full in-flight workspace:
live telemetry, phase management, Datalink, journal, network and Flight Review.


## M5 — Hermès in-flight operations

M5 extends `3615 HERMES` after ACARS start and keeps the terminal on the same
local recorder, telemetry worker, Datalink store and presence services as the
modern UI.

Supported in-flight surfaces:

- live phase, altitude, IAS/GS, vertical speed, heading, fuel, distance,
  airborne time and synchronization queue;
- pause and resume of the existing local FlightRecorder;
- forced telemetry/SOP synchronization;
- Datalink local-first mailbox with periodic refresh;
- Datalink READ and ACK receipts;
- keyboard-only message/reply composition (160-character terminal surface,
  while the backend retains its larger protocol limit);
- operational journal/timeline pagination;
- Air Inter Network crew presence and heartbeat-backed refresh;
- Flight Review summary;
- Flight Review FDM observations and company-rule issues;
- final PIREP filing after the recorder reaches IN;
- Recovery Center entry and recovery resume after an interrupted Hermès session.

The Minitel does not calculate flight phases or FDM scores. Phase transitions,
flight metrics and observations come directly from `FlightRecorder`,
`FlightTrackingEngine` and `FlightDataMonitor`.

Datalink remains local-first: outgoing messages, READ receipts and ACKs are
queued by `HermesDatalink` before network I/O, exactly as in the graphical UI.
Air Inter Network uses `HermesPresence`; no stale presence is replayed.

M5 deliberately keeps recovery abandonment in the graphical interface because
it archives and clears local flight state. Recovery resume itself is available
from the terminal.


## M6 — Videotex fidelity

M6 hardens the visual and interaction fidelity of the shared terminal without
changing any Prométhée or Hermès business rule.

Shared fidelity features:

- real 2×3 alphamosaic cells with a six-bit mask;
- joined and separated mosaic rendering;
- a semi-graphic Air Inter service mark in the boot sequence;
- the complete logical Videotex palette including blue;
- color display or monochrome/luminance rendering;
- a restrained CRT scanline/vignette layer;
- a warmer physical Minitel-inspired terminal chassis instead of a generic dark terminal;
- three transmission profiles: authentic (120 characters/s), fast and instant;
- fresh-screen rendering models a clear-screen operation and transmits only useful cells instead of 1,000 blank cells;
- terminal settings are available from GUIDE → 0 and CONNEXION/FIN → 3;
- the shell owns cursor placement on system pages independently of Prométhée/Hermès business clients;
- logical input buffers may exceed 40 characters while the physical display remains strictly 40 columns;
- Prométhée and Hermès persist the same speed/display preferences using shared local keys.

The renderer keeps logical Videotex colors in the buffer. Monochrome mode maps
those logical colors to luminance levels at render time, so application pages do
not need separate monochrome markup.

The shell itself does not own persistent storage. Prométhée and Hermès persist
the shared terminal preferences and inject them back into the shell on startup.
