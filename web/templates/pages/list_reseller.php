<?php
$reseller_total = count($reseller_users);
$reseller_suspended = count(
	array_filter($reseller_users, fn($account) => ($account["SUSPENDED"] ?? "no") === "yes"),
);
$reseller_limit = (int) ($reseller_profile["RESELLER_MAX_USERS"] ?? 0);
$reseller_available = max(0, $reseller_limit - $reseller_total);
?>

<div class="container orbix-dashboard orbix-reseller-center">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Account delegation")) ?></p>
			<h1><?= tohtml(_("Reseller Center")) ?></h1>
			<p><?= tohtml(_("Create and operate customer hosting accounts without access to server-wide administration.")) ?></p>
		</div>
		<span class="orbix-reseller-boundary"><i class="fas fa-shield-halved"></i><?= tohtml(_("Owner-scoped access")) ?></span>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<section class="orbix-reseller-ledger" aria-labelledby="reseller-ledger-title">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Account capacity")) ?></p>
			<h2 id="reseller-ledger-title"><?= tohtml(sprintf(_("%d of %d customer slots assigned"), $reseller_total, $reseller_limit)) ?></h2>
			<div class="orbix-reseller-meter"><span style="width: <?= tohtml($reseller_limit > 0 ? min(100, ($reseller_total / $reseller_limit) * 100) : 0) ?>%"></span></div>
		</div>
		<dl>
			<div><dt><?= tohtml(_("Available")) ?></dt><dd><?= tohtml($reseller_available) ?></dd></div>
			<div><dt><?= tohtml(_("Active")) ?></dt><dd><?= tohtml($reseller_total - $reseller_suspended) ?></dd></div>
			<div><dt><?= tohtml(_("Suspended")) ?></dt><dd><?= tohtml($reseller_suspended) ?></dd></div>
			<div><dt><?= tohtml(_("Packages")) ?></dt><dd><?= tohtml(count($reseller_packages)) ?></dd></div>
		</dl>
	</section>

	<div class="orbix-reseller-workspace">
		<section class="form-container orbix-reseller-create" aria-labelledby="create-customer-title">
			<p class="orbix-page-eyebrow"><?= tohtml(_("Provisioning")) ?></p>
			<h2 id="create-customer-title"><?= tohtml(_("Create a customer account")) ?></h2>
			<p><?= tohtml(_("The account is linked to your reseller ownership automatically.")) ?></p>
			<form method="post">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="action" value="create">
				<label for="v_username" class="form-label"><?= tohtml(_("Username")) ?></label>
				<input class="form-control" id="v_username" name="v_username" autocomplete="off" required>
				<label for="v_name" class="form-label"><?= tohtml(_("Contact name")) ?></label>
				<input class="form-control" id="v_name" name="v_name" required>
				<label for="v_email" class="form-label"><?= tohtml(_("Email")) ?></label>
				<input class="form-control" type="email" id="v_email" name="v_email" required>
				<label for="v_password" class="form-label"><?= tohtml(_("Temporary password")) ?></label>
				<input class="form-control" type="password" id="v_password" name="v_password" autocomplete="new-password" required>
				<small><?= tohtml(_("Use at least 8 characters with uppercase, lowercase, and a number.")) ?></small>
				<label for="v_package" class="form-label"><?= tohtml(_("Hosting package")) ?></label>
				<select class="form-select" id="v_package" name="v_package" required>
					<?php foreach ($reseller_packages as $package_name => $package): ?>
						<option value="<?= tohtml($package_name) ?>"><?= tohtml($package_name) ?></option>
					<?php endforeach; ?>
				</select>
				<button class="button button-primary" type="submit" <?= $reseller_available === 0 || empty($reseller_packages) ? "disabled" : "" ?>><i class="fas fa-user-plus"></i><?= tohtml(_("Create customer")) ?></button>
			</form>
		</section>

		<section class="orbix-reseller-accounts" aria-labelledby="customer-accounts-title">
			<div class="orbix-section-heading">
				<div><p class="orbix-page-eyebrow"><?= tohtml(_("Owned accounts")) ?></p><h2 id="customer-accounts-title"><?= tohtml(_("Customer accounts")) ?></h2></div>
				<span><?= tohtml(sprintf(ngettext("%d account", "%d accounts", $reseller_total), $reseller_total)) ?></span>
			</div>
			<?php if (empty($reseller_users)): ?>
				<div class="orbix-reseller-empty"><i class="fas fa-users"></i><h3><?= tohtml(_("No customer accounts yet")) ?></h3><p><?= tohtml(_("Use the provisioning form to create the first account in your portfolio.")) ?></p></div>
			<?php else: ?>
				<div class="orbix-reseller-account-list">
					<?php foreach ($reseller_users as $managed_user => $account): ?>
						<article class="orbix-reseller-account <?= ($account["SUSPENDED"] ?? "no") === "yes" ? "is-suspended" : "" ?>">
							<div class="orbix-reseller-account-mark"><?= tohtml(strtoupper(substr($managed_user, 0, 2))) ?></div>
							<div class="orbix-reseller-account-name"><h3><?= tohtml($managed_user) ?></h3><p><?= tohtml($account["NAME"] ?: $account["CONTACT"]) ?></p></div>
							<div class="orbix-reseller-meta"><span><?= tohtml(_("Package")) ?></span><strong><?= tohtml($account["PACKAGE"]) ?></strong></div>
							<div class="orbix-reseller-meta"><span><?= tohtml(_("Websites")) ?></span><strong><?= tohtml($account["U_WEB_DOMAINS"]) ?></strong></div>
							<div class="orbix-reseller-meta is-disk"><span><?= tohtml(_("Disk")) ?></span><strong><?= tohtml(sprintf("%s MB", $account["U_DISK"])) ?></strong></div>
							<span class="orbix-status-pill <?= ($account["SUSPENDED"] ?? "no") === "yes" ? "is-neutral" : "" ?>"><?= tohtml(($account["SUSPENDED"] ?? "no") === "yes" ? _("Suspended") : _("Active")) ?></span>
							<div class="orbix-reseller-actions">
								<form method="post"><input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>"><input type="hidden" name="user" value="<?= tohtml($managed_user) ?>"><input type="hidden" name="action" value="<?= ($account["SUSPENDED"] ?? "no") === "yes" ? "resume" : "suspend" ?>"><button class="button button-secondary" type="submit"><?= tohtml(($account["SUSPENDED"] ?? "no") === "yes" ? _("Resume") : _("Suspend")) ?></button></form>
								<a class="button button-secondary js-confirm-action" href="/delete/reseller/?<?= tohtml(http_build_query(["user" => $managed_user, "token" => $_SESSION["token"]])) ?>" data-confirm-title="<?= tohtml(_("Delete customer account")) ?>" data-confirm-message="<?= tohtml(sprintf(_("Permanently delete %s and all hosted data?"), $managed_user)) ?>"><i class="fas fa-trash icon-red"></i></a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	</div>
</div>
