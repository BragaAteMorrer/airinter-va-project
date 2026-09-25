# 3615 AIRINTER — Minitel Runtime (M0 + M1)

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

Prométhée/Hermès business pages remain outside M0/M1. M2 can now integrate the shell into Prométhée consultation screens.
