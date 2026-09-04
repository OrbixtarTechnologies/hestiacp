<?php
$deliverability_passed = count(array_filter($deliverability_result, fn($status, $key) => in_array($key, ["MX", "SPF", "DKIM", "DMARC"], true) && $status === "pass", ARRAY_FILTER_USE_BOTH));
?>

<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Outbound trust")) ?></p>
			<h1><?= tohtml(_("Email Deliverability")) ?></h1>
			<p><?= tohtml(_("Check the public DNS records that help messages arrive, authenticate correctly, and avoid spam folders.")) ?></p>
		</div>
		<a class="button button-secondary" href="/list/mail/">
			<i class="fas fa-envelope"></i><?= tohtml(_("Manage Mail Domains")) ?>
		</a>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<?php if (empty($deliverability_domains)) { ?>
		<section class="form-container">
			<h2><?= tohtml(_("Add a mail domain to begin")) ?></h2>
			<p><?= tohtml(_("Deliverability checks become available after this account has a mail domain.")) ?></p>
			<a class="button button-primary" href="/add/mail/"><i class="fas fa-plus"></i><?= tohtml(_("Add Mail Domain")) ?></a>
		</section>
	<?php } else { ?>
		<section class="orbix-deliverability-summary" aria-labelledby="deliverability-domain-title">
			<form method="get">
				<label class="form-label" for="deliverability-domain"><?= tohtml(_("Mail domain")) ?></label>
				<div class="orbix-inline-control">
					<select class="form-select" id="deliverability-domain" name="domain">
						<?php foreach ($deliverability_domains as $domain => $details) { ?>
							<option value="<?= tohtml($domain) ?>" <?= $domain === $deliverability_domain ? "selected" : "" ?>><?= tohtml($domain) ?></option>
						<?php } ?>
					</select>
					<button class="button button-primary" type="submit"><i class="fas fa-rotate"></i><?= tohtml(_("Run Check")) ?></button>
				</div>
			</form>
			<div class="orbix-deliverability-score">
				<strong><?= tohtml($deliverability_passed) ?>/4</strong>
				<span id="deliverability-domain-title"><?= tohtml($deliverability_passed === 4 ? _("DNS checks passed") : _("DNS records need attention")) ?></span>
				<small><?= tohtml(!empty($deliverability_result["CHECKED"]) ? sprintf(_("Checked %s"), $deliverability_result["CHECKED"]) : _("Check unavailable")) ?></small>
			</div>
		</section>

		<section aria-labelledby="deliverability-checks-title">
			<div class="orbix-section-heading">
				<div>
					<p class="orbix-page-eyebrow"><?= tohtml(_("Public DNS")) ?></p>
					<h2 id="deliverability-checks-title"><?= tohtml(_("Authentication path")) ?></h2>
				</div>
				<a href="/list/mail/?<?= tohtml(http_build_query(["domain" => $deliverability_domain, "dns" => "1", "token" => $_SESSION["token"]])) ?>"><?= tohtml(_("View required records")) ?></a>
			</div>
			<div class="orbix-dns-checks">
				<?php foreach ($deliverability_checks as $type => $check) {
					$status = $deliverability_result[$type] ?? "unavailable";
					$is_pass = $status === "pass";
					$status_label = match ($status) {
						"pass" => _("Published"),
						"disabled" => _("Disabled in mail settings"),
						"missing" => _("Missing or not public"),
						default => _("Could not check"),
					};
				?>
					<article class="orbix-dns-check" data-status="<?= tohtml($status) ?>">
						<div class="orbix-dns-check-heading">
							<span><?= tohtml($type) ?></span>
							<i class="fas <?= $is_pass ? "fa-circle-check" : "fa-triangle-exclamation" ?>" aria-hidden="true"></i>
						</div>
						<h3><?= tohtml($check["label"]) ?></h3>
						<code><?= tohtml($check["record"]) ?></code>
						<p><?= tohtml($check["description"]) ?></p>
						<strong><?= tohtml($status_label) ?></strong>
					</article>
				<?php } ?>
			</div>
		</section>

		<section class="orbix-deliverability-actions" aria-labelledby="deliverability-actions-title">
			<div>
				<h2 id="deliverability-actions-title"><?= tohtml(_("Fix a failed check")) ?></h2>
				<p><?= tohtml(_("Publish the required records at the authoritative DNS provider, wait for DNS propagation, then run the check again.")) ?></p>
			</div>
			<div>
				<a class="button button-secondary" href="/list/mail/?<?= tohtml(http_build_query(["domain" => $deliverability_domain, "dns" => "1", "token" => $_SESSION["token"]])) ?>"><i class="fas fa-list"></i><?= tohtml(_("Required Records")) ?></a>
				<a class="button button-secondary" href="/edit/mail/?<?= tohtml(http_build_query(["domain" => $deliverability_domain, "token" => $_SESSION["token"]])) ?>"><i class="fas fa-gear"></i><?= tohtml(_("Mail Settings")) ?></a>
			</div>
		</section>
	<?php } ?>
</div>
