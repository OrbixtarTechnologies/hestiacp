# Contributing to OrbixPanel

OrbixPanel welcomes focused bug fixes, security hardening, tests, documentation, translations, design improvements, and hosting-management features.

## Before starting

1. Search [existing issues](https://github.com/OrbixtarTechnologies/orbixtar-panel/issues) for related work.
2. Open or comment on an issue before undertaking a large feature or operational change.
3. Base changes on `main` and keep each pull request limited to one coherent outcome.
4. Never include real credentials, access keys, hostnames, email addresses, or server IPs in fixtures, logs, or screenshots.

Inherited command names, paths, environment variables, namespaces, and translation domains may contain upstream compatibility identifiers. Do not rename them cosmetically: migrations must remain compatible with installed servers, automation, packages, and upgrades.

## Development checks

Install the declared npm toolchain and run the checks relevant to the changed surfaces:

```bash
npm install
npm run build
npm run lint
```

For shell, PHP, installer, or server-command changes, run the applicable checks under `test/` on a supported Linux environment. Changes to user-facing workflows should also be verified at desktop and mobile widths, with keyboard navigation and reduced-motion preferences.

## Product expectations

- Use OrbixPanel and Orbixtar branding on all new user-visible and project-owned surfaces.
- Connect interface tools to real routes and commands; do not add controls that only simulate a capability.
- Preserve account, reseller, and administrator permission boundaries.
- Prefer plain hosting terminology and consistent action names throughout a workflow.
- Include success, empty, error, and rollback behavior for operational changes.
- Keep third-party trademarks, artwork, and proprietary implementation details out of the product.

## Pull requests

Describe the user or operator problem, the implemented behavior, security and compatibility considerations, and the exact verification performed. Add screenshots for visual changes and tests for new command, API, permission, or route behavior whenever practical.

By contributing, you agree that your work is provided under the repository's GPL-3.0-or-later license.
