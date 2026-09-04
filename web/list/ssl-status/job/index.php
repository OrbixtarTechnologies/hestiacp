<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "WEB";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (empty($_SESSION["WEB_SYSTEM"]) || ($panel[$user]["WEB_DOMAINS"] ?? "0") === "0") {
	header("Location: /dashboard/");
	exit();
}

$ssl_autossl_job_id = $_GET["job"] ?? "";
if (
	!is_string($ssl_autossl_job_id) ||
	!preg_match('/^[0-9]{14}-[0-9]+-[0-9]+$/', $ssl_autossl_job_id)
) {
	$_SESSION["error_msg"] = _("Select a valid AutoSSL job.");
	header("Location: /list/ssl-status/");
	exit();
}

exec(
	HESTIA_CMD .
		"v-get-autossl-log " .
		quoteshellarg($user) .
		" " .
		quoteshellarg($ssl_autossl_job_id),
	$output,
	$return_var,
);
if ($return_var !== 0) {
	check_return_code($return_var, $output);
	header("Location: /list/ssl-status/");
	exit();
}
$ssl_autossl_job_log = implode("\n", $output);
unset($output);

render_page($user, $TAB, "list_autossl_job");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
