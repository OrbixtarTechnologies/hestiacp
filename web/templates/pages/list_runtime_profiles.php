<?php
$runtime_installed_count = count($runtime_installed);
$runtime_extension_count = array_sum(
	array_map(fn($component) => count($component["extensions"]), $runtime_components),
);
$runtime_in_use_count = count(
	array_filter($runtime_components, fn($component) => !empty($component["domains"])),
);
$runtime_selected_json = htmlspecialchars(
	json_encode(array_values($runtime_installed), JSON_HEX_APOS | JSON_HEX_QUOT),
	ENT_QUOTES,
	"UTF-8",
);
?>

<div class="container orbix-dashboard orbix-runtime-manager">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Web stack build system")) ?></p>
			<h1><?= tohtml(_("Runtime Profiles")) ?></h1>
			<p><?= tohtml(_("Build and apply a deliberate MultiPHP set without removing a runtime that an active website still uses.")) ?></p>
		</div>
		<a class="button button-secondary" href="/edit/server/php/">
			<i class="fas fa-file-code"></i><?= tohtml(_("Edit PHP Configuration")) ?>
		</a>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<section class="orbix-runtime-console" aria-labelledby="runtime-console-title">
		<div class="orbix-runtime-console-copy">
			<p class="orbix-page-eyebrow"><?= tohtml(_("Active manifest")) ?></p>
			<h2 id="runtime-console-title"><?= tohtml(sprintf(_("PHP %s is the server default"), $runtime_default ?: "—")) ?></h2>
			<p><?= tohtml(_("Every profile is applied as a serialized background job: install first, switch the default, then remove only unused runtimes.")) ?></p>
		</div>
		<div class="orbix-runtime-signal" aria-label="<?= tohtml(_("Runtime summary")) ?>">
			<div><span><?= tohtml(_("Installed")) ?></span><strong><?= tohtml($runtime_installed_count) ?></strong></div>
			<div><span><?= tohtml(_("In use")) ?></span><strong><?= tohtml($runtime_in_use_count) ?></strong></div>
			<div><span><?= tohtml(_("Extensions")) ?></span><strong><?= tohtml($runtime_extension_count) ?></strong></div>
		</div>
	</section>

	<?php if (!$runtime_profile_supported) { ?>
		<section class="form-container">
			<h2><?= tohtml(_("MultiPHP profiles require PHP-FPM")) ?></h2>
			<p><?= tohtml(_("This server uses a web backend that cannot safely host parallel PHP runtimes. Migrate the web backend to PHP-FPM before applying a profile.")) ?></p>
			<a class="button button-secondary" href="/edit/server/"><i class="fas fa-gear"></i><?= tohtml(_("Open Server Configuration")) ?></a>
		</section>
	<?php } else { ?>
		<form
			method="post"
			class="orbix-runtime-workbench"
			x-data='{
				selected: <?= $runtime_selected_json ?>,
				original: <?= $runtime_selected_json ?>,
				defaultVersion: <?= json_encode($runtime_default, JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
				isSelected(version) { return this.selected.includes(version) },
				willInstall(version) { return this.isSelected(version) && !this.original.includes(version) },
				willRemove(version) { return !this.isSelected(version) && this.original.includes(version) },
				installCount() { return this.selected.filter((version) => !this.original.includes(version)).length },
				removeCount() { return this.original.filter((version) => !this.selected.includes(version)).length }
			}'
		>
			<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
			<input type="hidden" name="apply_profile" value="1">

			<div class="orbix-runtime-layout">
				<section class="orbix-runtime-catalog" aria-labelledby="runtime-catalog-title">
					<div class="orbix-section-heading">
						<div><p class="orbix-page-eyebrow"><?= tohtml(_("Component catalog")) ?></p><h2 id="runtime-catalog-title"><?= tohtml(_("Choose PHP runtimes")) ?></h2></div>
						<span><?= tohtml(sprintf(_("%d supported releases"), count($runtime_supported))) ?></span>
					</div>

					<div class="orbix-runtime-rows">
						<?php foreach ($runtime_components as $version => $component) {
							$protected = !empty($component["domains"]);
							$extension_preview = array_slice($component["extensions"], 0, 7);
						?>
							<article class="orbix-runtime-row" :class="{ 'is-selected': isSelected('<?= tohtml($version) ?>'), 'will-install': willInstall('<?= tohtml($version) ?>'), 'will-remove': willRemove('<?= tohtml($version) ?>') }">
								<div class="orbix-runtime-toggle">
									<?php if ($protected) { ?><input type="hidden" name="versions[]" value="<?= tohtml($version) ?>"><?php } ?>
									<input
										type="checkbox"
										class="form-check-input"
										id="runtime-<?= tohtml(str_replace(".", "-", $version)) ?>"
										name="versions[]"
										value="<?= tohtml($version) ?>"
										x-model="selected"
										<?= $protected ? "disabled" : "" ?>
									>
								</div>
								<label for="runtime-<?= tohtml(str_replace(".", "-", $version)) ?>" class="orbix-runtime-identity">
									<span>PHP</span><strong><?= tohtml($version) ?></strong>
								</label>
								<div class="orbix-runtime-state">
									<?php if ($component["default"]) { ?><span class="orbix-status-pill"><?= tohtml(_("Default")) ?></span><?php } ?>
									<?php if ($component["installed"]) { ?><span class="orbix-status-pill is-neutral"><?= tohtml(_("Installed")) ?></span><?php } else { ?><span class="orbix-status-pill is-outline"><?= tohtml(_("Available")) ?></span><?php } ?>
									<small x-show="willInstall('<?= tohtml($version) ?>')"><?= tohtml(_("Will install")) ?></small>
									<small x-show="willRemove('<?= tohtml($version) ?>')"><?= tohtml(_("Will remove")) ?></small>
								</div>
								<div class="orbix-runtime-usage">
									<strong><?= tohtml(sprintf(ngettext("%d website", "%d websites", count($component["domains"])), count($component["domains"]))) ?></strong>
									<small><?= $protected ? tohtml(_("Removal locked while assigned")) : tohtml(_("No direct assignments")) ?></small>
								</div>
								<div class="orbix-runtime-extensions">
									<strong><?= tohtml(sprintf(ngettext("%d extension", "%d extensions", count($component["extensions"])), count($component["extensions"]))) ?></strong>
									<small><?= !empty($extension_preview) ? tohtml(implode(" · ", $extension_preview)) : tohtml(_("Loaded after installation")) ?></small>
								</div>
							</article>
						<?php } ?>
					</div>
				</section>

				<aside class="orbix-runtime-build-sheet" aria-labelledby="runtime-build-title">
					<p class="orbix-page-eyebrow"><?= tohtml(_("Build sheet")) ?></p>
					<h2 id="runtime-build-title"><?= tohtml(_("Apply runtime profile")) ?></h2>
					<p><?= tohtml(_("The profile converges the server to exactly the selected runtime set.")) ?></p>

					<label class="form-label" for="runtime-default"><?= tohtml(_("Server default")) ?></label>
					<select class="form-select" id="runtime-default" name="default_version" x-model="defaultVersion">
						<?php foreach ($runtime_supported as $version) { ?>
							<option value="<?= tohtml($version) ?>" x-bind:disabled="!isSelected('<?= tohtml($version) ?>')"><?= tohtml("PHP " . $version) ?></option>
						<?php } ?>
					</select>

					<ol class="orbix-runtime-plan">
						<li><span>1</span><div><strong><?= tohtml(_("Install")) ?></strong><small><b x-text="installCount()"></b> <?= tohtml(_("new runtimes")) ?></small></div></li>
						<li><span>2</span><div><strong><?= tohtml(_("Switch")) ?></strong><small><?= tohtml(_("Default to")) ?> PHP <b x-text="defaultVersion"></b></small></div></li>
						<li><span>3</span><div><strong><?= tohtml(_("Retire")) ?></strong><small><b x-text="removeCount()"></b> <?= tohtml(_("unused runtimes")) ?></small></div></li>
					</ol>

					<label class="orbix-runtime-confirm">
						<input type="checkbox" class="form-check-input" name="confirm_profile" value="yes" required>
						<span><strong><?= tohtml(_("Confirm build plan")) ?></strong><small><?= tohtml(_("Package changes and service reloads run in the background. Directly assigned runtimes cannot be removed.")) ?></small></span>
					</label>
					<button class="button button-primary" type="submit" x-bind:disabled="selected.length === 0 || !isSelected(defaultVersion)">
						<i class="fas fa-cubes-stacked"></i><?= tohtml(_("Build and Apply Profile")) ?>
					</button>
				</aside>
			</div>
		</form>
	<?php } ?>

	<?php if (!empty($runtime_jobs)) { ?>
		<section class="orbix-runtime-history" aria-labelledby="runtime-history-title">
			<div class="orbix-section-heading"><div><p class="orbix-page-eyebrow"><?= tohtml(_("Deployment history")) ?></p><h2 id="runtime-history-title"><?= tohtml(_("Recent profile jobs")) ?></h2></div><a href="/list/runtime-profiles/"><?= tohtml(_("Refresh status")) ?></a></div>
			<div class="orbix-category-list">
				<?php foreach ($runtime_jobs as $job_id => $job) {
					$status_parts = explode(":", $job["STATUS"] ?? "unknown", 2);
					$status = $status_parts[0];
					$detail = $status_parts[1] ?? "";
					$icon = match ($status) {
						"completed" => "fa-circle-check icon-green",
						"failed", "interrupted" => "fa-triangle-exclamation icon-red",
						default => "fa-circle-notch fa-spin icon-blue",
					};
				?>
					<div class="orbix-runtime-job">
						<i class="fas <?= tohtml($icon) ?>"></i>
						<span><strong><?= tohtml("PHP " . str_replace(",", " · PHP ", $job["VERSIONS"] ?? "")) ?></strong><small><?= tohtml(ucwords(str_replace(["_", "-"], " ", $status))) ?><?= $detail !== "" ? " · " . tohtml($detail) : "" ?> · <?= tohtml($job["STARTED"] ?? "") ?></small></span>
						<span><?= tohtml(sprintf(_("Default PHP %s"), $job["DEFAULT_VERSION"] ?? "—")) ?></span>
					</div>
				<?php } ?>
			</div>
		</section>
	<?php } ?>
</div>
