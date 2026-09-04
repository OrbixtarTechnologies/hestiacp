<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Account transfer")) ?></p>
			<h1><?= tohtml(_("Migration Center")) ?></h1>
			<p><?= tohtml(_("Preflight and import a full cPanel account backup while tracking the migration as a background job.")) ?></p>
		</div>
		<a class="button button-secondary" href="/list/user/">
			<i class="fas fa-users"></i><?= tohtml(_("View Accounts")) ?>
		</a>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<div class="orbix-admin-overview">
		<section aria-labelledby="start-migration-title">
			<div class="orbix-section-heading">
				<div>
					<p class="orbix-page-eyebrow"><?= tohtml(_("New transfer")) ?></p>
					<h2 id="start-migration-title"><?= tohtml(_("Import a cPanel backup")) ?></h2>
				</div>
			</div>
			<form method="post" class="form-container">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="start_migration" value="1">
				<div class="u-mb20">
					<label class="form-label" for="migration-archive"><?= tohtml(_("Backup archive")) ?></label>
					<select class="form-select" id="migration-archive" name="archive" required <?= empty($migration_archives) ? "disabled" : "" ?>>
						<option value=""><?= tohtml(_("Select an archive")) ?></option>
						<?php foreach ($migration_archives as $archive => $details) { ?>
							<option value="<?= tohtml($archive) ?>"><?= tohtml($archive) ?> · <?= tohtml(humanize_usage_size(((int) ($details["SIZE"] ?? 0)) / 1024)) ?> <?= tohtml(humanize_usage_measure(((int) ($details["SIZE"] ?? 0)) / 1024)) ?></option>
						<?php } ?>
					</select>
					<p class="form-helper"><?= tohtml(_("Place a .tar.gz or .tgz cPanel backup directly in /backup. Archives with unsafe paths or missing account data are rejected before execution.")) ?></p>
				</div>
				<div class="form-check u-mb20">
					<input class="form-check-input" type="checkbox" name="restore_mx" id="restore-mx" value="yes">
					<label for="restore-mx"><?= tohtml(_("Restore source MX records instead of using this server's mail routing")) ?></label>
				</div>
				<button class="button button-primary" type="submit" <?= empty($migration_archives) ? "disabled" : "" ?>>
					<i class="fas fa-arrow-right-arrow-left"></i><?= tohtml(_("Run Preflight and Start")) ?>
				</button>
			</form>
		</section>

		<aside class="orbix-statistics-panel" aria-labelledby="migration-safety-title">
			<h2 id="migration-safety-title"><?= tohtml(_("Migration safeguards")) ?></h2>
			<dl>
				<div><dt><?= tohtml(_("Available archives")) ?></dt><dd><?= tohtml(count($migration_archives)) ?></dd></div>
				<div><dt><?= tohtml(_("Recent jobs")) ?></dt><dd><?= tohtml(count($migration_jobs)) ?></dd></div>
				<div><dt><?= tohtml(_("Execution")) ?></dt><dd><?= tohtml(_("Background")) ?></dd></div>
				<div><dt><?= tohtml(_("Archive validation")) ?></dt><dd><?= tohtml(_("Required")) ?></dd></div>
			</dl>
			<p><?= tohtml(_("The importer creates a new account. It will stop if that username already exists. Review websites, PHP versions, certificates, DNS, mail, databases, and scheduled tasks after completion.")) ?></p>
		</aside>
	</div>

	<section class="form-container orbix-remote-transfer" aria-labelledby="remote-transfer-title">
		<div class="orbix-section-heading">
			<div>
				<p class="orbix-page-eyebrow"><?= tohtml(_("Source server intake")) ?></p>
				<h2 id="remote-transfer-title"><?= tohtml(_("Transfer from a remote cPanel server")) ?></h2>
			</div>
			<span class="orbix-reseller-boundary"><i class="fas fa-key"></i><?= tohtml(_("Key authentication only")) ?></span>
		</div>
		<p><?= tohtml(_("Download an existing cpmove archive over host-key-pinned SFTP, validate it, then queue the normal import pipeline. Passwords are never accepted or stored.")) ?></p>
		<form method="post" class="orbix-remote-transfer-grid">
			<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
			<input type="hidden" name="start_remote_transfer" value="1">
			<div><label class="form-label" for="remote-host"><?= tohtml(_("Source server")) ?></label><input class="form-control" id="remote-host" name="remote_host" placeholder="cpanel.example.com" required></div>
			<div><label class="form-label" for="remote-port"><?= tohtml(_("SSH port")) ?></label><input class="form-control" type="number" min="1" max="65535" id="remote-port" name="remote_port" value="22" required></div>
			<div class="is-wide"><label class="form-label" for="remote-path"><?= tohtml(_("Remote cpmove path")) ?></label><input class="form-control" id="remote-path" name="remote_path" placeholder="/home/cpmove-client.tar.gz" required></div>
			<div class="is-wide"><label class="form-label" for="host-fingerprint"><?= tohtml(_("SHA256 host key fingerprint")) ?></label><input class="form-control" id="host-fingerprint" name="host_fingerprint" placeholder="SHA256:…" required></div>
			<div><label class="form-label" for="identity"><?= tohtml(_("Installed SSH identity")) ?></label><input class="form-control" id="identity" name="identity" value="id_ed25519" required></div>
			<div class="form-check orbix-remote-mx"><input class="form-check-input" type="checkbox" name="remote_restore_mx" id="remote-restore-mx" value="yes"><label for="remote-restore-mx"><?= tohtml(_("Keep source MX routing")) ?></label></div>
			<div class="is-wide orbix-remote-transfer-action"><p class="form-helper"><?= tohtml(_("The private key must already exist under /root/.ssh with owner-only permissions. Confirm the fingerprint through a separate trusted channel.")) ?></p><button class="button button-primary" type="submit"><i class="fas fa-cloud-arrow-down"></i><?= tohtml(_("Verify and Transfer")) ?></button></div>
		</form>
	</section>

	<section aria-labelledby="migration-jobs-title">
		<div class="orbix-section-heading">
			<div>
				<p class="orbix-page-eyebrow"><?= tohtml(_("Transfer activity")) ?></p>
				<h2 id="migration-jobs-title"><?= tohtml(_("Recent migrations")) ?></h2>
			</div>
			<a href="/list/migrations/"><?= tohtml(_("Refresh status")) ?></a>
		</div>
		<?php if (empty($migration_jobs)) { ?>
			<div class="form-container"><p><?= tohtml(_("No migration jobs have been started.")) ?></p></div>
		<?php } else { ?>
			<div class="orbix-category-list">
				<?php foreach ($migration_jobs as $job_id => $job) {
					$status = $job["STATUS"] ?? "unknown";
					$status_icon = str_starts_with($status, "completed")
						? "fa-circle-check icon-green"
						: (str_starts_with($status, "failed") || $status === "interrupted"
							? "fa-circle-xmark icon-red"
							: "fa-circle-notch fa-spin icon-blue");
				?>
					<a href="/list/migrations/log/?<?= tohtml(http_build_query(["job" => $job_id])) ?>">
						<i class="fas <?= tohtml($status_icon) ?>"></i>
						<span><strong><?= tohtml($job["BACKUP"] ?? $job_id) ?></strong><small><?= tohtml(($job["TYPE"] ?? "local") === "remote" ? sprintf(_("Remote from %s"), $job["SOURCE"] ?? "") : _("Local archive")) ?> · <?= tohtml(ucfirst(str_replace("_", " ", $status))) ?> · <?= tohtml($job["STARTED"] ?? "") ?></small></span>
						<i class="fas fa-file-lines"></i>
					</a>
				<?php } ?>
			</div>
		<?php } ?>
	</section>
</div>
