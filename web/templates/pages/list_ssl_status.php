<?php
$ssl_status_total = count($ssl_status_domains);
$ssl_status_attention =
	$ssl_status_summary["expiring"] +
	$ssl_status_summary["expired"] +
	$ssl_status_summary["unsecured"] +
	$ssl_status_summary["unknown"];
$ssl_status_labels = [
	"valid" => _("Valid"),
	"expiring" => _("Expiring soon"),
	"expired" => _("Expired"),
	"unsecured" => _("Not secured"),
	"unknown" => _("Needs inspection"),
];
$ssl_autossl_eligible_count = count(
	array_filter(
		$ssl_status_domains,
		fn($certificate) => !$certificate["suspended"] && $certificate["status"] !== "valid",
	),
);
?>

<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("HTTPS coverage")) ?></p>
			<h1><?= tohtml(_("SSL/TLS Status")) ?></h1>
			<p><?= tohtml(_("Review certificate coverage and renewal health across every website in this hosting account.")) ?></p>
		</div>
		<a class="button button-secondary" href="/list/web/">
			<i class="fas fa-earth-americas"></i><?= tohtml(_("Manage Websites")) ?>
		</a>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<section class="orbix-certificate-summary" aria-labelledby="certificate-summary-title">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Certificate health")) ?></p>
			<h2 id="certificate-summary-title"><?= tohtml($ssl_status_attention === 0 ? _("Every website is covered") : sprintf(ngettext("%d website needs attention", "%d websites need attention", $ssl_status_attention), $ssl_status_attention)) ?></h2>
			<p><?= tohtml(sprintf(ngettext("%d hosted website checked", "%d hosted websites checked", $ssl_status_total), $ssl_status_total)) ?></p>
		</div>
		<div class="orbix-certificate-counts">
			<div data-status="valid"><strong><?= tohtml($ssl_status_summary["valid"]) ?></strong><span><?= tohtml(_("Valid")) ?></span></div>
			<div data-status="expiring"><strong><?= tohtml($ssl_status_summary["expiring"]) ?></strong><span><?= tohtml(_("Expiring")) ?></span></div>
			<div data-status="expired"><strong><?= tohtml($ssl_status_summary["expired"]) ?></strong><span><?= tohtml(_("Expired")) ?></span></div>
			<div data-status="unsecured"><strong><?= tohtml($ssl_status_summary["unsecured"]) ?></strong><span><?= tohtml(_("Unsecured")) ?></span></div>
			<div data-status="unknown"><strong><?= tohtml($ssl_status_summary["unknown"]) ?></strong><span><?= tohtml(_("Inspect")) ?></span></div>
		</div>
	</section>

	<?php if (empty($ssl_status_domains)) { ?>
		<section class="form-container">
			<h2><?= tohtml(_("Add a website to begin")) ?></h2>
			<p><?= tohtml(_("Certificate monitoring becomes available after this account has a web domain.")) ?></p>
			<a class="button button-primary" href="/add/web/"><i class="fas fa-plus"></i><?= tohtml(_("Add a Website")) ?></a>
		</section>
	<?php } else { ?>
		<form method="post" class="orbix-autossl-workspace">
			<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
			<input type="hidden" name="start_autossl" value="1">
			<section class="orbix-autossl-pipeline" aria-labelledby="autossl-title">
				<div>
					<p class="orbix-page-eyebrow"><?= tohtml(_("Automated repair")) ?></p>
					<h2 id="autossl-title"><?= tohtml(_("Run AutoSSL")) ?></h2>
					<p><?= tohtml(_("Select websites that need attention. OrbixPanel will request and install Let’s Encrypt certificates in a tracked background job.")) ?></p>
				</div>
				<ol>
					<li><span>1</span><strong><?= tohtml(_("Assess")) ?></strong><small><?= tohtml(_("Use the health results below")) ?></small></li>
					<li><span>2</span><strong><?= tohtml(_("Queue")) ?></strong><small><?= tohtml(_("Serialize selected websites")) ?></small></li>
					<li><span>3</span><strong><?= tohtml(_("Issue")) ?></strong><small><?= tohtml(_("Track each certificate result")) ?></small></li>
				</ol>
				<button class="button button-primary" type="submit" <?= $ssl_autossl_eligible_count === 0 ? "disabled" : "" ?>><i class="fas fa-wand-magic-sparkles"></i><?= tohtml($ssl_autossl_eligible_count === 0 ? _("No Repairs Needed") : _("Run AutoSSL for Selected")) ?></button>
			</section>

			<section class="orbix-certificate-list" aria-label="<?= tohtml(_("Website certificates")) ?>">
			<?php foreach ($ssl_status_domains as $domain => $certificate) {
				$status = $certificate["status"];
				$autossl_eligible = !$certificate["suspended"] && $status !== "valid";
				$issuer = $certificate["issuer"] ?: _("No certificate metadata available");
				$renewal = $certificate["letsencrypt"] ? _("Automatic renewal") : _("Manual certificate");
				$expiry = match ($status) {
					"valid" => sprintf(ngettext("Expires in %d day", "Expires in %d days", $certificate["days_remaining"]), $certificate["days_remaining"]),
					"expiring" => sprintf(ngettext("Expires in %d day", "Expires in %d days", $certificate["days_remaining"]), $certificate["days_remaining"]),
					"expired" => sprintf(ngettext("Expired %d day ago", "Expired %d days ago", abs($certificate["days_remaining"])), abs($certificate["days_remaining"])),
					"unsecured" => _("HTTPS is not enabled"),
					default => _("Certificate expiry could not be read"),
				};
			?>
				<article class="orbix-certificate-row" data-status="<?= tohtml($status) ?>">
					<div class="orbix-certificate-state">
						<?php if ($autossl_eligible) { ?><input class="form-check-input" type="checkbox" name="domains[]" value="<?= tohtml($domain) ?>" aria-label="<?= tohtml(sprintf(_("Select %s for AutoSSL"), $domain)) ?>" checked><?php } else { ?><span aria-hidden="true"></span><?php } ?>
						<i class="fas <?= $status === "valid" ? "fa-circle-check" : ($status === "unsecured" ? "fa-lock-open" : "fa-triangle-exclamation") ?>" aria-hidden="true"></i>
					</div>
					<div class="orbix-certificate-domain">
						<div><h2><?= tohtml($domain) ?></h2><span class="orbix-status-pill"><?= tohtml($ssl_status_labels[$status]) ?></span><?php if ($certificate["suspended"]) { ?><span class="orbix-status-pill is-neutral"><?= tohtml(_("Suspended")) ?></span><?php } ?></div>
						<p><?= tohtml($expiry) ?><?php if ($certificate["expires_at"] !== null) { ?> · <?= tohtml(date("M j, Y", $certificate["expires_at"])) ?><?php } ?></p>
					</div>
					<div class="orbix-certificate-meta">
						<span><?= tohtml($status === "unsecured" ? _("Certificate") : $renewal) ?></span>
						<strong><?= tohtml($issuer) ?></strong>
					</div>
					<a class="button button-secondary" href="/edit/web/?<?= tohtml(http_build_query(["domain" => $domain, "token" => $_SESSION["token"]])) ?>"><i class="fas fa-gear"></i><?= tohtml($status === "unsecured" ? _("Enable HTTPS") : _("Manage Certificate")) ?></a>
				</article>
			<?php } ?>
			</section>
		</form>
	<?php } ?>

	<?php if (!empty($ssl_autossl_jobs)) { ?>
		<section class="orbix-autossl-jobs" aria-labelledby="autossl-jobs-title">
			<div class="orbix-section-heading"><div><p class="orbix-page-eyebrow"><?= tohtml(_("Background activity")) ?></p><h2 id="autossl-jobs-title"><?= tohtml(_("Recent AutoSSL jobs")) ?></h2></div><a href="/list/ssl-status/"><?= tohtml(_("Refresh jobs")) ?></a></div>
			<div class="orbix-category-list">
				<?php foreach ($ssl_autossl_jobs as $job_id => $job) {
					$status_parts = explode(":", $job["STATUS"] ?? "unknown", 2);
					$job_status = $status_parts[0];
					$job_progress = $status_parts[1] ?? sprintf("%d/%d", $job["PROCESSED"] ?? 0, $job["TOTAL"] ?? 0);
					$job_icon = match ($job_status) {
						"completed" => "fa-circle-check icon-green",
						"completed_with_errors", "failed", "interrupted" => "fa-triangle-exclamation icon-red",
						default => "fa-circle-notch fa-spin icon-blue",
					};
				?>
					<a href="/list/ssl-status/job/?<?= tohtml(http_build_query(["job" => $job_id])) ?>">
						<i class="fas <?= tohtml($job_icon) ?>"></i>
						<span><strong><?= tohtml(sprintf(_("AutoSSL · %s websites"), $job["TOTAL"] ?? 0)) ?></strong><small><?= tohtml(ucwords(str_replace("_", " ", $job_status))) ?> · <?= tohtml($job_progress) ?> · <?= tohtml($job["STARTED"] ?? "") ?></small></span>
						<span><?= tohtml(sprintf(_("%s succeeded · %s failed"), $job["SUCCEEDED"] ?? 0, $job["FAILED"] ?? 0)) ?></span>
						<i class="fas fa-file-lines"></i>
					</a>
				<?php } ?>
			</div>
		</section>
	<?php } ?>
</div>
