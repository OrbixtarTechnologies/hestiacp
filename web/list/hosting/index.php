<?php
$TAB = "HOSTING";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

function hosting_command_json(string $command): array {
	exec(HESTIA_CMD . $command, $output, $return_var);
	if ($return_var !== 0) {
		return [];
	}

	$data = json_decode(implode("", $output), true);
	return is_array($data) ? $data : [];
}

$hosting_websites =
	!empty($_SESSION["WEB_SYSTEM"]) && ($panel[$user]["WEB_DOMAINS"] ?? "0") !== "0"
		? hosting_command_json("v-list-web-domains " . $user . " json")
		: [];
$hosting_dns_zones =
	!empty($_SESSION["DNS_SYSTEM"]) && ($panel[$user]["DNS_DOMAINS"] ?? "0") !== "0"
		? hosting_command_json("v-list-dns-domains " . $user . " json")
		: [];
$hosting_mail_domains =
	!empty($_SESSION["MAIL_SYSTEM"]) && ($panel[$user]["MAIL_DOMAINS"] ?? "0") !== "0"
		? hosting_command_json("v-list-mail-domains " . $user . " json")
		: [];
$hosting_databases =
	!empty($_SESSION["DB_SYSTEM"]) && ($panel[$user]["DATABASES"] ?? "0") !== "0"
		? hosting_command_json("v-list-databases " . $user . " json")
		: [];
$hosting_cron_jobs =
	!empty($_SESSION["CRON_SYSTEM"]) && ($panel[$user]["CRON_JOBS"] ?? "0") !== "0"
		? hosting_command_json("v-list-cron-jobs " . $user . " json")
		: [];
$hosting_backups = !empty($_SESSION["BACKUP_SYSTEM"])
	? hosting_command_json("v-list-user-backups " . $user . " json")
	: [];

$hosting_mail_accounts = array_sum(
	array_map(
		fn($domain) => (int) ($domain["ACCOUNTS"] ?? ($domain["U_ACCOUNTS"] ?? 0)),
		$hosting_mail_domains,
	),
);
$hosting_ssl_domains = count(
	array_filter(
		$hosting_websites,
		fn($domain) => ($domain["SSL"] ?? "no") === "yes" ||
			($domain["SSL_HOME"] ?? "no") === "yes",
	),
);
$hosting_account = $panel[$user] ?? [];
$hosting_summary = [
	"websites" => count($hosting_websites),
	"ssl_domains" => $hosting_ssl_domains,
	"dns_zones" => count($hosting_dns_zones),
	"mail_domains" => count($hosting_mail_domains),
	"mail_accounts" => $hosting_mail_accounts,
	"databases" => count($hosting_databases),
	"cron_jobs" => count($hosting_cron_jobs),
	"backups" => count($hosting_backups),
];

render_page($user, $TAB, "list_hosting");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
