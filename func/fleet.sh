#!/bin/bash

fleet_nodes_dir="$HESTIA/data/fleet/nodes"
fleet_cache_dir="/var/log/orbixpanel/fleet"

fleet_prepare_dirs() {
	mkdir -p "$fleet_nodes_dir" "$fleet_cache_dir"
	chmod 700 "$HESTIA/data/fleet" "$fleet_nodes_dir"
	chmod 750 "$fleet_cache_dir"
}

fleet_validate_name() {
	if [[ "$1" == all || ! "$1" =~ ^[a-z0-9][a-z0-9-]{0,31}$ ]]; then
		check_result "$E_INVALID" "Fleet node name must use lowercase letters, numbers, and hyphens"
	fi
}

fleet_load_node() {
	local node=$1
	fleet_validate_name "$node"
	local config="$fleet_nodes_dir/$node.conf"
	if [ ! -f "$config" ]; then
		check_result "$E_NOTEXIST" "Fleet node $node does not exist"
	fi
	NAME='' HOST='' PORT='' TLS_PIN='' ACCESS_KEY='' SECRET_KEY='' DATE='' TIME=''
	source_conf "$config"
}

fleet_api_call() {
	local command=$1
	local format=${2-json}
	local endpoint="https://$HOST:$PORT/api/"
	printf '{"access_key":"%s","secret_key":"%s","cmd":"%s","arg1":"%s"}' \
		"$ACCESS_KEY" "$SECRET_KEY" "$command" "$format" \
		| curl --fail --silent --show-error \
			--insecure \
			--pinnedpubkey "$TLS_PIN" \
			--proto '=https' \
			--connect-timeout 8 \
			--max-time 25 \
			--header 'Content-Type: application/json' \
			--data-binary @- \
			"$endpoint"
}

fleet_collect_snapshot() {
	local node=$1
	local info health services checked_at
	info=$(fleet_api_call v-list-sys-info json) || return 1
	health=$(fleet_api_call v-list-sys-health-summary json) || return 1
	services=$(fleet_api_call v-list-sys-services json) || return 1
	if ! jq -e '.sysinfo | type == "object"' > /dev/null 2>&1 <<< "$info" \
		|| ! jq -e '.health | type == "object"' > /dev/null 2>&1 <<< "$health" \
		|| ! jq -e 'type == "object"' > /dev/null 2>&1 <<< "$services"; then
		return 1
	fi
	checked_at=$(date '+%FT%T%z')
	jq -n \
		--arg name "$node" \
		--arg checked_at "$checked_at" \
		--argjson info "$info" \
		--argjson health "$health" \
		--argjson services "$services" \
		'{STATUS:"online", NAME:$name, CHECKED_AT:$checked_at, INFO:$info.sysinfo, HEALTH:$health.health, SERVICES:$services}'
}

fleet_write_offline_snapshot() {
	local node=$1
	local message=${2:-Connection or authentication failed}
	local cache="$fleet_cache_dir/$node.json"
	local checked_at existing
	checked_at=$(date '+%FT%T%z')
	if [ -s "$cache" ] && jq -e 'type == "object"' "$cache" > /dev/null 2>&1; then
		existing=$(cat "$cache")
	else
		existing='{}'
	fi
	jq -n \
		--arg name "$node" \
		--arg checked_at "$checked_at" \
		--arg error "$message" \
		--argjson existing "$existing" \
		'$existing + {STATUS:"offline", NAME:$name, CHECKED_AT:$checked_at, ERROR:$error}'
}
