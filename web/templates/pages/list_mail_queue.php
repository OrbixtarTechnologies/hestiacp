<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Delivery operations")) ?></p>
			<h1><?= tohtml(_("Mail Queue")) ?></h1>
			<p><?= tohtml(_("Inspect messages that Exim has not delivered yet, identify frozen deliveries, and open an individual message for safe action.")) ?></p>
		</div>
		<a class="button button-secondary" href="/list/server-health/"><i class="fas fa-heart-pulse"></i><?= tohtml(_("Server Health")) ?></a>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<section class="orbix-queue-rail" data-status="<?= $mail_queue_frozen > 0 ? "attention" : "normal" ?>" aria-labelledby="mail-queue-summary">
		<div>
			<i class="fas <?= $mail_queue_frozen > 0 ? "fa-snowflake" : "fa-envelope-circle-check" ?>" aria-hidden="true"></i>
			<div>
				<p class="orbix-page-eyebrow"><?= tohtml(_("Exim delivery queue")) ?></p>
				<h2 id="mail-queue-summary"><?= tohtml((int) ($mail_queue_summary["TOTAL"] ?? 0) === 0 ? _("The mail queue is clear") : sprintf(ngettext("%d message is waiting", "%d messages are waiting", (int) ($mail_queue_summary["TOTAL"] ?? 0)), (int) ($mail_queue_summary["TOTAL"] ?? 0))) ?></h2>
			</div>
		</div>
		<dl>
			<div><dt><?= tohtml(_("Queued")) ?></dt><dd><?= tohtml((int) ($mail_queue_summary["TOTAL"] ?? 0)) ?></dd></div>
			<div><dt><?= tohtml(_("Frozen")) ?></dt><dd><?= tohtml($mail_queue_frozen) ?></dd></div>
			<div><dt><?= tohtml(_("Shown")) ?></dt><dd><?= tohtml((int) ($mail_queue_summary["RETURNED"] ?? 0)) ?></dd></div>
		</dl>
		<a href="/list/mail-queue/"><i class="fas fa-rotate"></i><?= tohtml(_("Refresh queue")) ?></a>
	</section>

	<?php if (($mail_queue_summary["TRUNCATED"] ?? "no") === "yes") { ?>
		<div class="alert alert-info" role="status"><?= tohtml(_("Showing the first 250 queued messages. Use Exim command-line tools for the complete queue.")) ?></div>
	<?php } ?>

	<?php if (empty($mail_queue_messages)) { ?>
		<section class="orbix-queue-empty"><i class="fas fa-envelope-circle-check"></i><div><h2><?= tohtml(_("No delayed deliveries")) ?></h2><p><?= tohtml(_("Exim is not currently holding any messages for another delivery attempt.")) ?></p></div></section>
	<?php } else { ?>
		<section class="orbix-queue-list" aria-label="<?= tohtml(_("Queued messages")) ?>">
			<div class="orbix-queue-list-heading" aria-hidden="true"><span><?= tohtml(_("State")) ?></span><span><?= tohtml(_("Message")) ?></span><span><?= tohtml(_("Route")) ?></span><span><?= tohtml(_("Age / size")) ?></span><span></span></div>
			<?php foreach ($mail_queue_messages as $message_id => $message) {
				$is_frozen = ($message["FROZEN"] ?? "no") === "yes";
			?>
				<article class="orbix-queue-row" data-status="<?= $is_frozen ? "frozen" : "waiting" ?>">
					<div class="orbix-queue-state"><i class="fas <?= $is_frozen ? "fa-snowflake" : "fa-clock" ?>"></i><span><?= tohtml($is_frozen ? _("Frozen") : _("Waiting")) ?></span></div>
					<div class="orbix-queue-message"><strong><?= tohtml($message_id) ?></strong><small><?= tohtml(($message["SENDER"] ?? "") ?: _("Bounce message")) ?></small></div>
					<div class="orbix-queue-route"><span><?= tohtml(_("To")) ?></span><strong><?= tohtml(($message["RECIPIENTS"] ?? "") ?: _("Recipient unavailable")) ?></strong></div>
					<div class="orbix-queue-age"><strong><?= tohtml($message["AGE"] ?? "—") ?></strong><small><?= tohtml($message["SIZE"] ?? "—") ?></small></div>
					<a class="button button-secondary" href="/list/mail-queue/message/?<?= tohtml(http_build_query(["id" => $message_id])) ?>"><i class="fas fa-magnifying-glass"></i><?= tohtml(_("Inspect")) ?></a>
				</article>
			<?php } ?>
		</section>
	<?php } ?>
</div>
