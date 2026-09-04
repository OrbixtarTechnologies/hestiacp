<?php
$TAB = "DASHBOARD";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

$sys = [];
$services = [];
$users = [];
$rrd = [];

if ($_SESSION["userContext"] === "admin" && empty($_SESSION["look"])) {
	exec(HESTIA_CMD . "v-list-sys-info json", $output, $return_var);
	$sys = json_decode(implode("", $output), true) ?: [];
	unset($output, $return_var);

	exec(HESTIA_CMD . "v-list-sys-services json", $output, $return_var);
	$services = json_decode(implode("", $output), true) ?: [];
	unset($output, $return_var);

	exec(HESTIA_CMD . "v-list-users json", $output, $return_var);
	$users = json_decode(implode("", $output), true) ?: [];
	unset($output, $return_var);

	exec(HESTIA_CMD . "v-list-sys-rrd json", $output, $return_var);
	$rrd = json_decode(implode("", $output), true) ?: [];
	unset($output, $return_var);
}

render_page($user, $TAB, "dashboard");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
