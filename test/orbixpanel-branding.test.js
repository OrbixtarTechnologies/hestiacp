import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

const repositoryRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const read = (relativePath) => fs.readFileSync(path.join(repositoryRoot, relativePath), 'utf8');

const walkFiles = (relativeDirectory) => {
	const absoluteDirectory = path.join(repositoryRoot, relativeDirectory);
	return fs.readdirSync(absoluteDirectory, { withFileTypes: true }).flatMap((entry) => {
		const relativePath = path.join(relativeDirectory, entry.name);
		return entry.isDirectory() ? walkFiles(relativePath) : [relativePath];
	});
};

test('project-owned public surfaces use the OrbixPanel identity', () => {
	const packageManifest = JSON.parse(read('package.json'));
	assert.equal(packageManifest.name, 'orbixpanel');
	assert.equal(
		packageManifest.repository,
		'https://github.com/OrbixtarTechnologies/orbixtar-panel',
	);

	const publicSurfaces = [
		'README.md',
		'SECURITY.md',
		'CONTRIBUTING.md',
		'docs/index.md',
		'docs/.vitepress/config.js',
		'docs/public/site.webmanifest',
		'.github/ISSUE_TEMPLATE/BUG-REPORT.yml',
		'.github/ISSUE_TEMPLATE/FEATURE-REQUEST.yml',
	];
	const forbiddenIdentity = /forum\.hestiacp|docs\.hestiacp|demo\.hestiacp|github\.com\/hestiacp/i;

	for (const surface of publicSurfaces) {
		assert.doesNotMatch(read(surface), forbiddenIdentity, `${surface} links to upstream branding`);
	}

	const docsConfig = read('docs/.vitepress/config.js');
	const webManifest = JSON.parse(read('docs/public/site.webmanifest'));
	assert.match(docsConfig, /title: 'OrbixPanel'/);
	assert.match(docsConfig, /provider: 'local'/);
	assert.equal(webManifest.name, 'OrbixPanel');
	assert.equal(webManifest.theme_color, '#1769e0');
});

test('maintained documentation and runtime copy contain no stale upstream product name', () => {
	const documentationFiles = walkFiles('docs').filter(
		(relativePath) =>
			!relativePath.includes(`${path.sep}node_modules${path.sep}`) &&
			!relativePath.includes(`${path.sep}docs${path.sep}community${path.sep}`) &&
			relativePath !== path.join('docs', 'team.md'),
	);
	const runtimeFiles = ['bin', 'func', 'install', 'src', 'web']
		.flatMap(walkFiles)
		.filter(
			(relativePath) =>
				!relativePath.includes(`${path.sep}vendor${path.sep}`) &&
				!relativePath.includes(`${path.sep}node_modules${path.sep}`) &&
				!relativePath.endsWith(path.join('install', 'common', 'roundcube', 'hestia.php')) &&
				!(
					relativePath.startsWith(path.join('src', 'deb')) &&
					path.basename(relativePath) === 'copyright'
				),
		);
	const staleProductName = /HestiaCP|Hestia Control Panel/;

	for (const relativePath of [...documentationFiles, ...runtimeFiles]) {
		const content = read(relativePath);
		if (content.includes('\0')) continue;
		assert.doesNotMatch(content, staleProductName, `${relativePath} has stale branding`);
	}
});

