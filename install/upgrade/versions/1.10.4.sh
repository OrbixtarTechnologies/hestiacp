#!/bin/bash

# Add least-privilege reseller ownership metadata to existing user records.
for user_conf in "$HESTIA"/data/users/*/user.conf; do
	[ -f "$user_conf" ] || continue
	if ! grep -q "^OWNER=" "$user_conf"; then
		sed -i "/^ROLE=/a OWNER='$ROOT_USER'" "$user_conf"
	fi
	if ! grep -q "^RESELLER=" "$user_conf"; then
		sed -i "/^OWNER=/a RESELLER='no'\nRESELLER_MAX_USERS='0'\nRESELLER_PACKAGES=''" "$user_conf"
	fi
done

"$HESTIA/bin/v-update-user-counters"

install -d -m 0750 "$HESTIA/data/api"
install -m 0640 "$HESTIA/install/common/api/uapi-read" "$HESTIA/data/api/uapi-read"
install -m 0640 "$HESTIA/install/common/api/fleet-read" "$HESTIA/data/api/fleet-read"
install -m 0640 "$HESTIA/install/common/api/whm-read" "$HESTIA/data/api/whm-read"
