#!/bin/bash

set -Eeuo pipefail

ORBIXPANEL_BRANCH="${ORBIXPANEL_BRANCH:-main}"
ORBIXPANEL_RAW_URL="https://raw.githubusercontent.com/OrbixtarTechnologies/orbixtar-panel/${ORBIXPANEL_BRANCH}"
HESTIA_ROOT="${HESTIA:-/usr/local/hestia}"
ORBIXPANEL_STATE_DIR="${ORBIXPANEL_STATE_DIR:-/var/lib/orbixpanel}"
ORBIXPANEL_REAPPLY_COMMAND="${ORBIXPANEL_REAPPLY_COMMAND:-/usr/local/sbin/orbixpanel-reapply-brand}"

if [[ ! -d "$HESTIA_ROOT/web" ]]; then
	echo "OrbixPanel overlay: panel web root was not found at $HESTIA_ROOT/web" >&2
	exit 1
fi

overlay_tmp=$(mktemp -d /tmp/orbixpanel-overlay.XXXXXX)
trap 'rm -rf "$overlay_tmp"' EXIT

overlay_files=(
	"install/orbixpanel-overlay.sh"
	"bin/v-add-sys-dependencies"
	"bin/v-add-sys-pma-restrict"
	"bin/v-add-sys-pma-sso"
	"bin/v-add-sys-quota"
	"bin/v-add-sys-sftp-jail"
	"bin/v-add-user"
	"bin/v-add-reseller-user"
	"bin/v-change-reseller-user-status"
	"bin/v-change-sys-port"
	"bin/v-change-sys-web-terminal-port"
	"bin/v-change-user-package"
	"bin/v-change-user-reseller"
	"bin/v-check-mail-domain-dns"
	"bin/v-delete-sys-filemanager"
	"bin/v-delete-sys-mail-queue-message"
	"bin/v-delete-sys-pma-sso"
	"bin/v-delete-reseller-user"
	"bin/v-delete-fleet-node"
	"bin/v-get-cpanel-import-log"
	"bin/v-get-autossl-log"
	"bin/v-get-sys-mail-queue-message"
	"bin/v-import-cpanel"
	"bin/v-import-directadmin"
	"bin/v-list-cpanel-backups"
	"bin/v-list-autossl-jobs"
	"bin/v-list-cpanel-imports"
	"bin/v-list-fleet-nodes"
	"bin/v-list-fleet-refresh-jobs"
	"bin/v-list-owned-user"
	"bin/v-list-owned-users"
	"bin/v-list-runtime-profile-jobs"
	"bin/v-list-reseller-users"
	"bin/v-list-sys-php-extensions"
	"bin/v-list-user"
	"bin/v-list-users"
	"bin/v-list-web-domain-ssl-status"
	"bin/v-list-sys-hestia-updates"
	"bin/v-list-sys-health-summary"
	"bin/v-list-sys-mail-queue"
	"bin/v-start-cpanel-import"
	"bin/v-start-cpanel-remote-transfer"
	"bin/v-start-autossl-user"
	"bin/v-start-fleet-refresh"
	"bin/v-start-runtime-profile"
	"bin/v-retry-sys-mail-queue-message"
	"bin/v-refresh-fleet-node"
	"bin/v-stop-firewall"
	"bin/v-update-dns-templates"
	"bin/v-update-firewall"
	"bin/v-update-mail-templates"
	"bin/v-update-sys-hestia-git"
	"bin/v-update-user-counters"
	"bin/v-update-web-templates"
	"web/css/themes/default.min.css"
	"web/images/logo.svg"
	"web/images/logo-header.svg"
	"web/images/logo.png"
	"web/images/favicon.png"
	"web/favicon.ico"
	"web/js/orbixpanel-overlay.min.js"
	"web/js/orbixpanel-overlay.min.js.map"
	"web/templates/header.php"
	"web/templates/includes/app-footer.php"
	"web/templates/includes/js.php"
	"web/templates/includes/panel.php"
	"web/templates/pages/dashboard.php"
	"web/templates/pages/edit_server.php"
	"web/templates/pages/edit_user.php"
	"web/templates/pages/edit_web.php"
	"web/templates/pages/list_db.php"
	"web/templates/pages/list_autossl_job.php"
	"web/templates/pages/list_hosting.php"
	"web/templates/pages/list_fleet.php"
	"web/templates/pages/list_mail.php"
	"web/templates/pages/list_mail_deliverability.php"
	"web/templates/pages/list_mail_queue.php"
	"web/templates/pages/list_mail_queue_message.php"
	"web/templates/pages/list_migration_log.php"
	"web/templates/pages/list_migrations.php"
	"web/templates/pages/list_runtime_profiles.php"
	"web/templates/pages/list_security.php"
	"web/templates/pages/list_reseller.php"
	"web/templates/pages/list_server_health.php"
	"web/templates/pages/list_ssl_status.php"
	"web/templates/pages/list_server_info.php"
	"web/templates/pages/list_services.php"
	"web/templates/pages/list_weblog.php"
	"web/api/index.php"
	"web/execute/bootstrap.php"
	"web/execute/Email/count_pops/index.php"
	"web/execute/Email/list_mail_domains/index.php"
	"web/execute/Email/list_pops/index.php"
	"web/execute/WebVhosts/list_domains/index.php"
	"web/inc/helpers.php"
	"web/inc/2fa/check.php"
	"web/inc/2fa/secret.php"
	"web/bulk/backup/incremental/index.php"
	"web/bulk/restore/index.php"
	"web/download/backup/index.php"
	"web/delete/reseller/index.php"
	"web/edit/user/index.php"
	"web/json-api/bootstrap.php"
	"web/json-api/accountsummary/index.php"
	"web/json-api/listaccts/index.php"
	"web/list/hosting/index.php"
	"web/list/fleet/index.php"
	"web/list/mail-deliverability/index.php"
	"web/list/mail-queue/index.php"
	"web/list/mail-queue/message/index.php"
	"web/list/migrations/index.php"
	"web/list/migrations/log/index.php"
	"web/list/runtime-profiles/index.php"
	"web/list/security/index.php"
	"web/list/reseller/index.php"
	"web/list/server-health/index.php"
	"web/list/ssl-status/index.php"
	"web/list/ssl-status/job/index.php"
	"web/schedule/backup/index.php"
	"web/schedule/restore/index.php"
	"web/schedule/restore/incremental/index.php"
	"web/unsuspend/dns/index.php"
	"web/unsuspend/mail/index.php"
	"func/upgrade.sh"
	"func/fleet.sh"
	"func/main.sh"
	"func/rebuild.sh"
	"func/syshealth.sh"
	"install/common/roundcube/hestia.php"
	"install/common/api/uapi-read"
	"install/common/api/fleet-read"
	"install/common/api/whm-read"
	"install/common/nginx/orbix-uapi.conf"
	"install/deb/filemanager/filegator/configuration.php"
	"install/deb/templates/web/awstats/nav.tpl"
)

