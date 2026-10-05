# Prométhée design system

The runtime contract for the Prométhée UI lives in `public/promethee-assets/promethee-design-system.css`.

## Layering contract

1. `promethee.css` keeps the legacy structural baseline.
2. `promethee-v2.css` contains the current operational layout and era-specific presentation.
3. `promethee-appearance.css` owns light/dark appearance values.
4. `promethee-design-system.css` exposes the canonical `--ds-*` tokens and the compatibility bridge used by the modern era.
5. Page-specific styles may specialize layout, but should consume `--ds-*` tokens for common colours, borders, radii, controls and typography.
6. `promethee-era-components.css` and `promethee-airinter-brand.css` add component/brand behavior.
7. `promethee-accessibility.css` remains the final accessibility and motion safety layer.

## Token families

- `--ds-brand-*`: Air Inter core identity.
- `--ds-band-*`: historic three-blue/one-red signature.
- `--ds-bg`, `--ds-surface*`, `--ds-text*`, `--ds-border*`: semantic surfaces and content.
- `--ds-action`, `--ds-danger`, `--ds-success`, `--ds-warning`, `--ds-info`: interaction/status semantics.
- `--ds-font-*`, `--ds-radius-*`, `--ds-space-*`, `--ds-shadow-*`: shared primitives.

## Compatibility

Modern Prométhée still exposes `--paper`, `--panel`, `--ink`, `--muted`, `--line`, `--ops-blue` and related aliases so older screens do not need a mass rewrite.

The 2000 and Minitel eras keep their dedicated variables and presentation. Do not replace those era overrides with modern `--ds-*` values unless the change is intentionally cross-era.

## Contribution rule

Before adding a new hard-coded colour or radius to a shared component, check whether an existing semantic token already represents the intent. Page-specific colours are acceptable only when they express a unique operational visualization rather than generic UI chrome.
