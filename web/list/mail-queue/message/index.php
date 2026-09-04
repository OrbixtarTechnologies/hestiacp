<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

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

$mail_queue_message_id = $_POST["message_id"] ?? ($_GET["id"] ?? "");
if (
	!is_string($mail_queue_message_id) ||
	!preg_match('/^[A-Za-z0-9]{6,}-[A-Za-z0-9]{6,}-[A-Za-z0-9]{2,}$/', $mail_queue_message_id)
) {
	$_SESSION["error_msg"] = _("Select a valid queued message.");
	header("Location: /list/mail-queue/");
	exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	verify_csrf($_POST);
	$action = $_POST["action"] ?? "";
	$command = match ($action) {
		"retry" => "v-retry-sys-mail-queue-message",
		"delete" => "v-delete-sys-mail-queue-message",
		default => "",
	};

	if ($command === "") {
		$_SESSION["error_msg"] = _("Select a valid mail queue action.");
	} else {
		exec(
			HESTIA_CMD . $command . " " . quoteshellarg($mail_queue_message_id),
			$output,
			$return_var,
		);
		if ($return_var === 0) {
			$_SESSION["ok_msg"] =
				$action === "retry"
					? _("The queued message was submitted for another delivery attempt.")
					: _("The queued message was deleted.");
		} else {
			check_return_code($return_var, $output);
		}
	}

	header("Location: /list/mail-queue/");
	exit();
}

exec(
	HESTIA_CMD . "v-get-sys-mail-queue-message " . quoteshellarg($mail_queue_message_id),
	$output,
	$return_var,
);
if ($return_var !== 0) {
	check_return_code($return_var, $output);
	header("Location: /list/mail-queue/");
	exit();
}
$mail_queue_message_detail = implode("\n", $output);
unset($output);

render_page($user, $TAB, "list_mail_queue_message");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
