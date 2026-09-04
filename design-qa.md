# Jupiter + Server Manager Design QA

## Comparison target

- Source visual truth: official cPanel Jupiter Tools reference at `https://docs.cpanel.net/img/jupiter-tools-page-110.png` and official WHM Jupiter reference at `https://docs.cpanel.net/img/whm-jupiter-interface-136.png`.
- Implementation: OrbixPanel `dashboard` route rendered through a browser-only fixture using the production templates, compiled default theme, Font Awesome assets, Chart.js bundle, and global tool-search module.
- Browser evidence: cloud-browser captures recorded during the implementation session for both administrator and user states. The browser runtime did not expose durable screenshot file paths.
- Viewport: 1360 × 936 CSS pixels, device scale factor 1.
- Source and implementation captures were compared at the same browser viewport. Source screenshots retain their original image aspect ratio; layout regions rather than annotation markers were evaluated.

## State and interactions

- Administrator state: Server Manager home with onboarding actions, favorites, statistics, monitoring charts, and persistent navigation.
- User state: Jupiter-style Tools home with Email, Files, Databases, Domains, Advanced tools, General Information, and Statistics.
- `/` focuses the global search field.
- Selecting a tool and pressing Enter navigates to its mapped route.
- Dashboard cards and tool entries use existing OrbixPanel routes and capability checks.
- Console checked: no application warnings or errors. A browser-extension metadata warning was observed and is unrelated to OrbixPanel.

## Findings

- No P0, P1, or P2 findings remain in the implemented foundation.
- The initial WHM comparison found a P2 omission: server monitoring was not present below Favorites. The dashboard now loads the existing RRD inventory and renders three live Chart.js monitoring panels with a link to the complete Task Monitor.
- Intentional clean-room differences: OrbixPanel branding and logo, Font Awesome icons, original copy, and only features backed by existing OrbixPanel commands/routes. These avoid copying cPanel-owned brand assets and do not affect the selected information architecture.

## Fidelity surfaces

- Fonts and typography: compact system hierarchy, legible small labels, strong section headings, and consistent weights match the reference density without importing proprietary fonts.
- Spacing and layout rhythm: fixed dark rail, persistent top search, two-column content/metadata layout, bordered tool groups, compact cards, and responsive collapse rules match the reference structure.
- Colors and visual tokens: dark navy navigation, neutral gray canvas, white panels, blue actions, orange group accents, and accessible form contrast are consistently tokenized.
- Image and icon quality: the supplied OrbixPanel vector logo and packaged Font Awesome icon font are used; no placeholder, emoji, CSS-drawn, or copied cPanel assets are present.
- Copy and content: labels describe real OrbixPanel operations and avoid claiming unavailable cPanel-only capabilities.

## Focused comparison

The top navigation/search, left navigation rail, onboarding cards, Favorites, tool-group rows, monitoring panels, General Information, and Statistics panels were readable in the full-width captures, so separate cropped comparisons were unnecessary.

## Follow-up polish

- P3: add per-user configurable Favorites after a persistence format is defined.
- P3: add collapsible WHM navigation groups as the remaining administrator routes are reorganized in later parity phases.

## Implementation checklist

- [x] Role-aware dashboard route
- [x] Jupiter user Tools layout
- [x] WHM-style administrator home
- [x] Persistent global tool search and `/` shortcut
- [x] Real route and capability mapping
- [x] Live server-monitoring charts
- [x] Responsive desktop/tablet/mobile rules
- [x] Accessible input contrast and focus treatment
- [x] Asset build, formatter, JavaScript lint, and whitespace checks

final result: passed
