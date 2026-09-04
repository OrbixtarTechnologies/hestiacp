<?php
$fleet_online = count(array_filter($fleet_nodes, fn($node) => ($node["STATUS"] ?? "") === "online"));
$fleet_offline = count(array_filter($fleet_nodes, fn($node) => ($node["STATUS"] ?? "") === "offline"));
$fleet_unknown = count($fleet_nodes) - $fleet_online - $fleet_offline;
?>

<div class="container orbix-dashboard orbix-fleet-manager">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Multi-server operations")) ?></p>
			<h1><?= tohtml(_("Server Fleet")) ?></h1>
			<p><?= tohtml(_("Monitor trusted OrbixPanel nodes from one control plane using read-only access keys and pinned TLS identities.")) ?></p>
		</div>
		<?php if (!empty($fleet_nodes)) { ?>
			<form method="post">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="refresh_nodes" value="1">
				<input type="hidden" name="node" value="all">
				<button class="button button-primary" type="submit"><i class="fas fa-arrows-rotate"></i><?= tohtml(_("Refresh Fleet")) ?></button>
			</form>
		<?php } ?>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<section class="orbix-fleet-topology" aria-labelledby="fleet-topology-title">
		<div class="orbix-fleet-control-plane">
			<i class="fas fa-tower-broadcast"></i>
			<span><small><?= tohtml(_("Control plane")) ?></small><strong id="fleet-topology-title"><?= tohtml($_SERVER["HTTP_HOST"] ?? _("This server")) ?></strong></span>
		</div>
		<div class="orbix-fleet-line" aria-hidden="true"></div>
		<div class="orbix-fleet-node-cloud">
			<?php if (empty($fleet_nodes)) { ?>
				<span class="is-empty"><i class="fas fa-circle-plus"></i><?= tohtml(_("Register the first node")) ?></span>
			<?php } else { ?>
				<?php foreach ($fleet_nodes as $name => $node) { ?><span data-status="<?= tohtml($node["STATUS"] ?? "never") ?>"><i class="fas fa-server"></i><?= tohtml($name) ?></span><?php } ?>
			<?php } ?>
		</div>
		<dl class="orbix-fleet-counts">
			<div><dt><?= tohtml(_("Online")) ?></dt><dd><?= tohtml($fleet_online) ?></dd></div>
			<div><dt><?= tohtml(_("Offline")) ?></dt><dd><?= tohtml($fleet_offline) ?></dd></div>
			<div><dt><?= tohtml(_("Unchecked")) ?></dt><dd><?= tohtml($fleet_unknown) ?></dd></div>
		</dl>
	</section>

	<div class="orbix-fleet-layout">
		<section class="orbix-fleet-nodes" aria-labelledby="fleet-nodes-title">
			<div class="orbix-section-heading"><div><p class="orbix-page-eyebrow"><?= tohtml(_("Trusted nodes")) ?></p><h2 id="fleet-nodes-title"><?= tohtml(_("Fleet health")) ?></h2></div><span><?= tohtml(sprintf(ngettext("%d node", "%d nodes", count($fleet_nodes)), count($fleet_nodes))) ?></span></div>

			<?php if (empty($fleet_nodes)) { ?>
				<div class="orbix-fleet-empty"><i class="fas fa-network-wired"></i><h3><?= tohtml(_("No fleet nodes yet")) ?></h3><p><?= tohtml(_("Create a fleet-read access key on another OrbixPanel server, pin its HTTPS public key, and register it here.")) ?></p></div>
			<?php } else { ?>
				<div class="orbix-fleet-card-list">
					<?php foreach ($fleet_nodes as $name => $node) {
						$services = is_array($node["SERVICES"] ?? null) ? $node["SERVICES"] : [];
						$stopped = count(array_filter($services, fn($service) => ($service["STATE"] ?? "stopped") !== "running"));
						$health = $node["HEALTH"] ?? [];
						$info = $node["INFO"] ?? [];
						$status = $node["STATUS"] ?? "never";
					?>
						<article class="orbix-fleet-card" data-status="<?= tohtml($status) ?>">
							<div class="orbix-fleet-card-head">
								<div class="orbix-fleet-node-icon"><i class="fas fa-server"></i><span></span></div>
								<div><p class="orbix-page-eyebrow"><?= tohtml(strtoupper($status)) ?></p><h2><?= tohtml($name) ?></h2><small><?= tohtml($node["HOST"] ?? "") ?>:<?= tohtml($node["PORT"] ?? "") ?></small></div>
								<span class="orbix-status-pill <?= $status === "online" ? "" : ($status === "offline" ? "is-danger" : "is-neutral") ?>"><?= tohtml(ucfirst($status)) ?></span>
							</div>

							<dl class="orbix-fleet-metrics">
								<div><dt><?= tohtml(_("Hostname")) ?></dt><dd><?= tohtml($info["HOSTNAME"] ?? "—") ?></dd></div>
								<div><dt><?= tohtml(_("OrbixPanel")) ?></dt><dd><?= isset($info["HESTIA"]) ? "v" . tohtml($info["HESTIA"]) : "—" ?></dd></div>
								<div><dt><?= tohtml(_("Load")) ?></dt><dd><?= tohtml($health["LOAD_ONE"] ?? "—") ?></dd></div>
								<div><dt><?= tohtml(_("Memory")) ?></dt><dd><?= isset($health["MEMORY_PERCENT"]) ? tohtml($health["MEMORY_PERCENT"]) . "%" : "—" ?></dd></div>
								<div><dt><?= tohtml(_("Disk")) ?></dt><dd><?= isset($health["DISK_PERCENT"]) ? tohtml($health["DISK_PERCENT"]) . "%" : "—" ?></dd></div>
								<div><dt><?= tohtml(_("Services")) ?></dt><dd><?= !empty($services) ? tohtml(count($services) - $stopped) . " / " . tohtml(count($services)) : "—" ?></dd></div>
							</dl>

							<?php if ($status === "offline") { ?><p class="orbix-fleet-error"><i class="fas fa-triangle-exclamation"></i><?= tohtml($node["ERROR"] ?: _("Pinned TLS connection or API authentication failed.")) ?></p><?php } ?>
							<div class="orbix-fleet-card-foot">
								<small><?= tohtml(_("Last checked")) ?> · <?= tohtml($node["CHECKED_AT"] ?: _("Never")) ?></small>
								<div>
									<form method="post"><input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>"><input type="hidden" name="refresh_nodes" value="1"><input type="hidden" name="node" value="<?= tohtml($name) ?>"><button class="button button-secondary" type="submit"><i class="fas fa-rotate"></i><?= tohtml(_("Refresh")) ?></button></form>
									<details class="orbix-fleet-remove">
										<summary class="button button-secondary button-danger"><i class="fas fa-trash"></i><?= tohtml(_("Remove")) ?></summary>
										<form method="post">
											<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
											<input type="hidden" name="delete_node" value="1">
											<input type="hidden" name="node" value="<?= tohtml($name) ?>">
											<label><input type="checkbox" name="confirm_delete" value="yes" required><?= tohtml(sprintf(_("Confirm removal of %s"), $name)) ?></label>
											<button class="button button-danger" type="submit"><?= tohtml(_("Remove node")) ?></button>
										</form>
									</details>
								</div>
							</div>
						</article>
					<?php } ?>
				</div>
			<?php } ?>
		</section>

		<aside class="orbix-fleet-register" aria-labelledby="fleet-register-title">
			<p class="orbix-page-eyebrow"><?= tohtml(_("Trust ceremony")) ?></p>
			<h2 id="fleet-register-title"><?= tohtml(_("Register a node")) ?></h2>
			<p><?= tohtml(_("Registration performs a live three-command health probe before any credential is stored.")) ?></p>
			<form method="post" autocomplete="off">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="add_node" value="1">
				<label class="form-label" for="fleet-name"><?= tohtml(_("Node name")) ?></label>
				<input class="form-control" id="fleet-name" name="name" required maxlength="32" pattern="[a-z0-9][a-z0-9-]{0,31}" placeholder="edge-west">
				<div class="orbix-fleet-host-fields"><div><label class="form-label" for="fleet-host"><?= tohtml(_("HTTPS host")) ?></label><input class="form-control" id="fleet-host" name="host" required placeholder="panel.example.com"></div><div><label class="form-label" for="fleet-port"><?= tohtml(_("Port")) ?></label><input class="form-control" id="fleet-port" name="port" required inputmode="numeric" value="8083"></div></div>
				<label class="form-label" for="fleet-pin"><?= tohtml(_("SHA256 public-key pin")) ?></label>
				<input class="form-control u-console" id="fleet-pin" name="tls_pin" required placeholder="sha256//…">
				<small class="orbix-fleet-hint"><?= tohtml(_("Pin the server certificate public key. Certificate-authority bypass is accepted only with this exact pin.")) ?></small>
				<label class="form-label" for="fleet-access"><?= tohtml(_("Access key")) ?></label>
				<input class="form-control u-console" id="fleet-access" name="access_key" required minlength="20" maxlength="20" spellcheck="false">
				<label class="form-label" for="fleet-secret"><?= tohtml(_("Secret key")) ?></label>
				<input class="form-control u-console" id="fleet-secret" name="secret_key" type="password" required minlength="40" maxlength="40" spellcheck="false">
				<button class="button button-primary" type="submit"><i class="fas fa-shield-halved"></i><?= tohtml(_("Verify and Register")) ?></button>
			</form>
		</aside>
	</div>

	<?php if (!empty($fleet_jobs)) { ?>
		<section class="orbix-fleet-history" aria-labelledby="fleet-history-title">
			<div class="orbix-section-heading"><div><p class="orbix-page-eyebrow"><?= tohtml(_("Background activity")) ?></p><h2 id="fleet-history-title"><?= tohtml(_("Recent fleet refreshes")) ?></h2></div><a href="/list/fleet/"><?= tohtml(_("Refresh status")) ?></a></div>
			<div class="orbix-category-list">
				<?php foreach ($fleet_jobs as $job_id => $job) {
					$status = explode(":", $job["STATUS"] ?? "unknown", 2)[0];
					$icon = match ($status) { "completed" => "fa-circle-check icon-green", "completed_with_errors", "interrupted", "failed" => "fa-triangle-exclamation icon-red", default => "fa-circle-notch fa-spin icon-blue" };
				?>
					<div class="orbix-fleet-job"><i class="fas <?= tohtml($icon) ?>"></i><span><strong><?= tohtml(ucwords(str_replace("_", " ", $status))) ?></strong><small><?= tohtml($job["STARTED"] ?? "") ?> · <?= tohtml($job["TARGET"] === "all" ? _("Entire fleet") : $job["TARGET"]) ?></small></span><span><?= tohtml(sprintf(_("%s online · %s offline"), $job["ONLINE"] ?? 0, $job["OFFLINE"] ?? 0)) ?></span></div>
				<?php } ?>
			</div>
		</section>
	<?php } ?>
</div>
