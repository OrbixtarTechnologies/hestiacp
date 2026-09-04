# OrbixPanel

OrbixPanel is Orbixtar's open-source hosting and server management platform. It brings customer hosting tools and administrator operations into one responsive control panel, with an interface designed around familiar cPanel and WHM workflows while remaining an independent product.

> Development status: active. The `main` branch contains ongoing product work and should be evaluated in a non-production environment before deployment.

## What OrbixPanel manages

### Hosting accounts

- Websites, domains, redirects, aliases, SSL/TLS, and application installers
- DNS zones and records, including DNSSEC where supported
- Email domains, mailboxes, aliases, forwarding, anti-spam, and antivirus controls
- MariaDB/MySQL and PostgreSQL databases and users
- Files, archives, backups, restores, disk usage, and transfer statistics
- Cron jobs, account preferences, SSH keys, two-factor authentication, and web terminal access

### Server administration

- Account provisioning, suspension, packages, quotas, and capability limits
- Web, DNS, mail, database, and PHP service configuration
- IP addresses, firewall rules, block lists, and brute-force protection
- Service health, resource monitoring, logs, updates, and notifications
- Local and remote backups, restore workflows, and incremental backup support
- Access keys and API-based automation

See the [cPanel and WHM parity matrix](docs/docs/reference/cpanel-whm-parity.md) for implemented workflows, compatibility boundaries, and remaining engineering gaps.

## Supported systems

- Debian 11, 12, and 13
- Ubuntu 22.04 LTS, 24.04 LTS, and 26.04 LTS
- 64-bit systems using a clean operating-system installation

KVM or LXC virtualization is recommended. Existing web, mail, DNS, or database stacks can conflict with services managed by OrbixPanel.

## Installation

Install OrbixPanel only on a fresh supported server. Sign in as `root`, download the installer, review its options, and run it:

```bash
curl -fsSL https://raw.githubusercontent.com/OrbixtarTechnologies/orbixtar-panel/main/install/hst-install.sh -o orbixpanel-install.sh
bash orbixpanel-install.sh --help
bash orbixpanel-install.sh
```

The installer provisions the selected services, configures the OrbixPanel identity, and displays the secure control-panel URL when setup completes.

## Development

The interface assets require Node.js and the npm version declared in `package.json`.

```bash
npm install
npm run build
npm run lint
```

Server-level behavior is exercised by the Bats suites under `test/`. Many integration tests require a supported Linux test host or the repository's container test environment.

See [CONTRIBUTING.md](CONTRIBUTING.md) before proposing a change and [SECURITY.md](SECURITY.md) when reporting a vulnerability.

## Project principles

- Orbixtar branding is the default across installation, runtime, documentation, and support surfaces.
- Customer and administrator tools must be backed by real routes, commands, capability checks, and permission boundaries.
- Familiar hosting-control-panel language may be used, but third-party trademarks, artwork, proprietary code, and misleading compatibility claims are not.
- Responsive behavior, keyboard access, reduced-motion preferences, secure defaults, and recoverable operational workflows are release requirements.

## License and upstream acknowledgement

OrbixPanel is licensed under the GNU General Public License v3.0 or later. It is derived from the Hestia Control Panel and Vesta Control Panel codebases; their copyright notices and license obligations remain applicable to inherited code. Hestia, HestiaCP, cPanel, and WHM are trademarks of their respective owners and do not sponsor or endorse OrbixPanel.
