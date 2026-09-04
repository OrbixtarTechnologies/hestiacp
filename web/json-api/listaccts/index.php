<?php
require_once dirname(__DIR__) . "/bootstrap.php";

$command = "listaccts";
$credentials = orbix_whm_bootstrap($command, ["v-list-owned-users"], []);
$accounts = orbix_uapi_command("WHM", $command, "v-list-owned-users", [
	$credentials["user"],
	"json",
]);

$data = [];
foreach ($accounts as $user => $details) {
	if (($details["ROLE"] ?? "user") !== "user") {
		continue;
	}
	$data[] = orbix_whm_account($user, $details, $credentials["user"]);
}

orbix_whm_response($command, ["acct" => $data]);
