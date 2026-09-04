<div class="container orbix-dashboard">
	<header class="orbix-page-heading">
		<div>
			<p class="orbix-page-eyebrow"><?= tohtml(_("Server protection")) ?></p>
			<h1><?= tohtml(_("Security Center")) ?></h1>
			<p><?= tohtml(_("Review network defenses, administrator access, API credentials, updates, and security activity from one place.")) ?></p>
		</div>
		<a class="button button-primary" href="/list/log/auth/">
			<i class="fas fa-shield-halved"></i><?= tohtml(_("Review sign-ins")) ?>
		</a>
	</header>

	<section aria-labelledby="security-posture-title">
		<div class="orbix-section-heading">
			<div>
				<p class="orbix-page-eyebrow"><?= tohtml(_("Current state")) ?></p>
				<h2 id="security-posture-title"><?= tohtml(_("Protection overview")) ?></h2>
			</div>
		</div>
		<div class="orbix-action-grid">
			<a class="orbix-action-card" href="/list/firewall/">
				<i class="fas fa-shield"></i>
				<span>
					<strong><?= tohtml(_("Firewall")) ?></strong>
					<small><?= $security_summary["firewall_enabled"] ? tohtml(sprintf(ngettext("%d managed rule", "%d managed rules", $security_summary["firewall_rules"]), $security_summary["firewall_rules"])) : tohtml(_("Not configured")) ?></small>
				</span>
				<i class="fas fa-chevron-right"></i>
			</a>
			<a class="orbix-action-card" href="/list/firewall/banlist/">
				<i class="fas fa-user-shield"></i>
				<span>
					<strong><?= tohtml(_("Blocked addresses")) ?></strong>
					<small><?= tohtml(sprintf(ngettext("%d active ban", "%d active bans", $security_summary["banned_addresses"]), $security_summary["banned_addresses"])) ?></small>
				</span>
				<i class="fas fa-chevron-right"></i>
			</a>
			<a class="orbix-action-card" href="/list/access-key/">
				<i class="fas fa-key"></i>
				<span>
					<strong><?= tohtml(_("API access")) ?></strong>
					<small><?= $security_summary["api_enabled"] ? tohtml(sprintf(ngettext("Enabled · %d key", "Enabled · %d keys", $security_summary["access_keys"]), $security_summary["access_keys"])) : tohtml(_("Disabled")) ?></small>
				</span>
				<i class="fas fa-chevron-right"></i>
			</a>
			<a class="orbix-action-card" href="/edit/user/?<?= tohtml(http_build_query(["user" => $user_plain, "token" => $_SESSION["token"]])) ?>">
				<i class="fas fa-mobile-screen-button"></i>
				<span>
					<strong><?= tohtml(_("Administrator 2FA")) ?></strong>
					<small><?= $security_summary["two_factor_enabled"] ? tohtml(_("Enabled")) : tohtml(_("Needs attention")) ?></small>
				</span>
				<i class="fas fa-chevron-right"></i>
			</a>
		</div>
	</section>

	<div class="orbix-admin-overview">
		<section aria-labelledby="security-tools-title">
			<div class="orbix-section-heading">
				<h2 id="security-tools-title"><?= tohtml(_("Security workflows")) ?></h2>
			</div>
			<div class="orbix-favorites-grid">
				<a class="orbix-tool-card" href="/list/firewall/"><i class="fas fa-shield-halved"></i><span><strong><?= tohtml(_("Firewall rules")) ?></strong><small><?= tohtml(_("Allow, reject, suspend, and prioritize network rules.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/firewall/ipset/"><i class="fas fa-list-check"></i><span><strong><?= tohtml(_("IP lists")) ?></strong><small><?= tohtml(_("Maintain reusable address lists for firewall policy.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/log/auth/"><i class="fas fa-user-clock"></i><span><strong><?= tohtml(_("Sign-in history")) ?></strong><small><?= tohtml(_("Inspect recent authentication activity for the administrator.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/access-key/"><i class="fas fa-code"></i><span><strong><?= tohtml(_("API credentials")) ?></strong><small><?= tohtml(_("Create and revoke access-scoped automation keys.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/edit/server/"><i class="fas fa-lock"></i><span><strong><?= tohtml(_("Panel hardening")) ?></strong><small><?= tohtml(_("Configure TLS, API policy, access restrictions, and service security.")) ?></small></span></a>
				<a class="orbix-tool-card" href="/list/updates/"><i class="fas fa-arrows-rotate"></i><span><strong><?= tohtml(_("Security updates")) ?></strong><small><?= tohtml(_("Review available panel and operating-system updates.")) ?></small></span></a>
			</div>
		</section>

		<aside class="orbix-statistics-panel" aria-labelledby="security-activity-title">
			<h2 id="security-activity-title"><?= tohtml(_("Activity")) ?></h2>
			<dl>
				<div><dt><?= tohtml(_("Recent sign-in events")) ?></dt><dd><?= tohtml($security_summary["authentication_events"]) ?></dd></div>
				<div><dt><?= tohtml(_("Firewall rules")) ?></dt><dd><?= tohtml($security_summary["firewall_rules"]) ?></dd></div>
				<div><dt><?= tohtml(_("Blocked addresses")) ?></dt><dd><?= tohtml($security_summary["banned_addresses"]) ?></dd></div>
				<div><dt><?= tohtml(_("API keys")) ?></dt><dd><?= tohtml($security_summary["access_keys"]) ?></dd></div>
			</dl>
			<a class="button button-secondary" href="/list/log/?<?= tohtml(http_build_query(["user" => "system", "token" => $_SESSION["token"]])) ?>"><i class="fas fa-binoculars"></i><?= tohtml(_("Open system logs")) ?></a>
		</aside>
	</div>
</div>
