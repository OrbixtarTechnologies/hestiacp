# cPanel and WHM parity

OrbixPanel provides an Orbixtar-owned hosting experience while retaining the proven internal service and package identifiers required for upgrade compatibility. The interface, documentation, runtime messages, installer experience, support links, API identity, and embedded tools use OrbixPanel branding.

This page tracks functional parity by workflow rather than copying cPanel or WHM screens verbatim.

## Hosting-account workflows

| Workflow             | OrbixPanel implementation                                                                           | Status |
| -------------------- | --------------------------------------------------------------------------------------------------- | ------ |
| Account home         | Hosting Overview with service counts, usage, recovery points, and common workflows                  | Native |
| Websites             | Web-domain provisioning, aliases, redirects, templates, statistics, caching, and document roots     | Native |
| SSL/TLS              | Account-wide health, 30-day expiry warning, manual certificates, and tracked bulk AutoSSL repair    | Native |
| Files                | Sandboxed File Manager, SFTP, SSH keys, archives, and additional FTP accounts                       | Native |
| DNS                  | Zone and record management, templates, DNSSEC, and DNS clustering                                   | Native |
| Email                | Domains, mailboxes, aliases, forwarding, catch-all, webmail, quotas, rate limits, and spam controls | Native |
| Email deliverability | Public MX, SPF, DKIM, and DMARC diagnosis with remediation links                                    | Native |
| Databases            | MariaDB/MySQL and PostgreSQL database and user management with bundled database tools               | Native |
| Automation           | Cron job creation, editing, suspension, and deletion                                                | Native |
| Backups              | Scheduled account backups, incremental restore paths, downloads, exclusions, and remote targets     | Native |
| Metrics              | Account usage, web statistics, logs, and resource history                                           | Native |

## Server-administration workflows

| Workflow         | OrbixPanel implementation                                                                         | Status  |
| ---------------- | ------------------------------------------------------------------------------------------------- | ------- |
| Accounts         | Provision, edit, suspend, delete, inspect, and package hosting accounts                           | Native  |
| Resellers        | Owner-scoped customer provisioning, package allow-lists, account caps, suspension, and deletion   | Native  |
| Packages         | Define account resource and service limits                                                        | Native  |
| Server health    | Capacity, load, uptime, disk, memory, and service-exception overview                              | Native  |
| Service manager  | Inspect, configure, start, stop, and restart monitored services                                   | Native  |
| Security         | Firewall, banned hosts, access keys, API controls, SSH settings, and security posture overview    | Native  |
| Migrations       | Local or resumable host-key-pinned SFTP intake of cPanel backups with validation, state, and logs | Native  |
| Mail queue       | Delayed and frozen message inspection, delivery-log review, retry, and per-message deletion       | Native  |
| Networking       | Server IP, NAT, ownership, and status management                                                  | Native  |
| Server fleet     | Pinned-TLS node registration, cached health, service inventory, and tracked serialized refreshes  | Native  |
| Updates          | Operating-system and panel update visibility and installation workflows                           | Native  |
| Recovery         | User backup inventory and restore scheduling                                                      | Native  |
| Operations       | System logs, task monitor, secured terminal, and server configuration                             | Native  |
| Runtime profiles | Tracked MultiPHP builds with default switching, extension inventory, and removal guards           | Native  |
| API integration  | cPanel UAPI v3 inventory plus owner-scoped WHM API 1 account listing and summary                  | Partial |

## Remaining parity gaps

The following areas need dedicated engineering rather than interface aliases:

- aggregate reseller resource pools and reseller-specific private branding beyond the enforced account and package scopes;
- live source-side cPanel backup generation and database-by-database delta synchronization before final cutover;
- broader write-capable cPanel UAPI and WHM API endpoint coverage beyond the initial read-only inventory routes;
- write-capable multi-server orchestration beyond DNS clustering and the read-only fleet health plane;
- web-server module compilation and non-PHP extension catalogs beyond the tracked MultiPHP runtime profile workflow;
- Linux integration, upgrade, rollback, accessibility, responsive-browser, and reduced-motion acceptance runs on supported deployment images.

## Compatibility boundary

Internal filesystem paths, package names, environment variables, PHP namespaces, legacy response headers, and service identifiers may retain upstream-compatible names where changing them would break installations or integrations. They are implementation contracts, not customer-facing branding. Legal copyright and license notices also remain intact.

The standalone OrbixPanel overlay reapplies project-owned runtime files after package upgrades so the branded interface and added workflows survive normal update cycles.
