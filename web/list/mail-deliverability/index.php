<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "MAIL";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (empty($_SESSION["MAIL_SYSTEM"]) || ($panel[$user]["MAIL_DOMAINS"] ?? "0") === "0") {
	header("Location: /dashboard/");
	exit();
}

exec(HESTIA_CMD . "v-list-mail-domains " . $user . " json", $output, $return_var);
$deliverability_domains = $return_var === 0 ? json_decode(implode("", $output), true) : [];
$deliverability_domains = is_array($deliverability_domains) ? $deliverability_domains : [];
unset($output);

$deliverability_domain = $_GET["domain"] ?? array_key_first($deliverability_domains);
if (
	$deliverability_domain !== null &&
	!array_key_exists($deliverability_domain, $deliverability_domains)
) {
	http_response_code(400);
	$_SESSION["error_msg"] = _("Select a mail domain that belongs to this account.");
	$deliverability_domain = array_key_first($deliverability_domains);
}

$deliverability_result = [];
if ($deliverability_domain !== null) {
	exec(
		HESTIA_CMD .
			"v-check-mail-domain-dns " .
			$user .
			" " .
			quoteshellarg($deliverability_domain) .
			" json",
		$output,
		$return_var,
	);
	if ($return_var === 0) {
		$data = json_decode(implode("", $output), true);
		$deliverability_result = is_array($data) ? $data[$deliverability_domain] ?? [] : [];
	} else {
		check_return_code($return_var, $output);
	}
	unset($output);
}

$deliverability_checks = [
	"MX" => [
		"label" => _("Mail routing"),
		"record" => $deliverability_domain ?? "",
		"description" => _("Directs incoming messages to a mail server for this domain."),
	],
	"SPF" => [
		"label" => _("Authorized senders"),
		"record" => $deliverability_domain ?? "",
		"description" => _("Declares which servers may send mail for this domain."),
	],
	"DKIM" => [
		"label" => _("Message signing"),
		"record" => $deliverability_domain ? "mail._domainkey." . $deliverability_domain : "",
		"description" => _("Publishes the key used to verify signed outgoing messages."),
	],
	"DMARC" => [
		"label" => _("Authentication policy"),
		"record" => $deliverability_domain ? "_dmarc." . $deliverability_domain : "",
		"description" => _("Tells receiving providers how to handle authentication failures."),
	],
];

render_page($user, $TAB, "list_mail_deliverability");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
