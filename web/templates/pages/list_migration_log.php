<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Migration job")) ?></p>
			<h1><?= tohtml(_("Migration Log")) ?></h1>
			<p><?= tohtml($migration_job) ?></p>
		</div>
		<a class="button button-secondary" href="/list/migrations/">
			<i class="fas fa-arrow-left"></i><?= tohtml(_("Back to Migration Center")) ?>
		</a>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<section class="form-container" aria-label="<?= tohtml(_("Migration output")) ?>">
		<pre class="orbix-migration-log"><?= tohtml(implode("\n", $migration_log_lines)) ?></pre>
	</section>
</div>