test('the install overlay ships every declared runtime file', () => {
	const overlay = read('install/orbixpanel-overlay.sh');
	const declaredPaths = [...overlay.matchAll(/^\s*"((?:bin|web|func|install)\/[^"]+)"\s*$/gm)].map(
		(match) => match[1],
	);

	assert.ok(declaredPaths.length >= 30, 'overlay unexpectedly lost its runtime file inventory');
	for (const relativePath of declaredPaths) {
		assert.ok(
			fs.existsSync(path.join(repositoryRoot, relativePath)),
			`overlay file does not exist: ${relativePath}`,
		);
	}

	for (const requiredPath of [
		'bin/v-check-mail-domain-dns',
		'bin/v-delete-sys-mail-queue-message',
		'bin/v-get-autossl-log',
		'bin/v-get-cpanel-import-log',
		'bin/v-get-sys-mail-queue-message',
		'bin/v-list-cpanel-backups',
		'bin/v-list-cpanel-imports',
		'bin/v-list-autossl-jobs',
		'bin/v-list-sys-health-summary',
		'bin/v-list-sys-mail-queue',
		'bin/v-list-web-domain-ssl-status',
		'bin/v-retry-sys-mail-queue-message',
		'bin/v-start-cpanel-import',
		'bin/v-start-cpanel-remote-transfer',
		'bin/v-start-autossl-user',
		'bin/v-add-reseller-user',
		'bin/v-change-user-reseller',
		'bin/v-delete-reseller-user',
		'bin/v-list-reseller-users',
		'web/templates/pages/dashboard.php',
		'web/templates/pages/list_hosting.php',
		'web/templates/pages/list_security.php',
		'web/list/hosting/index.php',
		'web/list/mail-deliverability/index.php',
		'web/list/mail-queue/index.php',
		'web/list/mail-queue/message/index.php',
		'web/list/migrations/index.php',
		'web/list/migrations/log/index.php',
		'web/list/security/index.php',
		'web/list/reseller/index.php',
		'web/list/server-health/index.php',
		'web/list/ssl-status/index.php',
		'web/list/ssl-status/job/index.php',
		'web/js/orbixpanel-overlay.min.js',
		'web/api/index.php',
		'web/execute/bootstrap.php',
		'web/execute/Email/list_pops/index.php',
		'web/execute/WebVhosts/list_domains/index.php',
		'func/upgrade.sh',
	]) {
		assert.ok(declaredPaths.includes(requiredPath), `overlay does not deliver ${requiredPath}`);
	}
});

