<?php
define("ORBIX_API_COMPATIBILITY", "WHM-API-1");
require_once dirname(__DIR__) . "/execute/bootstrap.php";

function orbix_whm_response(
	string $command,
	mixed $data,
	int $result = 1,
	string $reason = "OK",
	int $httpStatus = 200,
): never {
	http_response_code($httpStatus);
	echo json_encode(
		[
			"data" => $data,
			"metadata" => [
				"command" => $command,
				"reason" => $reason,
				"result" => $result,
				"version" => 1,
			],
		],
		JSON_UNESCAPED_SLASHES,
	);
	exit();
}

function orbix_whm_fail(string $command, string $message, int $httpStatus = 200): never {
	orbix_whm_response($command, null, 0, $message, $httpStatus);
}

function orbix_whm_bootstrap(string $command, array $commands, array $queryParams): array {
	if (!in_array($_SERVER["REQUEST_METHOD"] ?? "", ["GET", "HEAD"], true)) {
		header("Allow: GET, HEAD");
		orbix_whm_fail($command, "This compatibility function is read-only.", 405);
	}

	$config = orbix_uapi_read_config("WHM", $command);
	$credentials = orbix_compat_credentials("WHM", $command, "whm", "OrbixPanel WHM API");
	if (
		$credentials["user"] !== ($config["ROOT_USER"] ?? "admin") &&
		(int) ($config["API_SYSTEM"] ?? 0) < 2
	) {
		orbix_whm_fail($command, "User API access is disabled.", 403);
	}
	foreach (array_unique($commands) as $allowedCommand) {
		orbix_uapi_authorize("WHM", $command, $credentials, $allowedCommand);
	}

	orbix_uapi_allow_query_params("WHM", $command, array_merge(["api_version"], $queryParams));
	$version = orbix_uapi_query("WHM", $command, "api_version", "", 1);
	if ($version !== "1") {
		orbix_whm_fail($command, "Set api.version=1 for WHM API 1 compatibility.");
	}

	return $credentials;
}

function orbix_whm_account(string $user, array $details, string $defaultOwner): array {
	$date = (string) ($details["DATE"] ?? "");
	$timestamp = $date === "" ? 0 : strtotime($date);
	return [
		"user" => $user,
		"domain" => "",
		"email" => (string) ($details["CONTACT"] ?? ""),
		"owner" => (string) ($details["OWNER"] ?? $defaultOwner),
		"plan" => (string) ($details["PACKAGE"] ?? ""),
		"suspended" => ($details["SUSPENDED"] ?? "no") === "yes" ? 1 : 0,
		"suspendreason" => "",
		"diskused" => (float) ($details["U_DISK"] ?? 0),
		"disklimit" =>
			($details["DISK_QUOTA"] ?? "unlimited") === "unlimited"
				? 0
				: (float) $details["DISK_QUOTA"],
		"startdate" => $date,
		"unix_startdate" => $timestamp === false ? 0 : $timestamp,
		"theme" => "orbixpanel",
		"shell" => (string) ($details["SHELL"] ?? "nologin"),
	];
}
