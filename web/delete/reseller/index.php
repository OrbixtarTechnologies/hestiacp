<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

verify_csrf($_GET);

$managed_user = $_GET["user"] ?? "";
$reseller = $_SESSION["user"];

exec(HESTIA_CMD . "v-list-user " . quoteshellarg($reseller) . " json", $output, $return_var);
$profile_data = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$profile = $profile_data[$reseller] ?? [];
unset($output);

if (
	$_SESSION["userContext"] !== "user" ||
	!empty($_SESSION["look"]) ||
	($profile["RESELLER"] ?? "no") !== "yes" ||
	!preg_match('/^[A-Za-z][A-Za-z0-9_-]{1,28}[A-Za-z0-9]$/', $managed_user)
) {
	header("Location: /dashboard/");
	exit();
}

exec(
	HESTIA_CMD .
		"v-delete-reseller-user " .
		quoteshellarg($reseller) .
		" " .
		quoteshellarg($managed_user),
	$output,
	$return_var,
);
check_return_code($return_var, $output);
unset($output);

if (empty($_SESSION["error_msg"])) {
	$_SESSION["ok_msg"] = _("Customer account deleted.");
}

header("Location: /list/reseller/");
exit();
