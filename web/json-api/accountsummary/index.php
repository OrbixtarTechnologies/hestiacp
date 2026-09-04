<?php
require_once dirname(__DIR__) . "/bootstrap.php";

$command = "accountsummary";
$credentials = orbix_whm_bootstrap($command, ["v-list-owned-user"], ["user"]);
$user = orbix_uapi_query("WHM", $command, "user", "", 30);
if (!preg_match('/^[a-z][a-z0-9_-]{0,29}$/', $user)) {
	orbix_whm_fail($command, "A valid user parameter is required.");
}

$account = orbix_uapi_command("WHM", $command, "v-list-owned-user", [
	$credentials["user"],
	$user,
	"json",
]);
$details = $account[$user] ?? null;
if (!is_array($details)) {
	orbix_whm_fail($command, "The account could not be found.");
}

orbix_whm_response($command, [
	"acct" => [orbix_whm_account($user, $details, $credentials["user"])],
]);
