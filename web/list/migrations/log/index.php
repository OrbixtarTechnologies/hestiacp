<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "MIGRATIONS";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] !== "admin" || !empty($_SESSION["look"])) {
	header("Location: /dashboard/");
	exit();
}

$migration_job = $_GET["job"] ?? "";
if (!preg_match('/^[0-9]{14}-[0-9]+-[0-9]+$/', $migration_job)) {
	header("Location: /list/migrations/");
	exit();
}

exec(
	HESTIA_CMD . "v-get-cpanel-import-log " . quoteshellarg($migration_job),
	$migration_log_lines,
	$return_var,
);
if ($return_var !== 0) {
	check_return_code($return_var, $migration_log_lines);
	$migration_log_lines = [];
}

render_page($user, $TAB, "list_migration_log");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
