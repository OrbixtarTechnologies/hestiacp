<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "FLEET";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] !== "admin" || !empty($_SESSION["look"])) {
	header("Location: /dashboard/");
	exit();
}

function fleet_command_json(string $command): array {
	exec(HESTIA_CMD . $command, $output, $return_var);
	if ($return_var !== 0) {
		return [];
	}
	$data = json_decode(implode("", $output), true);
	return is_array($data) ? $data : [];
}

$fleet_nodes = fleet_command_json("v-list-fleet-nodes json");

if (!empty($_POST["add_node"])) {
	verify_csrf($_POST);
	$name = strtolower(trim($_POST["name"] ?? ""));
	$host = trim($_POST["host"] ?? "");
	$port = trim($_POST["port"] ?? "8083");
	$tls_pin = trim($_POST["tls_pin"] ?? "");
	$access_key = trim($_POST["access_key"] ?? "");
	$secret_key = trim($_POST["secret_key"] ?? "");

	if (
		!preg_match('/^(?!all$)[a-z0-9][a-z0-9-]{0,31}$/', $name) ||
		!preg_match('/^[A-Za-z0-9][A-Za-z0-9.-]{0,252}$/', $host) ||
		!preg_match('/^[0-9]{1,5}$/', $port) ||
		(int) $port < 1 ||
		(int) $port > 65535 ||
		!preg_match('#^sha256//[A-Za-z0-9+/]{43}=$#', $tls_pin) ||
		!preg_match('/^[A-Za-z0-9]{20}$/', $access_key) ||
		!preg_match('/^[A-Za-z0-9_=-]{40}$/', $secret_key)
	) {
		$_SESSION["error_msg"] = _(
			"Enter a node name, HTTPS host and port, SHA256 public-key pin, and valid fleet access key.",
		);
	} else {
		$secret_handle = tmpfile();
		$secret_path = $secret_handle ? stream_get_meta_data($secret_handle)["uri"] : "";
		if (!$secret_handle || fwrite($secret_handle, $secret_key . "\n") === false) {
			if ($secret_handle) {
				fclose($secret_handle);
			}
			$_SESSION["error_msg"] = _("The fleet credential could not be prepared securely.");
			header("Location: /list/fleet/");
			exit();
		}
		fflush($secret_handle);
		exec(
			HESTIA_CMD .
				"v-add-fleet-node " .
				quoteshellarg($name) .
				" " .
				quoteshellarg($host) .
				" " .
				quoteshellarg($port) .
				" " .
				quoteshellarg($tls_pin) .
				" " .
				quoteshellarg($access_key) .
				" " .
				quoteshellarg($secret_path),
			$output,
			$return_var,
		);
		fclose($secret_handle);
		if ($return_var === 0) {
			$_SESSION["ok_msg"] = _(
				"Fleet node verified and registered. Its credential is stored in the root-only fleet registry.",
			);
		} else {
			check_return_code($return_var, $output);
		}
	}
	header("Location: /list/fleet/");
	exit();
}

if (!empty($_POST["refresh_nodes"])) {
	verify_csrf($_POST);
	$target = $_POST["node"] ?? "all";
	if ($target !== "all" && !array_key_exists($target, $fleet_nodes)) {
		$_SESSION["error_msg"] = _("Select a registered fleet node to refresh.");
	} else {
		exec(HESTIA_CMD . "v-start-fleet-refresh " . quoteshellarg($target), $output, $return_var);
		if (
			$return_var === 0 &&
			preg_match('/^[0-9]{14}-[0-9]+-[0-9]+$/', trim($output[0] ?? ""))
		) {
			$_SESSION["ok_msg"] = _(
				"Fleet refresh queued. Each node will be checked with its pinned TLS identity.",
			);
		} else {
			check_return_code($return_var, $output);
		}
	}
	header("Location: /list/fleet/");
	exit();
}

if (!empty($_POST["delete_node"])) {
	verify_csrf($_POST);
	$target = $_POST["node"] ?? "";
	$confirmed = ($_POST["confirm_delete"] ?? "") === "yes";
	if (!$confirmed || !array_key_exists($target, $fleet_nodes)) {
		$_SESSION["error_msg"] = _("Confirm a registered fleet node to remove.");
	} else {
		exec(HESTIA_CMD . "v-delete-fleet-node " . quoteshellarg($target), $output, $return_var);
		if ($return_var === 0) {
			$_SESSION["ok_msg"] = _("Fleet node and its cached health were removed.");
		} else {
			check_return_code($return_var, $output);
		}
	}
	header("Location: /list/fleet/");
	exit();
}

$fleet_jobs = fleet_command_json("v-list-fleet-refresh-jobs json");
$fleet_jobs = array_slice($fleet_jobs, 0, 5, true);

render_page($user, $TAB, "list_fleet");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
