<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Certificate automation")) ?></p>
			<h1><?= tohtml(_("AutoSSL Job")) ?></h1>
			<p><?= tohtml($ssl_autossl_job_id) ?></p>
		</div>
		<a class="button button-secondary" href="/list/ssl-status/"><i class="fas fa-arrow-left"></i><?= tohtml(_("Back to SSL/TLS Status")) ?></a>
	</header>

	<section aria-labelledby="autossl-job-output-title">
		<div class="orbix-section-heading">
			<div><p class="orbix-page-eyebrow"><?= tohtml(_("Issuance output")) ?></p><h2 id="autossl-job-output-title"><?= tohtml(_("Latest job log")) ?></h2></div>
			<a href="/list/ssl-status/job/?<?= tohtml(http_build_query(["job" => $ssl_autossl_job_id])) ?>"><?= tohtml(_("Refresh log")) ?></a>
		</div>
		<pre class="orbix-autossl-log"><?= tohtml($ssl_autossl_job_log ?: _("This job has not produced output yet.")) ?></pre>
	</section>
</div>
