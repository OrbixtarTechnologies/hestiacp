<?php
$hosting_disk_limit = ($hosting_account["DISK_QUOTA"] ?? "unlimited") === "unlimited"
	? "∞"
	: humanize_usage_size($hosting_account["DISK_QUOTA"]) . " " . humanize_usage_measure($hosting_account["DISK_QUOTA"]);
$hosting_bandwidth_limit = ($hosting_account["BANDWIDTH"] ?? "unlimited") === "unlimited"
	? "∞"
	: humanize_usage_size($hosting_account["BANDWIDTH"]) . " " . humanize_usage_measure($hosting_account["BANDWIDTH"]);
?>

<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Account operations")) ?></p>
			<h1><?= tohtml(_("Hosting Overview")) ?></h1>
			<p><?= tohtml(_("Manage the services, content, automation, and recovery points attached to this hosting account.")) ?></p>
		</div>
		<a class="button button-primary" href="/add/web/">
			<i class="fas fa-plus"></i><?= tohtml(_("Add a Website")) ?>
		</a>
	</header>

	<section aria-labelledby="hosting-services-title">
		<div class="orbix-section-heading">
			<div>
				<p class="orbix-page-eyebrow"><?= tohtml(_("At a glance")) ?></p>
				<h2 id="hosting-services-title"><?= tohtml(_("Hosted services")) ?></h2>
			</div>
		</div>
		<div class="orbix-action-grid">
			<a class="orbix-action-card" href="/list/web/">
				<i class="fas fa-earth-americas"></i>
				<span><strong><?= tohtml(_("Websites")) ?></strong><small><?= tohtml(sprintf(ngettext("%d website", "%d websites", $hosting_summary["websites"]), $hosting_summary["websites"])) ?> · <?= tohtml(sprintf(ngettext("%d with SSL", "%d with SSL", $hosting_summary["ssl_domains"]), $hosting_summary["ssl_domains"])) ?></small></span>
				<i class="fas fa-chevron-right"></i>
			</a>
			<a class="orbix-action-card" href="/list/mail/">
				<i class="fas fa-envelope"></i>
				<span><strong><?= tohtml(_("Email")) ?></strong><small><?= tohtml(sprintf(ngettext("%d domain", "%d domains", $hosting_summary["mail_domains"]), $hosting_summary["mail_domains"])) ?> · <?= tohtml(sprintf(ngettext("%d mailbox", "%d mailboxes", $hosting_summary["mail_accounts"]), $hosting_summary["mail_accounts"])) ?></small></span>
				<i class="fas fa-chevron-right"></i>
			</a>
			<a class="orbix-action-card" href="/list/dns/">
				<i class="fas fa-location-dot"></i>
				<span><strong><?= tohtml(_("DNS zones")) ?></strong><small><?= tohtml(sprintf(ngettext("%d managed zone", "%d managed zones", $hosting_summary["dns_zones"]), $hosting_summary["dns_zones"])) ?></small></span>
				<i class="fas fa-chevron-right"></i>
			</a>
			<a class="orbix-action-card" href="/list/db/">
				<i class="fas fa-database"></i>
				<span><strong><?= tohtml(_("Databases")) ?></strong><small><?= tohtml(sprintf(ngettext("%d database", "%d databases", $hosting_summary["databases"]), $hosting_summary["databases"])) ?></small></span>
				<i class="fas fa-chevron-right"></i>
			</a>
		</div>
	</section>

	<div class="orbix-admin-overview">
		<section aria-labelledby="hosting-workflows-title">
			<div class="orbix-section-heading"><h2 id="hosting-workflows-title"><?= tohtml(_("Common workflows")) ?></h2></div>
			<div class="orbix-favorites-grid">
				<a class="orbix-tool-card" href="/fm/"><i class="fas fa-folder-open"></i><span><strong><?= tohtml(_("File Manager")) ?></strong><small><?= tohtml(_("Upload, edit, move, and protect website files.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/ssl-status/"><i class="fas fa-lock"></i><span><strong><?= tohtml(_("SSL/TLS Status")) ?></strong><small><?= tohtml(_("Review certificates and HTTPS coverage.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/backup/"><i class="fas fa-clock-rotate-left"></i><span><strong><?= tohtml(_("Backup Manager")) ?></strong><small><?= tohtml(sprintf(ngettext("%d recovery point available", "%d recovery points available", $hosting_summary["backups"]), $hosting_summary["backups"])) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/cron/"><i class="fas fa-clock"></i><span><strong><?= tohtml(_("Scheduled Tasks")) ?></strong><small><?= tohtml(sprintf(ngettext("%d active task", "%d active tasks", $hosting_summary["cron_jobs"]), $hosting_summary["cron_jobs"])) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/mail-deliverability/"><i class="fas fa-shield-halved"></i><span><strong><?= tohtml(_("Email Deliverability")) ?></strong><small><?= tohtml(_("Check public MX, SPF, DKIM, and DMARC records.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/stats/"><i class="fas fa-chart-column"></i><span><strong><?= tohtml(_("Usage Reports")) ?></strong><small><?= tohtml(_("Inspect account traffic and resource history.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/edit/user/?<?= tohtml(http_build_query(["user" => $user_plain, "token" => $_SESSION["token"]])) ?>"><i class="fas fa-user-shield"></i><span><strong><?= tohtml(_("Account Security")) ?></strong><small><?= tohtml(_("Update credentials, contact details, and two-factor authentication.")) ?></small></span></a>
			</div>
		</section>

		<aside class="orbix-statistics-panel" aria-labelledby="hosting-usage-title">
			<h2 id="hosting-usage-title"><?= tohtml(_("Account usage")) ?></h2>
			<dl>
				<div><dt><?= tohtml(_("Disk")) ?></dt><dd><?= humanize_usage_size($hosting_account["U_DISK"] ?? 0) ?> <?= humanize_usage_measure($hosting_account["U_DISK"] ?? 0) ?> / <?= tohtml($hosting_disk_limit) ?></dd></div>
				<div><dt><?= tohtml(_("Bandwidth")) ?></dt><dd><?= humanize_usage_size($hosting_account["U_BANDWIDTH"] ?? 0) ?> <?= humanize_usage_measure($hosting_account["U_BANDWIDTH"] ?? 0) ?> / <?= tohtml($hosting_bandwidth_limit) ?></dd></div>
				<div><dt><?= tohtml(_("Websites")) ?></dt><dd><?= tohtml($hosting_summary["websites"]) ?></dd></div>
				<div><dt><?= tohtml(_("Mailboxes")) ?></dt><dd><?= tohtml($hosting_summary["mail_accounts"]) ?></dd></div>
				<div><dt><?= tohtml(_("Databases")) ?></dt><dd><?= tohtml($hosting_summary["databases"]) ?></dd></div>
			</dl>
			<a class="button button-secondary" href="/list/stats/"><i class="fas fa-chart-line"></i><?= tohtml(_("Open Usage Reports")) ?></a>
		</aside>
	</div>
</div>