test('Email Deliverability checks public mail authentication DNS for owned domains', () => {
	const command = read('bin/v-check-mail-domain-dns');
	const route = read('web/list/mail-deliverability/index.php');
	const page = read('web/templates/pages/list_mail_deliverability.php');
	const dashboard = read('web/templates/pages/dashboard.php');
	const navigation = read('web/templates/includes/panel.php');
	const mailList = read('web/templates/pages/list_mail.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(command, /is_object_valid 'mail' 'DOMAIN' "\$domain"/);
	assert.match(command, /timeout 12 dig/);
	for (const record of ['MX', 'v=spf1', 'v=DKIM1', 'v=DMARC1']) {
		assert.match(command, new RegExp(record));
	}
	assert.match(route, /array_key_exists\(\$deliverability_domain, \$deliverability_domains\)/);
	assert.match(route, /v-check-mail-domain-dns/);
	assert.match(page, /Email Deliverability/);
	assert.match(page, /Authentication path/);
	assert.match(dashboard, /href="\/list\/mail-deliverability\/"/);
	assert.match(navigation, /data-orbix-url="\/list\/mail-deliverability\/"/);
	assert.match(mailList, /href="\/list\/mail-deliverability\/"/);
	assert.match(compiledTheme, /orbix-dns-checks/);
});

test('SSL/TLS Status inventories certificate health without exposing private keys', () => {
	const command = read('bin/v-list-web-domain-ssl-status');
	const route = read('web/list/ssl-status/index.php');
	const page = read('web/templates/pages/list_ssl_status.php');
	const dashboard = read('web/templates/pages/dashboard.php');
	const navigation = read('web/templates/includes/panel.php');
	const webList = read('web/templates/pages/list_web.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(command, /is_object_valid 'web' 'DOMAIN' "\$domain"/);
	assert.match(command, /fingerprint -sha256/);
	assert.doesNotMatch(command, /\.key|"KEY"/);
	assert.match(route, /v-list-web-domain-ssl-status/);
	assert.match(route, /strtotime\("\+30 days"/);
	for (const status of ['valid', 'expiring', 'expired', 'unsecured', 'unknown']) {
		assert.match(route, new RegExp(`"${status}"`));
	}
	assert.match(page, /SSL\/TLS Status/);
	assert.match(page, /Manage Certificate/);
	assert.match(dashboard, /href="\/list\/ssl-status\/"/);
	assert.match(navigation, /data-orbix-url="\/list\/ssl-status\/"/);
	assert.match(webList, /href="\/list\/ssl-status\/"/);
	assert.match(compiledTheme, /orbix-certificate-summary/);
});

test('AutoSSL runs selected certificate repairs as tracked account jobs', () => {
	const starter = read('bin/v-start-autossl-user');
	const jobs = read('bin/v-list-autossl-jobs');
	const log = read('bin/v-get-autossl-log');
	const route = read('web/list/ssl-status/index.php');
	const logRoute = read('web/list/ssl-status/job/index.php');
	const page = read('web/templates/pages/list_ssl_status.php');
	const navigation = read('web/templates/includes/panel.php');
	const overlay = read('install/orbixpanel-overlay.sh');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(starter, /v-add-letsencrypt-domain/);
	assert.match(starter, /flock -n/);
	assert.match(starter, /flock -x 8/);
	assert.match(starter, /already active for \$user/);
	assert.match(starter, /sleep 10/);
	assert.match(starter, /openssl x509 -checkend 2592000/);
	assert.match(starter, /completed_with_errors/);
	assert.match(starter, /requested_domains\[@\]/);
	assert.match(starter, /\$\{#requested_domains\[@\]\} > 100/);
	assert.match(jobs, /JOB_USER.*== "\$user"/);
	assert.match(jobs, /interrupted:/);
	assert.match(log, /job_user.*!= "\$user"/);
	assert.match(log, /tail -n 300/);
	assert.match(route, /verify_csrf\(\$_POST\)/);
	assert.match(route, /array_key_exists\(\$domain, \$ssl_status_domains\)/);
	assert.match(route, /\["status"\] !== "valid"/);
	assert.match(route, /v-start-autossl-user/);
	assert.match(logRoute, /v-get-autossl-log/);
	assert.match(page, /Run AutoSSL for Selected/);
	assert.match(page, /Recent AutoSSL jobs/);
	assert.match(navigation, /data-orbix-url="\/list\/ssl-status\/"/);
	assert.match(overlay, /bin\/v-start-autossl-user/);
	assert.match(compiledTheme, /orbix-autossl-pipeline/);
});

test('Migration Center securely queues and tracks cPanel imports', () => {
	const pageRoute = read('web/list/migrations/index.php');
	const logRoute = read('web/list/migrations/log/index.php');
	const starter = read('bin/v-start-cpanel-import');
	const remoteStarter = read('bin/v-start-cpanel-remote-transfer');
	const importer = read('bin/v-import-cpanel');
	const overlay = read('install/orbixpanel-overlay.sh');
	const dashboard = read('web/templates/pages/dashboard.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(pageRoute, /\$_SESSION\["userContext"\] !== "admin"/);
	assert.match(pageRoute, /verify_csrf\(\$_POST\)/);
	assert.match(pageRoute, /array_key_exists\(\$archive, \$migration_archives\)/);
	assert.match(pageRoute, /v-start-cpanel-import/);
	assert.match(logRoute, /v-get-cpanel-import-log/);
	assert.match(starter, /gzip -t/);
	assert.match(starter, /unsafe archive path/);
	assert.match(starter, /nohup \/bin\/bash/);
	assert.match(starter, /flock -n/);
	assert.match(remoteStarter, /ssh-keyscan -T 10/);
	assert.match(remoteStarter, /ssh-keygen -lf .* -E sha256/);
	assert.match(remoteStarter, /grep -Fxq "\$fingerprint"/);
	assert.match(remoteStarter, /printf "%s\\n" "\$key_line" > "\$known_hosts"/);
	assert.match(remoteStarter, /remote_size=\$\(ssh/);
	assert.match(remoteStarter, /insufficient backup storage/);
	assert.match(remoteStarter, /sftp -b "\$batch_file"/);
	assert.match(remoteStarter, /StrictHostKeyChecking=yes/);
	assert.match(remoteStarter, /BatchMode=yes/);
	assert.match(remoteStarter, /reget/);
	assert.match(remoteStarter, /gzip -t/);
	assert.match(remoteStarter, /ln -- "\$partial_file" "\$final_file"/);
	assert.doesNotMatch(remoteStarter, /password/i);
	assert.match(importer, /--no-same-owner --no-same-permissions/);
	assert.match(importer, /exactly one cPanel account directory/);
	assert.match(importer, /is_user_format_valid "\$new_user"/);
	assert.match(importer, /is_domain_format_valid "\$main_domain1"/);
	assert.match(importer, /Unsafe document-root path/);
	assert.match(importer, /DATE='\$date'/);
	assert.match(overlay, /bin\/\* \| install\/orbixpanel-overlay\.sh\) file_mode=0755/);
	assert.match(dashboard, /href="\/list\/migrations\/"/);
	assert.match(pageRoute, /v-start-cpanel-remote-transfer/);
	assert.match(read('web/templates/pages/list_migrations.php'), /Key authentication only/);
	assert.match(compiledTheme, /orbix-migration-log/);
});

test('Hosting Overview aggregates the customer hosting workflow', () => {
	const route = read('web/list/hosting/index.php');
	const page = read('web/templates/pages/list_hosting.php');
	const dashboard = read('web/templates/pages/dashboard.php');
	const navigation = read('web/templates/includes/panel.php');

	for (const command of [
		'v-list-web-domains',
		'v-list-dns-domains',
		'v-list-mail-domains',
		'v-list-databases',
		'v-list-cron-jobs',
		'v-list-user-backups',
	]) {
		assert.match(route, new RegExp(command));
	}
	assert.match(route, /render_page\(\$user, \$TAB, "list_hosting"\)/);
	assert.match(page, /Hosting Overview/);
	assert.match(page, /Backup Manager/);
	assert.match(dashboard, /href="\/list\/hosting\/"/);
	assert.match(navigation, /data-orbix-url="\/list\/hosting\/"/);
});

test('Reseller Center enforces owner-scoped customer delegation', () => {
	const enable = read('bin/v-change-user-reseller');
	const create = read('bin/v-add-reseller-user');
	const list = read('bin/v-list-reseller-users');
	const status = read('bin/v-change-reseller-user-status');
	const remove = read('bin/v-delete-reseller-user');
	const core = read('func/main.sh');
	const route = read('web/list/reseller/index.php');
	const deleteRoute = read('web/delete/reseller/index.php');
	const page = read('web/templates/pages/list_reseller.php');
	const navigation = read('web/templates/includes/panel.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(enable, /only root-owned user accounts can become resellers/);
	assert.match(enable, /reseller still owns \$owned_count account/);
	assert.match(enable, /RESELLER_PACKAGES/);
	assert.match(create, /flock -n 9/);
	assert.match(create, /owned_count.*OWNER/);
	assert.match(create, /package \$package is not assigned/);
	assert.match(create, /v-add-user/);
	assert.match(list, /OWNER='\$reseller'/);
	for (const command of [status, remove]) {
		assert.match(command, /is_reseller_valid "\$reseller"/);
		assert.match(command, /is_reseller_user_valid "\$reseller" "\$user"/);
	}
	assert.match(core, /managed_owner.*!= "\$reseller"/s);
	assert.match(route, /\$_SESSION\["userContext"\] !== "user"/);
	assert.match(route, /verify_csrf\(\$_POST\)/);
	assert.match(route, /v-add-reseller-user/);
	assert.match(deleteRoute, /verify_csrf\(\$_GET\)/);
	assert.match(deleteRoute, /v-delete-reseller-user/);
	assert.match(page, /Owner-scoped access/);
	assert.match(navigation, /data-orbix-url="\/list\/reseller\/"/);
	assert.match(compiledTheme, /orbix-reseller-ledger/);
});

test('Security Center is a real administrator-only route', () => {
	const route = read('web/list/security/index.php');
	const dashboard = read('web/templates/pages/dashboard.php');
	const navigation = read('web/templates/includes/panel.php');

	assert.match(route, /\$_SESSION\["userContext"\] !== "admin"/);
	assert.match(route, /render_page\(\$user, \$TAB, "list_security"\)/);
	assert.match(dashboard, /href="\/list\/security\/"/);
	assert.match(navigation, /data-orbix-url="\/list\/security\/"/);
});

test('Server Health provides administrator-only capacity and service triage', () => {
	const command = read('bin/v-list-sys-health-summary');
	const route = read('web/list/server-health/index.php');
	const page = read('web/templates/pages/list_server_health.php');
	const dashboard = read('web/templates/pages/dashboard.php');
	const navigation = read('web/templates/includes/panel.php');
	const services = read('web/templates/pages/list_services.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(command, /df -Pk \//);
	assert.match(command, /free -m/);
	assert.match(command, /_NPROCESSORS_ONLN/);
	assert.match(route, /\$_SESSION\["userContext"\] !== "admin"/);
	assert.match(route, /!empty\(\$_SESSION\["look"\]\)/);
	for (const commandName of [
		'v-list-sys-info',
		'v-list-sys-health-summary',
		'v-list-sys-services',
	]) {
		assert.match(route, new RegExp(commandName));
	}
	assert.match(page, /Server Health/);
	assert.match(page, /Service exceptions/);
	assert.match(dashboard, /href="\/list\/server-health\/"/);
	assert.match(navigation, /data-orbix-url="\/list\/server-health\/"/);
	assert.match(services, /href="\/list\/server-health\/"/);
	assert.match(compiledTheme, /orbix-health-banner/);
});

test('Mail Queue provides safe administrator-only message triage', () => {
	const listCommand = read('bin/v-list-sys-mail-queue');
	const detailCommand = read('bin/v-get-sys-mail-queue-message');
	const retryCommand = read('bin/v-retry-sys-mail-queue-message');
	const deleteCommand = read('bin/v-delete-sys-mail-queue-message');
	const listRoute = read('web/list/mail-queue/index.php');
	const messageRoute = read('web/list/mail-queue/message/index.php');
	const listPage = read('web/templates/pages/list_mail_queue.php');
	const messagePage = read('web/templates/pages/list_mail_queue_message.php');
	const dashboard = read('web/templates/pages/dashboard.php');
	const navigation = read('web/templates/includes/panel.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(listCommand, /exim -bpc/);
	assert.match(listCommand, /exim -bp/);
	assert.match(listCommand, /mktemp \/tmp\/orbixpanel-mail-queue/);
	assert.match(listCommand, /Unable to read the complete Exim mail queue/);
	assert.match(listCommand, /head -n 250/);
	assert.match(detailCommand, /exim -Mvh/);
	assert.match(detailCommand, /exim -Mvl/);
	assert.doesNotMatch(detailCommand, /exim -Mvb/);
	assert.match(retryCommand, /exim -M "\$message_id"/);
	assert.match(deleteCommand, /exim -Mrm "\$message_id"/);
	for (const command of [detailCommand, retryCommand, deleteCommand]) {
		assert.match(command, /\^\[\[:alnum:\]\]\{6,\}-/);
	}
	assert.match(listRoute, /\$_SESSION\["userContext"\] !== "admin"/);
	assert.match(listRoute, /!empty\(\$_SESSION\["look"\]\)/);
	assert.match(messageRoute, /verify_csrf\(\$_POST\)/);
	assert.match(messageRoute, /quoteshellarg\(\$mail_queue_message_id\)/);
	assert.match(listPage, /Mail Queue/);
	assert.match(messagePage, /Message bodies are intentionally excluded/);
	assert.match(messagePage, /type="checkbox" required/);
	assert.match(dashboard, /href="\/list\/mail-queue\/"/);
	assert.match(navigation, /data-orbix-url="\/list\/mail-queue\/"/);
	assert.match(compiledTheme, /orbix-queue-rail/);
});

test('the API exposes OrbixPanel identity without breaking legacy clients', () => {
	const api = read('web/api/index.php');
	assert.match(api, /header\("OrbixPanel-Exit-Code: \$exit_code"\)/);
	assert.match(api, /header\("X-OrbixPanel-API-Version: 1"\)/);
	assert.match(api, /header\("Hestia-Exit-Code: \$exit_code"\)/);
	assert.match(api, /api_response_headers\(\$cmd_exit_code\)/);
});

test('the cPanel UAPI compatibility layer preserves access-key permissions and ownership', () => {
	const bootstrap = read('web/execute/bootstrap.php');
	const mailDomains = read('web/execute/Email/list_mail_domains/index.php');
	const mailboxes = read('web/execute/Email/list_pops/index.php');
	const mailboxCount = read('web/execute/Email/count_pops/index.php');
	const webVhosts = read('web/execute/WebVhosts/list_domains/index.php');
	const nginx = read('install/common/nginx/orbix-uapi.conf');
	const permission = read('install/common/api/uapi-read');
	const overlay = read('install/orbixpanel-overlay.sh');

	assert.match(bootstrap, /function orbix_compat_credentials/);
	assert.match(bootstrap, /orbix_compat_credentials\(\$module, \$function, "cpanel"/);
	assert.match(bootstrap, /v-check-access-key/);
	assert.match(bootstrap, /\$keyData\["USER"\].*\$credentials\["user"\]/s);
	assert.match(bootstrap, /API_ALLOWED_IP/);
	assert.match(bootstrap, /API_SYSTEM/);
	assert.match(bootstrap, /array_unique\(\$commands\)/);
	assert.match(bootstrap, /\["GET", "HEAD"\]/);
	assert.match(bootstrap, /function orbix_uapi_query/);
	assert.match(bootstrap, /function orbix_uapi_allow_query_params/);
	assert.match(bootstrap, /Unsupported query parameter/);
	assert.match(mailDomains, /v-list-mail-domains/);
	assert.match(mailDomains, /\["select"\]/);
	assert.match(mailboxes, /v-list-mail-accounts/);
	assert.match(mailboxes, /\["domain", "skip_main"\]/);
	assert.match(mailboxes, /\$credentials\["user"\]/);
	assert.match(mailboxCount, /\["ACCOUNTS"\]/);
	assert.match(webVhosts, /v-list-web-domains/);
	assert.match(permission, /ROLE='user'/);
	assert.match(permission, /v-list-mail-domains/);
	assert.match(permission, /v-list-web-domains/);
	assert.match(nginx, /\^\/execute\//);
	assert.match(nginx, /HTTP_AUTHORIZATION \$http_authorization/);
	assert.match(overlay, /hestia-nginx" -t/);
	assert.match(overlay, /nginx\.conf\.before-uapi/);
	for (const endpoint of [mailDomains, mailboxes, mailboxCount, webVhosts]) {
		assert.match(endpoint, /orbix_uapi_bootstrap/);
		assert.match(endpoint, /orbix_uapi_response/);
	}
});

test('the WHM API compatibility layer limits account inventory by administrator or reseller ownership', () => {
	const bootstrap = read('web/json-api/bootstrap.php');
	const listAccounts = read('web/json-api/listaccts/index.php');
	const accountSummary = read('web/json-api/accountsummary/index.php');
	const ownedUsers = read('bin/v-list-owned-users');
	const ownedUser = read('bin/v-list-owned-user');
	const nginx = read('install/common/nginx/orbix-uapi.conf');
	const permission = read('install/common/api/whm-read');
	const overlay = read('install/orbixpanel-overlay.sh');

	assert.match(bootstrap, /ORBIX_API_COMPATIBILITY", "WHM-API-1/);
	assert.match(bootstrap, /orbix_compat_credentials\("WHM", \$command, "whm"/);
	assert.match(bootstrap, /api_version/);
	assert.match(bootstrap, /\["GET", "HEAD"\]/);
	assert.match(listAccounts, /v-list-owned-users/);
	assert.match(listAccounts, /\["acct" => \$data\]/);
	assert.match(accountSummary, /v-list-owned-user/);
	assert.match(accountSummary, /A valid user parameter is required/);
	assert.match(ownedUsers, /if \[ "\$user" = "\$ROOT_USER" \]/);
	assert.match(ownedUsers, /is_reseller_valid "\$user"/);
	assert.match(ownedUser, /is_reseller_user_valid "\$user" "\$account"/);
	assert.match(ownedUser, /is not a hosting account/);
	assert.match(permission, /ROLE='user'/);
	assert.match(permission, /v-list-owned-users,v-list-owned-user/);
	assert.match(nginx, /\^\/json-api\/\(listaccts\|accountsummary\)/);
	assert.match(overlay, /install\/common\/api\/whm-read/);
});

test('Runtime Profiles serializes MultiPHP builds and blocks unsafe runtime removal', () => {
	const route = read('web/list/runtime-profiles/index.php');
	const page = read('web/templates/pages/list_runtime_profiles.php');
	const starter = read('bin/v-start-runtime-profile');
	const jobs = read('bin/v-list-runtime-profile-jobs');
	const extensions = read('bin/v-list-sys-php-extensions');
	const installer = read('bin/v-add-web-php');
	const remover = read('bin/v-delete-web-php');
	const navigation = read('web/templates/includes/panel.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(route, /\$_SESSION\["userContext"\] !== "admin"/);
	assert.match(route, /!empty\(\$_SESSION\["look"\]\)/);
	assert.match(route, /verify_csrf\(\$_POST\)/);
	assert.match(route, /v-start-runtime-profile/);
	assert.match(route, /v-list-sys-php-extensions/);
	assert.match(page, /Build and Apply Profile/);
	assert.match(page, /Directly assigned runtimes cannot be removed/);
	assert.match(starter, /flock -x 8/);
	assert.match(starter, /v-add-web-php/);
	assert.match(starter, /v-change-sys-php/);
	assert.match(starter, /v-delete-web-php/);
	assert.match(starter, /running:install/);
	assert.match(starter, /running:default/);
	assert.match(starter, /running:remove/);
	assert.match(jobs, /interrupted/);
	assert.match(extensions, /"\$php_binary" -m/);
	assert.match(installer, /grep -Fxq/);
	assert.match(installer, /wait "\$BACK_PID"/);
	assert.match(remover, /v-list-default-php/);
	assert.match(remover, /assigned to one or more web domains/);
	assert.match(remover, /wait "\$BACK_PID"/);
	assert.match(navigation, /data-orbix-url="\/list\/runtime-profiles\/"/);
	assert.match(compiledTheme, /orbix-runtime-manager/);
});

test('Server Fleet uses pinned TLS, least-privilege keys, secret-safe transport, and serialized refreshes', () => {
	const route = read('web/list/fleet/index.php');
	const page = read('web/templates/pages/list_fleet.php');
	const fleet = read('func/fleet.sh');
	const addNode = read('bin/v-add-fleet-node');
	const listNodes = read('bin/v-list-fleet-nodes');
	const refresh = read('bin/v-start-fleet-refresh');
	const permission = read('install/common/api/fleet-read');
	const navigation = read('web/templates/includes/panel.php');
	const compiledTheme = read('web/css/themes/default.min.css');

	assert.match(route, /\$_SESSION\["userContext"\] !== "admin"/);
	assert.match(route, /!empty\(\$_SESSION\["look"\]\)/);
	assert.match(route, /verify_csrf\(\$_POST\)/);
	assert.match(route, /tmpfile\(\)/);
	assert.match(route, /fclose\(\$secret_handle\)/);
	assert.match(page, /Monitor trusted OrbixPanel nodes/);
	assert.match(page, /SHA256 public-key pin/);
	assert.match(page, /name="confirm_delete" value="yes" required/);
	assert.doesNotMatch(page, /onsubmit=/);
	assert.match(fleet, /--pinnedpubkey "\$TLS_PIN"/);
	assert.match(fleet, /--proto '=https'/);
	assert.match(fleet, /--data-binary @-/);
	assert.match(fleet, /v-list-sys-health-summary/);
	assert.match(addNode, /realpath -e/);
	assert.match(addNode, /"\$secret_realpath"/);
	assert.match(addNode, /chmod 600 "\$temporary_config"/);
	assert.doesNotMatch(listNodes, /SECRET_KEY:/);
	assert.match(refresh, /flock -x 8/);
	assert.match(refresh, /v-refresh-fleet-node/);
	assert.match(permission, /ROLE='admin'/);
	assert.match(permission, /v-list-sys-info,v-list-sys-health-summary,v-list-sys-services/);
	assert.match(navigation, /data-orbix-url="\/list\/fleet\/"/);
	assert.match(compiledTheme, /orbix-fleet-manager/);
});

test('the standalone search bundle is delivered and guarded against duplicate initialization', () => {
	const source = read('web/js/src/globalToolSearch.js');
	const bundle = read('web/js/orbixpanel-overlay.min.js');
	const scripts = read('web/templates/includes/js.php');

	assert.match(source, /orbixSearchReady/);
	assert.match(bundle, /orbixSearchReady/);
	assert.match(scripts, /\/js\/orbixpanel-overlay\.min\.js/);
});
