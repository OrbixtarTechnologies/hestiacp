<?php
$server_health_uptime = (int) ($server_health_capacity["UPTIME_SECONDS"] ?? 0) / 60;
$server_health_disk_used = (int) ($server_health_capacity["DISK_USED_KB"] ?? 0);
$server_health_disk_total = (int) ($server_health_capacity["DISK_TOTAL_KB"] ?? 0);
$server_health_memory_used = (int) ($server_health_capacity["MEMORY_USED_MB"] ?? 0);
$server_health_memory_total = (int) ($server_health_capacity["MEMORY_TOTAL_MB"] ?? 0);
?>

<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Server operations")) ?></p>
			<h1><?= tohtml(_("Server Health")) ?></h1>
			<p><?= tohtml(_("Monitor capacity, load, uptime, and service availability before they affect hosted accounts.")) ?></p>
		</div>
		<a class="button button-secondary" href="/list/server/"><i class="fas fa-sliders"></i><?= tohtml(_("Manage Services")) ?></a>
	</header>

	<section class="orbix-health-banner" data-status="<?= $server_health_needs_attention ? "attention" : "healthy" ?>" aria-labelledby="server-health-state">
		<i class="fas <?= $server_health_needs_attention ? "fa-triangle-exclamation" : "fa-circle-check" ?>" aria-hidden="true"></i>
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml($server_health_system["HOSTNAME"] ?? _("This server")) ?></p>
			<h2 id="server-health-state"><?= tohtml($server_health_needs_attention ? _("Server health needs attention") : _("Core systems are healthy")) ?></h2>
			<p><?= tohtml(sprintf(ngettext("%d of %d monitored service is running", "%d of %d monitored services are running", count($server_health_services)), $server_health_running, count($server_health_services))) ?></p>
		</div>
		<span><?= tohtml(sprintf(_("Up %s"), humanize_time($server_health_uptime))) ?></span>
	</section>

	<section class="orbix-health-grid" aria-label="<?= tohtml(_("Server capacity")) ?>">
		<article class="orbix-health-metric" data-level="<?= $server_health_load_percent >= 90 ? "critical" : ($server_health_load_percent >= 70 ? "warning" : "normal") ?>">
			<div><span><?= tohtml(_("CPU load")) ?></span><strong><?= tohtml(number_format($server_health_load_one, 2)) ?></strong></div>
			<div class="orbix-health-bar"><span style="width: <?= tohtml($server_health_load_percent) ?>%"></span></div>
			<small><?= tohtml(sprintf(_("%d cores · %d%% of one-minute capacity"), $server_health_cpu_cores, $server_health_load_percent)) ?></small>
		</article>
		<article class="orbix-health-metric" data-level="<?= $server_health_memory_percent >= 90 ? "critical" : ($server_health_memory_percent >= 75 ? "warning" : "normal") ?>">
			<div><span><?= tohtml(_("Memory")) ?></span><strong><?= tohtml($server_health_memory_percent) ?>%</strong></div>
			<div class="orbix-health-bar"><span style="width: <?= tohtml($server_health_memory_percent) ?>%"></span></div>
			<small><?= tohtml(sprintf(_("%s MB used of %s MB"), number_format($server_health_memory_used), number_format($server_health_memory_total))) ?></small>
		</article>
		<article class="orbix-health-metric" data-level="<?= $server_health_disk_percent >= 85 ? "critical" : ($server_health_disk_percent >= 70 ? "warning" : "normal") ?>">
			<div><span><?= tohtml(_("Root disk")) ?></span><strong><?= tohtml($server_health_disk_percent) ?>%</strong></div>
			<div class="orbix-health-bar"><span style="width: <?= tohtml($server_health_disk_percent) ?>%"></span></div>
			<small><?= humanize_usage_size($server_health_disk_used) ?> <?= humanize_usage_measure($server_health_disk_used) ?> <?= tohtml(_("used of")) ?> <?= humanize_usage_size($server_health_disk_total) ?> <?= humanize_usage_measure($server_health_disk_total) ?></small>
		</article>
		<article class="orbix-health-metric" data-level="<?= empty($server_health_stopped) ? "normal" : "critical" ?>">
			<div><span><?= tohtml(_("Services")) ?></span><strong><?= tohtml($server_health_running) ?>/<?= tohtml(count($server_health_services)) ?></strong></div>
			<div class="orbix-health-bar"><span style="width: <?= tohtml(count($server_health_services) > 0 ? round(($server_health_running / count($server_health_services)) * 100) : 0) ?>%"></span></div>
			<small><?= tohtml(empty($server_health_stopped) ? _("All monitored services are available") : sprintf(ngettext("%d service is stopped", "%d services are stopped", count($server_health_stopped)), count($server_health_stopped))) ?></small>
		</article>
	</section>

	<div class="orbix-admin-overview">
		<section aria-labelledby="stopped-services-title">
			<div class="orbix-section-heading"><h2 id="stopped-services-title"><?= tohtml(_("Service exceptions")) ?></h2><a href="/list/server/"><?= tohtml(_("Open Service Manager")) ?></a></div>
			<?php if (empty($server_health_stopped)) { ?>
				<div class="orbix-health-empty"><i class="fas fa-circle-check"></i><div><strong><?= tohtml(_("No stopped services")) ?></strong><p><?= tohtml(_("Every service monitored by OrbixPanel is currently running.")) ?></p></div></div>
			<?php } else { ?>
				<div class="orbix-health-exceptions">
					<?php foreach ($server_health_stopped as $service => $details) { ?>
						<a href="/list/server/"><i class="fas fa-circle-xmark"></i><span><strong><?= tohtml($service) ?></strong><small><?= tohtml(_($details["SYSTEM"] ?? "service")) ?></small></span><span><?= tohtml(_("Stopped")) ?></span><i class="fas fa-chevron-right"></i></a>
					<?php } ?>
				</div>
			<?php } ?>
		</section>

		<aside class="orbix-statistics-panel" aria-labelledby="server-details-title">
			<h2 id="server-details-title"><?= tohtml(_("Server details")) ?></h2>
			<dl>
				<div><dt><?= tohtml(_("Hostname")) ?></dt><dd><?= tohtml($server_health_system["HOSTNAME"] ?? "—") ?></dd></div>
				<div><dt><?= tohtml(_("Operating system")) ?></dt><dd><?= tohtml(trim(($server_health_system["OS"] ?? "") . " " . ($server_health_system["VERSION"] ?? ""))) ?></dd></div>
				<div><dt><?= tohtml(_("Architecture")) ?></dt><dd><?= tohtml($server_health_system["ARCH"] ?? "—") ?></dd></div>
				<div><dt><?= tohtml(_("OrbixPanel")) ?></dt><dd>v<?= tohtml($server_health_system["HESTIA"] ?? $_SESSION["VERSION"]) ?></dd></div>
				<div><dt><?= tohtml(_("Load average")) ?></dt><dd><?= tohtml($server_health_system["LOADAVERAGE"] ?? "—") ?></dd></div>
			</dl>
			<a class="button button-secondary" href="/list/rrd/"><i class="fas fa-chart-line"></i><?= tohtml(_("Open Task Monitor")) ?></a>
			<?php if (!empty($_SESSION["MAIL_SYSTEM"]) && $_SESSION["MAIL_SYSTEM"] !== "remote") { ?><a class="button button-secondary" href="/list/mail-queue/"><i class="fas fa-envelopes-bulk"></i><?= tohtml(_("Open Mail Queue")) ?></a><?php } ?>
		</aside>
	</div>
</div>
