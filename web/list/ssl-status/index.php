<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "WEB";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (empty($_SESSION["WEB_SYSTEM"]) || ($panel[$user]["WEB_DOMAINS"] ?? "0") === "0") {
	header("Location: /dashboard/");
	exit();
}

exec(HESTIA_CMD . "v-list-web-domains " . $user . " json", $output, $return_var);
$ssl_web_domains = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$ssl_web_domains = is_array($ssl_web_domains) ? $ssl_web_domains : [];
unset($output);

if (!empty($_POST["start_autossl"])) {
	verify_csrf($_POST);
}

exec(HESTIA_CMD . "v-list-autossl-jobs " . quoteshellarg($user) . " json", $output, $return_var);
$ssl_autossl_jobs = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$ssl_autossl_jobs = is_array($ssl_autossl_jobs) ? $ssl_autossl_jobs : [];
$ssl_autossl_jobs = array_slice($ssl_autossl_jobs, 0, 10, true);
unset($output);

$ssl_status_domains = [];
$ssl_status_summary = [
	"valid" => 0,
	"expiring" => 0,
	"expired" => 0,
	"unsecured" => 0,
	"unknown" => 0,
];
$ssl_status_now = time();
$ssl_status_warning_time = strtotime("+30 days", $ssl_status_now);

foreach ($ssl_web_domains as $domain => $details) {
	$certificate = [];
	$status = "unsecured";
	$expires_at = null;
	$days_remaining = null;
	$ssl_enabled = ($details["SSL"] ?? "no") === "yes";

	if ($ssl_enabled) {
		exec(
			HESTIA_CMD . "v-list-web-domain-ssl-status " . $user . " " . quoteshellarg($domain),
			$output,
			$return_var,
		);

		if ($return_var === 0) {
			$result = json_decode(implode("", $output), true);
			$certificate = is_array($result) ? $result[$domain] ?? [] : [];
		}
		unset($output);

		$expires_at = strtotime($certificate["NOT_AFTER"] ?? "") ?: null;
		if ($expires_at === null) {
			$status = "unknown";
		} elseif ($expires_at <= $ssl_status_now) {
			$status = "expired";
		} elseif ($expires_at <= $ssl_status_warning_time) {
			$status = "expiring";
		} else {
			$status = "valid";
		}

		if ($expires_at !== null) {
			$days_remaining = (int) floor(($expires_at - $ssl_status_now) / 86400);
		}
	}

	$ssl_status_summary[$status]++;
	$ssl_status_domains[$domain] = [
		"status" => $status,
		"expires_at" => $expires_at,
		"days_remaining" => $days_remaining,
		"issuer" => $certificate["ISSUER"] ?? "",
		"subject" => $certificate["SUBJECT"] ?? "",
		"aliases" => $certificate["ALIASES"] ?? "",
		"fingerprint" => $certificate["FINGERPRINT"] ?? "",
		"letsencrypt" => ($details["LETSENCRYPT"] ?? "no") === "yes",
		"suspended" => ($details["SUSPENDED"] ?? "no") === "yes",
	];
}

if (!empty($_POST["start_autossl"])) {
	$selected_domains = $_POST["domains"] ?? [];
	$selected_domains = is_array($selected_domains)
		? array_values(array_unique(array_filter($selected_domains, "is_string")))
		: [];
	$selected_domains = array_values(
		array_filter(
			$selected_domains,
			fn($domain) => array_key_exists($domain, $ssl_status_domains) &&
				!$ssl_status_domains[$domain]["suspended"] &&
				$ssl_status_domains[$domain]["status"] !== "valid",
		),
	);

	if (empty($selected_domains) || count($selected_domains) > 100) {
		$_SESSION["error_msg"] = _(
			"Select between 1 and 100 active websites that need certificate repair.",
		);
	} else {
		exec(
			HESTIA_CMD .
				"v-start-autossl-user " .
				quoteshellarg($user) .
				" " .
				quoteshellarg(implode(",", $selected_domains)),
			$output,
			$return_var,
		);
		if (
			$return_var === 0 &&
			preg_match('/^[0-9]{14}-[0-9]+-[0-9]+$/', trim($output[0] ?? ""))
		) {
			$_SESSION["ok_msg"] = _(
				"AutoSSL started. Certificate requests will run in the background.",
			);
		} else {
			check_return_code($return_var, $output);
		}
	}

	header("Location: /list/ssl-status/");
	exit();
}

render_page($user, $TAB, "list_ssl_status");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
