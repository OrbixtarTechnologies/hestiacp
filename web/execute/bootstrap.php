<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

require_once dirname(__DIR__) . "/inc/vendor/autoload.php";
require_once dirname(__DIR__) . "/inc/helpers.php";

define("ORBIX_UAPI_CMD", "/usr/bin/sudo /usr/local/hestia/bin/");

header("Content-Type: application/json; charset=utf-8");
header(
	"X-OrbixPanel-Compatibility: " .
		(defined("ORBIX_API_COMPATIBILITY") ? ORBIX_API_COMPATIBILITY : "cPanel-UAPI-3"),
);

if (basename($_SERVER["SCRIPT_FILENAME"] ?? "") === "bootstrap.php") {
	http_response_code(404);
	exit();
}

function orbix_uapi_response(
	string $module,
	string $function,
	mixed $data,
	int $status = 1,
	?array $errors = null,
	int $httpStatus = 200,
): never {
	http_response_code($httpStatus);
	echo json_encode(
		[
			"apiversion" => 3,
			"func" => $function,
			"module" => $module,
			"result" => [
				"data" => $data,
				"errors" => $errors,
				"messages" => null,
				"metadata" => (object) [],
				"status" => $status,
				"warnings" => null,
			],
		],
		JSON_UNESCAPED_SLASHES,
	);
	exit();
}

function orbix_uapi_fail(
	string $module,
	string $function,
	string $message,
	int $httpStatus = 200,
): never {
	orbix_uapi_response($module, $function, null, 0, [$message], $httpStatus);
}

function orbix_compat_fail(
	string $module,
	string $function,
	string $message,
	int $httpStatus = 200,
): never {
	if (
		defined("ORBIX_API_COMPATIBILITY") &&
		ORBIX_API_COMPATIBILITY === "WHM-API-1" &&
		function_exists("orbix_whm_fail")
	) {
		orbix_whm_fail($function, $message, $httpStatus);
	}
	orbix_uapi_fail($module, $function, $message, $httpStatus);
}

function orbix_uapi_client_ip(): string {
	return get_real_user_ip();
}

function orbix_uapi_read_config(string $module, string $function): array {
	exec(ORBIX_UAPI_CMD . "v-list-sys-config json", $output, $returnCode);
	$config = $returnCode === 0 ? json_decode(implode("", $output), true) : [];
	$config = $config["config"] ?? [];
	if (($config["API_SYSTEM"] ?? "0") === "0") {
		orbix_compat_fail($module, $function, "API access is disabled.", 403);
	}
	$allowedIps = $config["API_ALLOWED_IP"] ?? "";
	if ($allowedIps !== "allow-all") {
		$allowList = array_filter(explode(",", $allowedIps));
		if (
			!in_array(orbix_uapi_client_ip(), $allowList, true) &&
			!in_array("0.0.0.0", $allowList, true)
		) {
			orbix_compat_fail(
				$module,
				$function,
				"This IP address is not allowed to use the API.",
				403,
			);
		}
	}
	return $config;
}

function orbix_compat_credentials(
	string $module,
	string $function,
	string $scheme,
	string $realm,
): array {
	$authorization = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
	if (
		!preg_match(
			"/^" .
				preg_quote($scheme, "/") .
				' ([A-Za-z][A-Za-z0-9_-]{0,29}):([A-Za-z0-9]{20})\.([A-Za-z0-9_=-]{40})$/i',
			$authorization,
			$matches,
		)
	) {
		header("WWW-Authenticate: " . $scheme . ' realm="' . $realm . '"');
		orbix_compat_fail(
			$module,
			$function,
			"Use Authorization: " . $scheme . " USER:ACCESS_KEY.SECRET_KEY.",
			401,
		);
	}
	return ["user" => $matches[1], "access_key" => $matches[2], "secret_key" => $matches[3]];
}

function orbix_uapi_credentials(string $module, string $function): array {
	return orbix_compat_credentials($module, $function, "cpanel", "OrbixPanel UAPI");
}

function orbix_uapi_authorize(
	string $module,
	string $function,
	array $credentials,
	string $command,
): void {
	exec(
		ORBIX_UAPI_CMD .
			"v-check-access-key " .
			quoteshellarg($credentials["access_key"]) .
			" " .
			quoteshellarg($credentials["secret_key"]) .
			" " .
			quoteshellarg($command) .
			" " .
			quoteshellarg(orbix_uapi_client_ip()) .
			" json",
		$output,
		$returnCode,
	);
	$keyData = $returnCode === 0 ? json_decode(implode("", $output), true) : [];
	if ($returnCode !== 0 || ($keyData["USER"] ?? "") !== $credentials["user"]) {
		orbix_compat_fail($module, $function, "Authentication or command permission failed.", 403);
	}
}

function orbix_uapi_bootstrap(string $module, string $function, array $commands): array {
	if (!in_array($_SERVER["REQUEST_METHOD"] ?? "", ["GET", "HEAD"], true)) {
		header("Allow: GET, HEAD");
		orbix_compat_fail($module, $function, "This compatibility function is read-only.", 405);
	}
	$config = orbix_uapi_read_config($module, $function);
	$credentials = orbix_uapi_credentials($module, $function);
	if (
		$credentials["user"] !== ($config["ROOT_USER"] ?? "admin") &&
		(int) ($config["API_SYSTEM"] ?? 0) < 2
	) {
		orbix_compat_fail($module, $function, "User API access is disabled.", 403);
	}
	foreach (array_unique($commands) as $command) {
		orbix_uapi_authorize($module, $function, $credentials, $command);
	}
	return $credentials;
}

function orbix_uapi_query(
	string $module,
	string $function,
	string $name,
	string $default = "",
	int $maxLength = 253,
): string {
	$value = $_GET[$name] ?? $default;
	if (!is_string($value) || strlen($value) > $maxLength || preg_match("/[[:cntrl:]]/", $value)) {
		orbix_compat_fail($module, $function, "Invalid query parameter: " . $name . ".");
	}
	return $value;
}

function orbix_uapi_allow_query_params(string $module, string $function, array $allowed): void {
	foreach (array_keys($_GET) as $name) {
		if (!is_string($name) || !in_array($name, $allowed, true)) {
			orbix_compat_fail(
				$module,
				$function,
				"Unsupported query parameter: " . (is_string($name) ? $name : "unknown") . ".",
			);
		}
	}
}

function orbix_uapi_command(
	string $module,
	string $function,
	string $command,
	array $arguments,
): array {
	$query = ORBIX_UAPI_CMD . $command;
	foreach ($arguments as $argument) {
		$query .= " " . quoteshellarg((string) $argument);
	}
	exec($query, $output, $returnCode);
	if ($returnCode !== 0) {
		orbix_compat_fail(
			$module,
			$function,
			implode("\n", $output) ?: "OrbixPanel command failed.",
		);
	}
	$data = json_decode(implode("", $output), true);
	if (!is_array($data)) {
		orbix_compat_fail($module, $function, "OrbixPanel returned an invalid response.");
	}
	return $data;
}
