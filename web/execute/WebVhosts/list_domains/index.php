<?php
require_once dirname(__DIR__, 2) . "/bootstrap.php";

$module = "WebVhosts";
$function = "list_domains";
$credentials = orbix_uapi_bootstrap($module, $function, ["v-list-web-domains"]);
orbix_uapi_allow_query_params($module, $function, []);
$domains = orbix_uapi_command($module, $function, "v-list-web-domains", [
	$credentials["user"],
	"json",
]);

$data = [];
foreach ($domains as $domain => $details) {
	$data[] = [
		"domain" => $domain,
		"documentroot" => $details["DOCUMENT_ROOT"] ?? "",
		"ip" => $details["IP"] ?? "",
		"ipv6" => $details["IP6"] ?? "",
		"is_ssl" => ($details["SSL"] ?? "no") === "yes" ? 1 : 0,
		"suspended" => ($details["SUSPENDED"] ?? "no") === "yes" ? 1 : 0,
	];
}

orbix_uapi_response($module, $function, $data);
