# Server fleet

Server Fleet gives one OrbixPanel control plane a cached, read-only health view of other OrbixPanel servers. Nodes authenticate with narrowly scoped access keys. Every connection is HTTPS and must match a pinned SHA256 public key, so registration does not depend on public certificate-authority trust.

## Prepare a remote node

On each remote OrbixPanel server, enable administrator API access and create an access key using the bundled `fleet-read` permission profile:

```bash
v-add-access-key admin fleet-read "Fleet health monitor" json
```

The profile permits only these commands:

- `v-list-sys-info`
- `v-list-sys-health-summary`
- `v-list-sys-services`

Keep the returned access and secret keys available for the one-time registration form.

## Pin the node identity

Generate curl's SHA256 public-key pin from the certificate served by the remote panel port. Run this from a trusted administrative workstation and verify the endpoint before copying the result:

```bash
openssl s_client -connect panel.example.com:8083 -servername panel.example.com < /dev/null 2> /dev/null \
	| openssl x509 -pubkey -noout \
	| openssl pkey -pubin -outform DER \
	| openssl dgst -sha256 -binary \
	| openssl base64
```

Prefix the output with `sha256//` when registering the node. For example, a complete pin looks like `sha256//BASE64_VALUE=`.

## Register and refresh nodes

Open **Server Manager → Server Fleet**, then enter a short node name, HTTPS host and port, public-key pin, access key, and secret key. OrbixPanel runs all three permitted health calls before saving anything. Failed TLS pinning, authentication, permissions, or response validation aborts registration.

The credential is stored under the root-only fleet registry and is never returned by list commands or the interface. The registration route passes the secret through a short-lived file, and remote API request bodies are streamed to curl over standard input, so secrets do not appear in process command lines.

Fleet refreshes run as serialized background jobs. Each node has bounded connection and execution timeouts; an unreachable node is marked offline without preventing the remaining nodes from refreshing. The interface retains the last successful health snapshot so operators can compare the failure with earlier state.

## Security boundary

Server Fleet is intentionally read-only. It does not distribute SSH keys, execute arbitrary commands, change services, or provision accounts across nodes. Adding cross-node mutations requires a separate, explicitly permissioned workflow with per-action rollback and audit semantics.
