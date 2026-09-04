<?php
$is_admin_dashboard = $_SESSION["userContext"] === "admin" && empty($_SESSION["look"]);
$account_name = $panel[$user]["NAME"] ?: $user_plain;
$disk_limit = $panel[$user]["DISK_QUOTA"] === "unlimited" ? "∞" : humanize_usage_size($panel[$user]["DISK_QUOTA"]) . " " . humanize_usage_measure($panel[$user]["DISK_QUOTA"]);
$bandwidth_limit = $panel[$user]["BANDWIDTH"] === "unlimited" ? "∞" : humanize_usage_size($panel[$user]["BANDWIDTH"]) . " " . humanize_usage_measure($panel[$user]["BANDWIDTH"]);
?>

<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= $is_admin_dashboard ? tohtml(_("Server administration")) : tohtml(_("Account control panel")) ?></p>
			<h1><?= $is_admin_dashboard ? tohtml(_("Server Manager")) : tohtml(_("Tools")) ?></h1>
			<p><?= $is_admin_dashboard ? tohtml(_("Manage accounts, services, security, and server configuration.")) : tohtml(_("Manage your websites, email, files, databases, security, and account preferences.")) ?></p>
		</div>
		<?php if ($is_admin_dashboard) { ?>
			<a class="button button-primary" href="/add/user/">
				<i class="fas fa-plus"></i><?= tohtml(_("Create an Account")) ?>
			</a>
		<?php } ?>
	</header>

	<?php if ($is_admin_dashboard) { ?>
		<section class="orbix-next-steps" aria-labelledby="next-steps-title">
			<div class="orbix-section-heading">
				<div>
					<p class="orbix-page-eyebrow"><?= tohtml(_("Getting started")) ?></p>
					<h2 id="next-steps-title"><?= tohtml(_("Important next steps")) ?></h2>
				</div>
			</div>
			<div class="orbix-action-grid">
				<a class="orbix-action-card" href="/edit/server/">
					<i class="fas fa-sliders"></i>
					<span><strong><?= tohtml(_("Configure the server")) ?></strong><small><?= tohtml(_("Set hostname, nameservers, mail, DNS, and web service defaults.")) ?></small></span>
					<i class="fas fa-chevron-right"></i>
				</a>
				<a class="orbix-action-card" href="/add/ip/">
					<i class="fas fa-network-wired"></i>
					<span><strong><?= tohtml(_("Add an IP address")) ?></strong><small><?= tohtml(_("Configure addresses used by hosted websites and services.")) ?></small></span>
					<i class="fas fa-chevron-right"></i>
				</a>
				<a class="orbix-action-card" href="/list/package/">
					<i class="fas fa-box-open"></i>
					<span><strong><?= tohtml(_("Create hosting packages")) ?></strong><small><?= tohtml(_("Define account limits, quotas, and enabled capabilities.")) ?></small></span>
					<i class="fas fa-chevron-right"></i>
				</a>
				<a class="orbix-action-card" href="/list/firewall/">
					<i class="fas fa-shield-halved"></i>
					<span><strong><?= tohtml(_("Review server security")) ?></strong><small><?= tohtml(_("Inspect firewall rules, blocked hosts, and access controls.")) ?></small></span>
					<i class="fas fa-chevron-right"></i>
				</a>
			</div>
		</section>

		<div class="orbix-admin-overview">
			<section aria-labelledby="favorites-title">
				<div class="orbix-section-heading">
					<h2 id="favorites-title"><?= tohtml(_("Favorites")) ?></h2>
					<a href="/list/user/"><?= tohtml(_("View all accounts")) ?></a>
				</div>
				<div class="orbix-favorites-grid">
					<a class="orbix-tool-card" href="/list/user/"><i class="fas fa-users"></i><span><strong><?= tohtml(_("List Accounts")) ?></strong><small><?= tohtml(_("Review, suspend, and manage hosting accounts.")) ?></small></span></a>
					<a class="orbix-tool-card" href="/add/user/"><i class="fas fa-user-plus"></i><span><strong><?= tohtml(_("Create a New Account")) ?></strong><small><?= tohtml(_("Provision a hosting account and resource package.")) ?></small></span></a>
					<a class="orbix-tool-card" href="/list/server/"><i class="fas fa-server"></i><span><strong><?= tohtml(_("Service Manager")) ?></strong><small><?= tohtml(_("Inspect, start, stop, and restart services.")) ?></small></span></a>
					<a class="orbix-tool-card" href="/list/dns/"><i class="fas fa-globe"></i><span><strong><?= tohtml(_("DNS Zone Manager")) ?></strong><small><?= tohtml(_("Manage hosted DNS zones and records.")) ?></small></span></a>
					<a class="orbix-tool-card" href="/list/terminal/"><i class="fas fa-terminal"></i><span><strong><?= tohtml(_("Terminal")) ?></strong><small><?= tohtml(_("Open the secured server command console.")) ?></small></span></a>
					<a class="orbix-tool-card" href="/list/updates/"><i class="fas fa-arrows-rotate"></i><span><strong><?= tohtml(_("System Updates")) ?></strong><small><?= tohtml(_("Review available panel and operating system updates.")) ?></small></span></a>
				</div>
			</section>

			<aside class="orbix-statistics-panel" aria-labelledby="server-stats-title">
				<h2 id="server-stats-title"><?= tohtml(_("Statistics")) ?></h2>
				<dl>
					<div><dt><?= tohtml(_("Accounts")) ?></dt><dd><?= tohtml(count($users)) ?></dd></div>
					<div><dt><?= tohtml(_("Running services")) ?></dt><dd><?= tohtml(count(array_filter($services, fn($service) => ($service["STATE"] ?? "") === "running"))) ?> / <?= tohtml(count($services)) ?></dd></div>
					<div><dt><?= tohtml(_("Load average")) ?></dt><dd><?= tohtml($sys["sysinfo"]["LOADAVERAGE"] ?? "—") ?></dd></div>
					<div><dt><?= tohtml(_("Server")) ?></dt><dd><?= tohtml($sys["sysinfo"]["HOSTNAME"] ?? $_SERVER["HTTP_HOST"]) ?></dd></div>
					<div><dt><?= tohtml(_("OrbixPanel")) ?></dt><dd>v<?= tohtml($sys["sysinfo"]["HESTIA"] ?? $_SESSION["VERSION"]) ?></dd></div>
				</dl>
				<a class="button button-secondary" href="/list/rrd/"><i class="fas fa-chart-line"></i><?= tohtml(_("Open Task Monitor")) ?></a>
			</aside>
		</div>

		<?php $dashboard_charts = array_slice($rrd, 0, 3, true); ?>
		<?php if (!empty($dashboard_charts)) { ?>
			<section class="orbix-monitoring" aria-labelledby="monitoring-title">
				<div class="orbix-section-heading">
					<div>
						<p class="orbix-page-eyebrow"><?= tohtml(_("Live resources")) ?></p>
						<h2 id="monitoring-title"><?= tohtml(_("Server Monitoring")) ?></h2>
					</div>
					<a href="/list/rrd/"><?= tohtml(_("View reports")) ?></a>
				</div>
				<div class="orbix-monitoring-grid">
					<?php foreach ($dashboard_charts as $chart) { ?>
						<article class="orbix-monitoring-card">
							<h3><?= tohtml($chart["TITLE"]) ?></h3>
							<canvas
								class="js-rrd-chart"
								data-service="<?= tohtml($chart["TYPE"] !== "net" ? $chart["RRD"] : "net_" . $chart["RRD"]) ?>"
								data-period="daily"
							></canvas>
						</article>
					<?php } ?>
				</div>
			</section>
		<?php } ?>

		<section class="orbix-admin-tools" aria-labelledby="admin-tools-title">
			<div class="orbix-section-heading"><h2 id="admin-tools-title"><?= tohtml(_("Tools")) ?></h2></div>
			<div class="orbix-category-list">
				<a href="/edit/server/"><i class="fas fa-gear"></i><span><?= tohtml(_("Server Configuration")) ?></span><i class="fas fa-chevron-right"></i></a>
				<a href="/list/user/"><i class="fas fa-user-gear"></i><span><?= tohtml(_("Account Functions")) ?></span><i class="fas fa-chevron-right"></i></a>
				<a href="/list/package/"><i class="fas fa-boxes-stacked"></i><span><?= tohtml(_("Packages")) ?></span><i class="fas fa-chevron-right"></i></a>
				<a href="/list/server/"><i class="fas fa-sliders"></i><span><?= tohtml(_("Service Configuration")) ?></span><i class="fas fa-chevron-right"></i></a>
				<a href="/list/firewall/"><i class="fas fa-shield"></i><span><?= tohtml(_("Security Center")) ?></span><i class="fas fa-chevron-right"></i></a>
				<a href="/list/backup/"><i class="fas fa-box-archive"></i><span><?= tohtml(_("Backup")) ?></span><i class="fas fa-chevron-right"></i></a>
			</div>
		</section>
	<?php } else { ?>
		<div class="orbix-user-layout">
			<div class="orbix-tool-groups">
				<?php if (!empty($_SESSION["MAIL_SYSTEM"]) && $panel[$user]["MAIL_DOMAINS"] != "0") { ?>
					<section class="orbix-tool-group" aria-labelledby="email-tools-title">
						<div class="orbix-tool-group-heading"><i class="fas fa-envelope"></i><h2 id="email-tools-title"><?= tohtml(_("Email")) ?></h2></div>
						<div class="orbix-icon-grid">
							<a href="/list/mail/"><i class="fas fa-user-envelope"></i><span><?= tohtml(_("Email Accounts")) ?></span></a>
							<a href="/list/mail/"><i class="fas fa-share-from-square"></i><span><?= tohtml(_("Forwarders")) ?></span></a>
							<a href="/list/mail/"><i class="fas fa-route"></i><span><?= tohtml(_("Mail Routing")) ?></span></a>
							<a href="/list/mail/"><i class="fas fa-shield-virus"></i><span><?= tohtml(_("Spam Filters")) ?></span></a>
						</div>
					</section>
				<?php } ?>

				<section class="orbix-tool-group" aria-labelledby="files-tools-title">
					<div class="orbix-tool-group-heading"><i class="fas fa-folder"></i><h2 id="files-tools-title"><?= tohtml(_("Files")) ?></h2></div>
					<div class="orbix-icon-grid">
						<?php if (!empty($_SESSION["FILE_MANAGER"]) && $_SESSION["FILE_MANAGER"] === "true") { ?><a href="/fm/"><i class="fas fa-folder-open"></i><span><?= tohtml(_("File Manager")) ?></span></a><?php } ?>
						<a href="/list/web/"><i class="fas fa-chart-pie"></i><span><?= tohtml(_("Disk Usage")) ?></span></a>
						<a href="/list/backup/"><i class="fas fa-clock-rotate-left"></i><span><?= tohtml(_("Backup")) ?></span></a>
						<a href="/list/backup/"><i class="fas fa-wand-magic-sparkles"></i><span><?= tohtml(_("Backup Manager")) ?></span></a>
					</div>
				</section>

				<?php if (!empty($_SESSION["DB_SYSTEM"]) && $panel[$user]["DATABASES"] != "0") { ?>
					<section class="orbix-tool-group" aria-labelledby="database-tools-title">
						<div class="orbix-tool-group-heading"><i class="fas fa-database"></i><h2 id="database-tools-title"><?= tohtml(_("Databases")) ?></h2></div>
						<div class="orbix-icon-grid">
							<a href="/list/db/"><i class="fas fa-database"></i><span><?= tohtml(_("MySQL Databases")) ?></span></a>
							<a href="/list/db/"><i class="fas fa-user-lock"></i><span><?= tohtml(_("Database Users")) ?></span></a>
							<a href="/list/db/"><i class="fas fa-screwdriver-wrench"></i><span><?= tohtml(_("Database Manager")) ?></span></a>
						</div>
					</section>
				<?php } ?>

				<section class="orbix-tool-group" aria-labelledby="domains-tools-title">
					<div class="orbix-tool-group-heading"><i class="fas fa-globe"></i><h2 id="domains-tools-title"><?= tohtml(_("Domains")) ?></h2></div>
					<div class="orbix-icon-grid">
						<a href="/list/web/"><i class="fas fa-earth-americas"></i><span><?= tohtml(_("Domains")) ?></span></a>
						<?php if (!empty($_SESSION["DNS_SYSTEM"]) && $panel[$user]["DNS_DOMAINS"] != "0") { ?><a href="/list/dns/"><i class="fas fa-location-dot"></i><span><?= tohtml(_("Zone Editor")) ?></span></a><?php } ?>
						<a href="/list/web/"><i class="fas fa-arrow-right-arrow-left"></i><span><?= tohtml(_("Redirects")) ?></span></a>
						<a href="/list/web/"><i class="fas fa-lock"></i><span><?= tohtml(_("SSL/TLS Status")) ?></span></a>
					</div>
				</section>

				<section class="orbix-tool-group" aria-labelledby="advanced-tools-title">
					<div class="orbix-tool-group-heading"><i class="fas fa-sliders"></i><h2 id="advanced-tools-title"><?= tohtml(_("Advanced")) ?></h2></div>
					<div class="orbix-icon-grid">
						<a href="/list/cron/"><i class="fas fa-clock"></i><span><?= tohtml(_("Cron Jobs")) ?></span></a>
						<a href="/list/stats/"><i class="fas fa-chart-column"></i><span><?= tohtml(_("Metrics")) ?></span></a>
						<?php if (!empty($_SESSION["WEB_TERMINAL"]) && $_SESSION["WEB_TERMINAL"] === "true" && $_SESSION["login_shell"] !== "nologin") { ?><a href="/list/terminal/"><i class="fas fa-terminal"></i><span><?= tohtml(_("Terminal")) ?></span></a><?php } ?>
						<a href="/edit/user/?user=<?= urlencode($user_plain) ?>&amp;token=<?= urlencode($_SESSION["token"]) ?>"><i class="fas fa-address-card"></i><span><?= tohtml(_("Contact Information")) ?></span></a>
					</div>
				</section>
			</div>

			<aside class="orbix-account-sidebar">
				<section>
					<h2><?= tohtml(_("General Information")) ?></h2>
					<dl>
						<div><dt><?= tohtml(_("Current User")) ?></dt><dd><?= tohtml($user_plain) ?></dd></div>
						<div><dt><?= tohtml(_("Account Name")) ?></dt><dd><?= tohtml($account_name) ?></dd></div>
						<div><dt><?= tohtml(_("Home Directory")) ?></dt><dd>/home/<?= tohtml($user_plain) ?></dd></div>
						<div><dt><?= tohtml(_("Package")) ?></dt><dd><?= tohtml($panel[$user]["PACKAGE"]) ?></dd></div>
					</dl>
					<a class="button button-secondary" href="/edit/user/?user=<?= urlencode($user_plain) ?>&amp;token=<?= urlencode($_SESSION["token"]) ?>"><i class="fas fa-user-gear"></i><?= tohtml(_("Account Preferences")) ?></a>
				</section>
				<section>
					<h2><?= tohtml(_("Statistics")) ?></h2>
					<dl>
						<div><dt><?= tohtml(_("Disk Usage")) ?></dt><dd><?= humanize_usage_size($panel[$user]["U_DISK"]) ?> <?= humanize_usage_measure($panel[$user]["U_DISK"]) ?> / <?= tohtml($disk_limit) ?></dd></div>
						<div><dt><?= tohtml(_("Bandwidth")) ?></dt><dd><?= humanize_usage_size($panel[$user]["U_BANDWIDTH"]) ?> <?= humanize_usage_measure($panel[$user]["U_BANDWIDTH"]) ?> / <?= tohtml($bandwidth_limit) ?></dd></div>
						<div><dt><?= tohtml(_("Domains")) ?></dt><dd><?= tohtml($panel[$user]["U_WEB_DOMAINS"]) ?> / <?= tohtml($panel[$user]["WEB_DOMAINS"] === "unlimited" ? "∞" : $panel[$user]["WEB_DOMAINS"]) ?></dd></div>
						<div><dt><?= tohtml(_("Email Accounts")) ?></dt><dd><?= tohtml($panel[$user]["U_MAIL_ACCOUNTS"]) ?></dd></div>
						<div><dt><?= tohtml(_("Databases")) ?></dt><dd><?= tohtml($panel[$user]["U_DATABASES"]) ?> / <?= tohtml($panel[$user]["DATABASES"] === "unlimited" ? "∞" : $panel[$user]["DATABASES"]) ?></dd></div>
					</dl>
				</section>
			</aside>
		</div>
	<?php } ?>
</div>
