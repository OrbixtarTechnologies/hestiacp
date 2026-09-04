<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "RESELLER";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

$reseller = $_SESSION["user"];
$reseller_shell = quoteshellarg($reseller);

exec(HESTIA_CMD . "v-list-user " . $reseller_shell . " json", $output, $return_var);
$reseller_data = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$reseller_profile = $reseller_data[$reseller] ?? [];
unset($output);

if (
	$_SESSION["userContext"] !== "user" ||
	!empty($_SESSION["look"]) ||
	($reseller_profile["RESELLER"] ?? "no") !== "yes"
) {
	header("Location: /dashboard/");
	exit();
}

$allowed_package_names = array_values(
	array_filter(explode(",", $reseller_profile["RESELLER_PACKAGES"] ?? "")),
);

if (!empty($_POST["action"])) {
	verify_csrf($_POST);
	$action = $_POST["action"];

	if ($action === "create") {
		$required = ["v_username", "v_password", "v_email", "v_package", "v_name"];
		foreach ($required as $field) {
			if (empty($_POST[$field])) {
				$_SESSION["error_msg"] = _(
					"Complete every account field before creating the customer.",
				);
				break;
			}
		}

		if (
			empty($_SESSION["error_msg"]) &&
			!preg_match('/^[A-Za-z][A-Za-z0-9_-]{1,28}[A-Za-z0-9]$/', $_POST["v_username"])
		) {
			$_SESSION["error_msg"] = _(
				"Use 3–30 letters, numbers, dashes, or underscores for the username.",
			);
		}
		if (
			empty($_SESSION["error_msg"]) &&
			!filter_var($_POST["v_email"], FILTER_VALIDATE_EMAIL)
		) {
			$_SESSION["error_msg"] = _("Enter a valid customer email address.");
		}
		if (empty($_SESSION["error_msg"]) && !validate_password($_POST["v_password"])) {
			$_SESSION["error_msg"] = _("Password does not match the minimum requirements.");
		}
		if (
			empty($_SESSION["error_msg"]) &&
			!in_array($_POST["v_package"], $allowed_package_names, true)
		) {
			$_SESSION["error_msg"] = _(
				"This hosting package is not available to your reseller account.",
			);
		}

		if (empty($_SESSION["error_msg"])) {
			$password_file = tempnam("/tmp", "orbix-reseller-");
			file_put_contents($password_file, $_POST["v_password"] . "\n");
			exec(
				HESTIA_CMD .
					"v-add-reseller-user " .
					$reseller_shell .
					" " .
					quoteshellarg($_POST["v_username"]) .
					" " .
					quoteshellarg($password_file) .
					" " .
					quoteshellarg($_POST["v_email"]) .
					" " .
					quoteshellarg($_POST["v_package"]) .
					" " .
					quoteshellarg($_POST["v_name"]),
				$output,
				$return_var,
			);
			unlink($password_file);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$_SESSION["ok_msg"] = sprintf(
					_("Customer account %s was created."),
					$_POST["v_username"],
				);
			}
		}
	} elseif (in_array($action, ["suspend", "resume"], true)) {
		$managed_user = $_POST["user"] ?? "";
		if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]{1,28}[A-Za-z0-9]$/', $managed_user)) {
			$_SESSION["error_msg"] = _("The selected customer account is invalid.");
		} else {
			$command = "v-change-reseller-user-status";
			$arguments = $reseller_shell . " " . quoteshellarg($managed_user);
			$arguments .= " " . quoteshellarg($action === "suspend" ? "yes" : "no");
			exec(HESTIA_CMD . $command . " " . $arguments, $output, $return_var);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$message = match ($action) {
					"suspend" => _("Customer account suspended."),
					"resume" => _("Customer account resumed."),
					default => _("Customer account resumed."),
				};
				$_SESSION["ok_msg"] = $message;
			}
		}
	}

	header("Location: /list/reseller/");
	exit();
}

exec(HESTIA_CMD . "v-list-reseller-users " . $reseller_shell . " json", $output, $return_var);
$reseller_users = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$reseller_users = is_array($reseller_users) ? $reseller_users : [];
unset($output);

exec(HESTIA_CMD . "v-list-user-packages json", $output, $return_var);
$all_packages = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$reseller_packages = array_intersect_key(
	is_array($all_packages) ? $all_packages : [],
	array_flip($allowed_package_names),
);
unset($output);

render_page($user, $TAB, "list_reseller");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
