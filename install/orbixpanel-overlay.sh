#!/bin/bash

set -Eeuo pipefail

ORBIXPANEL_BRANCH="${ORBIXPANEL_BRANCH:-main}"
ORBIXPANEL_RAW_URL="https://raw.githubusercontent.com/OrbixtarTechnologies/hestiacp/${ORBIXPANEL_BRANCH}"
HESTIA_ROOT="${HESTIA:-/usr/local/hestia}"

if [[ ! -d "$HESTIA_ROOT/web" ]]; then
	echo "OrbixPanel overlay: panel web root was not found at $HESTIA_ROOT/web" >&2
	exit 1
fi

overlay_tmp=$(mktemp -d /tmp/orbixpanel-overlay.XXXXXX)
trap 'rm -rf "$overlay_tmp"' EXIT

overlay_files=(
	"web/css/themes/default.min.css"
	"web/images/logo.svg"
	"web/images/logo-header.svg"
	"web/images/logo.png"
	"web/images/favicon.png"
	"web/favicon.ico"
	"web/templates/header.php"
	"web/templates/includes/app-footer.php"
	"web/templates/includes/panel.php"
	"web/templates/pages/list_server_info.php"
	"web/templates/pages/list_services.php"
	"web/templates/pages/list_weblog.php"
	"web/inc/2fa/check.php"
	"web/inc/2fa/secret.php"
)

echo "[ * ] Applying OrbixPanel interface..."

for relative_path in "${overlay_files[@]}"; do
	source_url="$ORBIXPANEL_RAW_URL/$relative_path"
	temporary_path="$overlay_tmp/$relative_path"
	target_path="$HESTIA_ROOT/$relative_path"

	mkdir -p "$(dirname "$temporary_path")"
	curl --fail --silent --show-error --location --retry 3 "$source_url" --output "$temporary_path"
	mkdir -p "$(dirname "$target_path")"
	install -m 0644 "$temporary_path" "$target_path"
done

if [[ -x "$HESTIA_ROOT/bin/v-change-sys-config-value" ]]; then
	"$HESTIA_ROOT/bin/v-change-sys-config-value" APP_NAME "OrbixPanel"
fi

install -m 0755 "$0" "$HESTIA_ROOT/bin/v-update-orbixpanel-brand"

echo "[ ✓ ] OrbixPanel interface installed."
