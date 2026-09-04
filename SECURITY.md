# OrbixPanel security policy

Security reports help Orbixtar protect OrbixPanel users and the servers they operate. Do not disclose a suspected vulnerability in a public issue, discussion, or social-media post before a fix is available.

## Reporting a vulnerability

Submit a private report through [GitHub Security Advisories](https://github.com/OrbixtarTechnologies/orbixtar-panel/security/advisories/new).

Include:

- the affected OrbixPanel version, operating system, and enabled services;
- a clear impact assessment and the permissions required to reproduce it;
- complete reproduction steps or a minimal proof of concept;
- relevant logs with credentials, hostnames, email addresses, and IP addresses removed; and
- any temporary mitigation you have confirmed.

The Orbixtar team will validate the report, coordinate a remediation, and credit the reporter when requested and appropriate.

## Supported versions

| Version               | Security support |
| --------------------- | ---------------- |
| Latest release        | Supported        |
| Development snapshots | Best effort      |
| Older releases        | Upgrade required |

## In scope

- Remote command execution
- Code or SQL injection
- Authentication or authorization bypass
- Privilege escalation
- Cross-site scripting and cross-site request forgery
- Exposure of secrets or another account's data
- Unsafe server-management actions reachable without the documented permission

## Generally out of scope

- Reports without a reproducible security impact
- Vulnerabilities exclusively in an upstream dependency that should be reported to its maintainer
- Social engineering or attacks requiring physical access
- Findings that require an already compromised administrator or server
- Self-XSS, reflected file downloads, or open redirects without additional impact
- Test-only fixtures under `test/`
