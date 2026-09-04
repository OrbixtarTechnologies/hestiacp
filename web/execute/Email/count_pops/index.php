<?php
require_once dirname(__DIR__, 2) . "/bootstrap.php";

$module = "Email";
$function = "count_pops";
$credentials = orbix_uapi_bootstrap($module, $function, ["v-list-mail-domains"]);
orbix_uapi_allow_query_params($module, $function, []);
$domains = orbix_uapi_command($module, $function, "v-list-mail-domains", [
	$credentials["user"],
	"json",
]);

$count = array_sum(array_map(fn($domain) => (int) ($domain["ACCOUNTS"] ?? 0), $domains));
orbix_uapi_response($module, $function, $count);
