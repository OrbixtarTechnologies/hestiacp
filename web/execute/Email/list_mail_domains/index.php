<?php
require_once dirname(__DIR__, 2) . "/bootstrap.php";

$module = "Email";
$function = "list_mail_domains";
$credentials = orbix_uapi_bootstrap($module, $function, ["v-list-mail-domains"]);
orbix_uapi_allow_query_params($module, $function, ["select"]);
$domains = orbix_uapi_command($module, $function, "v-list-mail-domains", [
	$credentials["user"],
	"json",
]);

$selected = array_key_exists("select", $_GET)
	? orbix_uapi_query($module, $function, "select")
	: null;
$data = [];
foreach ($domains as $domain => $details) {
	$entry = ["domain" => $domain];
	if ($selected !== null) {
		$entry["select"] = hash_equals($domain, $selected) ? 1 : 0;
	}
	$data[] = $entry;
}

orbix_uapi_response($module, $function, $data);