echo "[ * ] Applying OrbixPanel interface..."

for relative_path in "${overlay_files[@]}"; do
	source_url="$ORBIXPANEL_RAW_URL/$relative_path"
	temporary_path="$overlay_tmp/$relative_path"
	target_path="$HESTIA_ROOT/$relative_path"

	mkdir -p "$(dirname "$temporary_path")"
	curl --fail --silent --show-error --location --retry 3 "$source_url" --output "$temporary_path"
	mkdir -p "$(dirname "$target_path")"
	file_mode=0644
	case "$relative_path" in
		bin/* | install/orbixpanel-overlay.sh) file_mode=0755 ;;
	esac
	install -m "$file_mode" "$temporary_path" "$target_path"
done

install -d -m 0750 "$HESTIA_ROOT/data/api"
install -m 0640 "$HESTIA_ROOT/install/common/api/uapi-read" "$HESTIA_ROOT/data/api/uapi-read"
install -m 0640 "$HESTIA_ROOT/install/common/api/fleet-read" "$HESTIA_ROOT/data/api/fleet-read"
install -m 0640 "$HESTIA_ROOT/install/common/api/whm-read" "$HESTIA_ROOT/data/api/whm-read"

panel_nginx_config="$HESTIA_ROOT/nginx/conf/nginx.conf"
if [[ -f "$panel_nginx_config" ]] && ! grep -q 'orbix-uapi.conf' "$panel_nginx_config"; then
	panel_nginx_backup="$overlay_tmp/nginx.conf.before-uapi"
	cp -p "$panel_nginx_config" "$panel_nginx_backup"
	sed -i '/^[[:space:]]*location ~ \\.php\$ {/i\		include /usr/local/hestia/install/common/nginx/orbix-uapi.conf;\n' "$panel_nginx_config"
	if "$HESTIA_ROOT/nginx/sbin/hestia-nginx" -t -c "$panel_nginx_config"; then
		if [[ -s /run/hestia-nginx.pid ]]; then
			kill -HUP "$(cat /run/hestia-nginx.pid)"
		fi
	else
		cp -p "$panel_nginx_backup" "$panel_nginx_config"
		echo "OrbixPanel overlay: UAPI route configuration was reverted after validation failed" >&2
		exit 1
	fi
fi

# Repair ownership metadata without rebuilding customer services.
ORBIXPANEL_ROOT_USER=$(awk -F"'" '/^ROOT_USER=/ {print $2}' "$HESTIA_ROOT/conf/hestia.conf")
ORBIXPANEL_ROOT_USER=${ORBIXPANEL_ROOT_USER:-admin}
for user_conf in "$HESTIA_ROOT"/data/users/*/user.conf; do
	[[ -f "$user_conf" ]] || continue
	if ! grep -q "^OWNER=" "$user_conf"; then
		sed -i "/^ROLE=/a OWNER='$ORBIXPANEL_ROOT_USER'" "$user_conf"
	fi
	if ! grep -q "^RESELLER=" "$user_conf"; then
		sed -i "/^OWNER=/a RESELLER='no'\nRESELLER_MAX_USERS='0'\nRESELLER_PACKAGES=''" "$user_conf"
	fi
