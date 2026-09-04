<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Queued message")) ?></p>
			<h1><?= tohtml($mail_queue_message_id) ?></h1>
			<p><?= tohtml(_("Review delivery headers and the Exim delivery log. Message bodies are intentionally excluded.")) ?></p>
		</div>
		<a class="button button-secondary" href="/list/mail-queue/"><i class="fas fa-arrow-left"></i><?= tohtml(_("Back to Mail Queue")) ?></a>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<div class="orbix-admin-overview">
		<section aria-labelledby="queue-message-detail-title">
			<div class="orbix-section-heading"><div><p class="orbix-page-eyebrow"><?= tohtml(_("Read-only evidence")) ?></p><h2 id="queue-message-detail-title"><?= tohtml(_("Headers and delivery log")) ?></h2></div></div>
			<pre class="orbix-queue-log"><?= tohtml($mail_queue_message_detail) ?></pre>
		</section>

		<aside class="orbix-queue-actions" aria-labelledby="queue-actions-title">
			<h2 id="queue-actions-title"><?= tohtml(_("Message actions")) ?></h2>
			<p><?= tohtml(_("Retry asks Exim to attempt delivery now. Delete permanently removes this message from the server queue.")) ?></p>
			<form method="post">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="message_id" value="<?= tohtml($mail_queue_message_id) ?>">
				<button class="button button-primary" type="submit" name="action" value="retry"><i class="fas fa-paper-plane"></i><?= tohtml(_("Retry Delivery")) ?></button>
			</form>
			<form method="post" class="orbix-queue-delete">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="message_id" value="<?= tohtml($mail_queue_message_id) ?>">
				<label class="form-check"><input class="form-check-input" type="checkbox" required><span><?= tohtml(_("I understand this message cannot be recovered.")) ?></span></label>
				<button class="button button-secondary button-danger" type="submit" name="action" value="delete"><i class="fas fa-trash"></i><?= tohtml(_("Delete Message")) ?></button>
			</form>
		</aside>
	</div>
</div>
