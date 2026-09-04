<?php
$TAB = "SERVER_HEALTH";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] !== "admin" || !empty($_SESSION["look"])) {
	header("Location: /dashboard/");
	exit();
}

function server_health_command_json(string $command): array {
	exec(HESTIA_CMD . $command, $output, $return_var);
	if ($return_var !== 0) {
		return [];
	}

	$data = json_decode(implode("", $output), true);
	return is_array($data) ? $data : [];
}

$server_health_system = server_health_command_json("v-list-sys-info json")["sysinfo"] ?? [];
$server_health_capacity =
	server_health_command_json("v-list-sys-health-summary json")["health"] ?? [];
$server_health_services = server_health_command_json("v-list-sys-services json");

$server_health_stopped = array_filter(
	$server_health_services,
	fn($service) => ($service["STATE"] ?? "stopped") !== "running",
);
$server_health_running = count($server_health_services) - count($server_health_stopped);
$server_health_disk_percent = min(
	100,
	max(0, (int) ($server_health_capacity["DISK_PERCENT"] ?? 0)),
);
$server_health_memory_percent = min(
	100,
	max(0, (int) ($server_health_capacity["MEMORY_PERCENT"] ?? 0)),
);
$server_health_load_one = (float) ($server_health_capacity["LOAD_ONE"] ?? 0);
$server_health_cpu_cores = max(1, (int) ($server_health_capacity["CPU_CORES"] ?? 1));
$server_health_load_percent = min(
	100,
	max(0, (int) round(($server_health_load_one / $server_health_cpu_cores) * 100)),
);
$server_health_needs_attention =
	empty($server_health_services) ||
	count($server_health_stopped) > 0 ||
	$server_health_disk_percent >= 85 ||
	$server_health_memory_percent >= 90 ||
	$server_health_load_percent >= 90;

render_page($user, $TAB, "list_server_health");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
