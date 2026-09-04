<?php
$TAB = "MAIL_QUEUE";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (
	$_SESSION["userContext"] !== "admin" ||
	!empty($_SESSION["look"]) ||
	empty($_SESSION["MAIL_SYSTEM"]) ||
	$_SESSION["MAIL_SYSTEM"] === "remote"
) {
	header("Location: /dashboard/");
	exit();
}

exec(HESTIA_CMD . "v-list-sys-mail-queue json", $output, $return_var);
$mail_queue_data = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$mail_queue_data = is_array($mail_queue_data) ? $mail_queue_data : [];
if ($return_var !== 0) {
	check_return_code($return_var, $output);
}
unset($output);

$mail_queue_summary = $mail_queue_data["summary"] ?? [
	"TOTAL" => 0,
	"RETURNED" => 0,
	"TRUNCATED" => "no",
];
$mail_queue_messages = $mail_queue_data["messages"] ?? [];
$mail_queue_messages = is_array($mail_queue_messages) ? $mail_queue_messages : [];
$mail_queue_frozen = count(
	array_filter($mail_queue_messages, fn($message) => ($message["FROZEN"] ?? "no") === "yes"),
);

render_page($user, $TAB, "list_mail_queue");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
