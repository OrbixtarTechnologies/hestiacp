<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "RUNTIME";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] !== "admin" || !empty($_SESSION["look"])) {
	header("Location: /dashboard/");
	exit();
}

function runtime_profile_command_json(string $command): array {
	exec(HESTIA_CMD . $command, $output, $return_var);
	if ($return_var !== 0) {
		return [];
	}
	$data = json_decode(implode("", $output), true);
	return is_array($data) ? $data : [];
}

$runtime_supported = [
	"5.6",
	"7.0",
	"7.1",
	"7.2",
	"7.3",
	"7.4",
	"8.0",
	"8.1",
	"8.2",
	"8.3",
	"8.4",
	"8.5",
];
$runtime_installed = runtime_profile_command_json("v-list-sys-php json");
$runtime_defaults = runtime_profile_command_json("v-list-default-php json");
$runtime_default = (string) ($runtime_defaults[0] ?? "");
$runtime_backend_usage = backendtpl_with_webdomains();
$runtime_components = [];

foreach ($runtime_supported as $version) {
	$template = "PHP-" . str_replace(".", "_", $version);
	$used_by = $runtime_backend_usage[$template] ?? [];
	$domains = [];
	foreach ($used_by as $owner => $owner_domains) {
		foreach ($owner_domains as $domain) {
			$domains[] = ["owner" => $owner, "domain" => $domain];
		}
	}

	$extensions = [];
	if (in_array($version, $runtime_installed, true)) {
		$extensions = runtime_profile_command_json(
			"v-list-sys-php-extensions " . quoteshellarg($version) . " json",
		);
	}

	$runtime_components[$version] = [
		"installed" => in_array($version, $runtime_installed, true),
		"default" => $version === $runtime_default,
		"domains" => $domains,
		"extensions" => $extensions,
	];
}

if (!empty($_POST["apply_profile"])) {
	verify_csrf($_POST);
	$selected = $_POST["versions"] ?? [];
	$selected = is_array($selected)
		? array_values(array_unique(array_filter($selected, "is_string")))
		: [];
	$selected = array_values(
		array_filter($selected, fn($version) => in_array($version, $runtime_supported, true)),
	);
	$default = is_string($_POST["default_version"] ?? null) ? $_POST["default_version"] : "";
	$confirmed = ($_POST["confirm_profile"] ?? "") === "yes";

	if (
		empty($selected) ||
		!in_array($default, $selected, true) ||
		count($selected) > count($runtime_supported) ||
		!$confirmed
	) {
		$_SESSION["error_msg"] = _(
			"Select at least one runtime, keep the default selected, and confirm the build plan.",
		);
	} else {
		sort($selected, SORT_NATURAL);
		exec(
			HESTIA_CMD .
				"v-start-runtime-profile " .
				quoteshellarg(implode(",", $selected)) .
				" " .
				quoteshellarg($default),
			$output,
			$return_var,
		);
		if (
			$return_var === 0 &&
			preg_match('/^[0-9]{14}-[0-9]+-[0-9]+$/', trim($output[0] ?? ""))
		) {
			$_SESSION["ok_msg"] = _(
				"Runtime profile queued. Installation, default switching, and safe removals will run in order.",
			);
		} else {
			check_return_code($return_var, $output);
		}
	}

	header("Location: /list/runtime-profiles/");
	exit();
}

$runtime_jobs = runtime_profile_command_json("v-list-runtime-profile-jobs json");
$runtime_jobs = array_slice($runtime_jobs, 0, 10, true);
$runtime_profile_supported = $_SESSION["WEB_BACKEND"] === "php-fpm";

render_page($user, $TAB, "list_runtime_profiles");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
