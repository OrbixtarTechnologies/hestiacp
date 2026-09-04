<?php
$TAB = "SECURITY";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] !== "admin" || !empty($_SESSION["look"])) {
	header("Location: /dashboard/");
	exit();
}

function security_command_json(string $command): array {
	exec(HESTIA_CMD . $command, $output, $return_var);
	if ($return_var !== 0) {
		return [];
	}

	$data = json_decode(implode("", $output), true);
	return is_array($data) ? $data : [];
}

$firewall_rules = !empty($_SESSION["FIREWALL_SYSTEM"])
	? security_command_json("v-list-firewall json")
	: [];
$banned_addresses = !empty($_SESSION["FIREWALL_EXTENSION"])
	? security_command_json("v-list-firewall-ban json")
	: [];
$authentication_events = security_command_json("v-list-user-auth-log " . $user . " json");
$access_keys =
	!empty($_SESSION["API_SYSTEM"]) && (int) $_SESSION["API_SYSTEM"] > 0
		? security_command_json("v-list-access-keys " . $user . " json")
		: [];

$current_account = $panel[$user] ?? ($panel[$user_plain] ?? []);
$security_summary = [
	"firewall_enabled" => !empty($_SESSION["FIREWALL_SYSTEM"]),
	"firewall_rules" => count($firewall_rules),
	"banned_addresses" => count($banned_addresses),
	"authentication_events" => count($authentication_events),
	"api_enabled" => !empty($_SESSION["API_SYSTEM"]) && (int) $_SESSION["API_SYSTEM"] > 0,
	"access_keys" => count($access_keys),
	"two_factor_enabled" => ($current_account["TWOFA"] ?? "no") === "yes",
];

render_page($user, $TAB, "list_security");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