done

if [[ -x "$HESTIA_ROOT/bin/v-change-sys-config-value" ]]; then
	"$HESTIA_ROOT/bin/v-change-sys-config-value" APP_NAME "OrbixPanel"
fi

install -m 0755 "$HESTIA_ROOT/install/orbixpanel-overlay.sh" "$HESTIA_ROOT/bin/v-update-orbixpanel-brand"

install -d -m 0755 "$(dirname "$ORBIXPANEL_REAPPLY_COMMAND")" "$ORBIXPANEL_STATE_DIR"
cat > "$ORBIXPANEL_REAPPLY_COMMAND" << 'REAPPLY_SCRIPT'
#!/bin/bash

set -Eeuo pipefail

state_file="/var/lib/orbixpanel/hestia-package-version"
updater="/usr/local/hestia/bin/v-update-orbixpanel-brand"
current_version="$(dpkg-query -W -f='${Version}' hestia 2> /dev/null || true)"
previous_version="$(cat "$state_file" 2> /dev/null || true)"

if [[ -z "$current_version" || "$current_version" == "$previous_version" ]]; then
	exit 0
fi

if [[ ! -x "$updater" ]]; then
	echo "OrbixPanel updater was not found at $updater" >&2
	exit 1
fi

"$updater"
printf '%s\n' "$current_version" > "$state_file"
REAPPLY_SCRIPT
chmod 0755 "$ORBIXPANEL_REAPPLY_COMMAND"

cat > /etc/apt/apt.conf.d/99-orbixpanel-brand << 'APT_HOOK'
DPkg::Post-Invoke { "if [ -x /usr/local/sbin/orbixpanel-reapply-brand ]; then /usr/local/sbin/orbixpanel-reapply-brand >>/var/log/orbixpanel-brand.log 2>&1 || true; fi"; };
APT_HOOK

installed_package_version="$(dpkg-query -W -f='${Version}' hestia 2> /dev/null || true)"
if [[ -n "$installed_package_version" ]]; then
	printf '%s\n' "$installed_package_version" > "$ORBIXPANEL_STATE_DIR/hestia-package-version"
fi

echo "[ ✓ ] OrbixPanel interface installed."
