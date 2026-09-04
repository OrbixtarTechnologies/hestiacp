<?php
require_once dirname(__DIR__, 2) . "/bootstrap.php";

$module = "Email";
$function = "list_pops";
$credentials = orbix_uapi_bootstrap($module, $function, [
	"v-list-mail-domains",
	"v-list-mail-accounts",
]);
orbix_uapi_allow_query_params($module, $function, ["domain", "skip_main"]);
$domains = orbix_uapi_command($module, $function, "v-list-mail-domains", [
	$credentials["user"],
	"json",
]);
$requestedDomain = orbix_uapi_query($module, $function, "domain");
$skipMainValue = orbix_uapi_query($module, $function, "skip_main", "0", 1);
if (!in_array($skipMainValue, ["0", "1"], true)) {
	orbix_uapi_fail($module, $function, "skip_main must be 0 or 1.");
}
$skipMain = $skipMainValue === "1";
$data = [];

foreach ($domains as $domain => $domainDetails) {
	if ($requestedDomain !== "" && !hash_equals($domain, $requestedDomain)) {
		continue;
	}
	$accounts = orbix_uapi_command($module, $function, "v-list-mail-accounts", [
		$credentials["user"],
		$domain,
		"json",
	]);
	foreach ($accounts as $account => $details) {
		$data[] = [
			"email" => $account . "@" . $domain,
			"login" => $account . "@" . $domain,
			"domain" => $domain,
			"user" => $account,
			"diskused" => (float) ($details["U_DISK"] ?? 0),
			"diskquota" =>
				($details["QUOTA"] ?? "unlimited") === "unlimited" ? 0 : (float) $details["QUOTA"],
			"suspended_incoming" => 0,
			"suspended_login" => ($details["SUSPENDED"] ?? "no") === "yes" ? 1 : 0,
		];
	}
}

// OrbixPanel has no synthetic mailbox for the Unix account, so skip_main has no effect.
unset($skipMain);
orbix_uapi_response($module, $function, $data);
