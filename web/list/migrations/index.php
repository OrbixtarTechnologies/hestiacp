<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "MIGRATIONS";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] !== "admin" || !empty($_SESSION["look"])) {
	header("Location: /dashboard/");
	exit();
}

function migration_command_json(string $command): array {
	exec(HESTIA_CMD . $command, $output, $return_var);
	if ($return_var !== 0) {
		return [];
	}

	$data = json_decode(implode("", $output), true);
	return is_array($data) ? $data : [];
}

$migration_archives = migration_command_json("v-list-cpanel-backups json");

if (!empty($_POST["start_migration"])) {
	verify_csrf($_POST);
	$archive = $_POST["archive"] ?? "";
	$restore_mx = !empty($_POST["restore_mx"]) ? "yes" : "no";

	if (!array_key_exists($archive, $migration_archives)) {
		$_SESSION["error_msg"] = _(
			"Select a valid cPanel backup from the server backup directory.",
		);
	} else {
		exec(
			HESTIA_CMD .
				"v-start-cpanel-import " .
				quoteshellarg($archive) .
				" " .
				quoteshellarg($restore_mx),
			$output,
			$return_var,
		);
		if (
			$return_var === 0 &&
			preg_match('/^[0-9]{14}-[0-9]+-[0-9]+$/', trim($output[0] ?? ""))
		) {
			$_SESSION["ok_msg"] = _("The cPanel migration was queued for preflight checks.");
		} else {
			check_return_code($return_var, $output);
		}
	}

	header("Location: /list/migrations/");
	exit();
}

if (!empty($_POST["start_remote_transfer"])) {
	verify_csrf($_POST);
	$remote_host = trim($_POST["remote_host"] ?? "");
	$remote_port = trim($_POST["remote_port"] ?? "22");
	$remote_path = trim($_POST["remote_path"] ?? "");
	$host_fingerprint = trim($_POST["host_fingerprint"] ?? "");
	$identity = trim($_POST["identity"] ?? "id_ed25519");
	$restore_mx = !empty($_POST["remote_restore_mx"]) ? "yes" : "no";

	if (
		$remote_host === "" ||
		$remote_path === "" ||
		!preg_match('/^[0-9]{1,5}$/', $remote_port) ||
		!preg_match('/^SHA256:[A-Za-z0-9+\/]{43}$/', $host_fingerprint) ||
		!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$/', $identity)
	) {
		$_SESSION["error_msg"] = _(
			"Enter a source server, SSH port, remote backup path, SHA256 host key, and installed identity name.",
		);
	} else {
		exec(
			HESTIA_CMD .
				"v-start-cpanel-remote-transfer " .
				quoteshellarg($remote_host) .
				" " .
				quoteshellarg($remote_port) .
				" " .
				quoteshellarg($remote_path) .
				" " .
				quoteshellarg($host_fingerprint) .
				" " .
				quoteshellarg($identity) .
				" " .
				quoteshellarg($restore_mx),
			$output,
			$return_var,
		);
		if (
			$return_var === 0 &&
			preg_match('/^[0-9]{14}-[0-9]+-[0-9]+$/', trim($output[0] ?? ""))
		) {
			$_SESSION["ok_msg"] = _(
				"Remote transfer queued. Interrupted downloads can be resumed by submitting the same source again.",
			);
		} else {
			check_return_code($return_var, $output);
		}
	}

	header("Location: /list/migrations/");
	exit();
}

$migration_jobs = migration_command_json("v-list-cpanel-imports json");
$migration_jobs = array_slice($migration_jobs, 0, 25, true);

render_page($user, $TAB, "list_migrations");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
