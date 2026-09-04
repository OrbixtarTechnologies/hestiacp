import { defineConfig } from 'vitepress';
import { version } from '../../package.json';

export default defineConfig({
	lang: 'en-US',
	title: 'OrbixPanel',
	description: "Orbixtar's open-source hosting and server management control panel.",

	lastUpdated: true,
	cleanUrls: false,

	head: [
		['link', { rel: 'icon', sizes: 'any', href: '/favicon.ico' }],
		['link', { rel: 'icon', type: 'image/svg+xml', sizes: '16x16', href: '/logo.svg' }],
		['link', { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' }],
		['link', { rel: 'manifest', href: '/site.webmanifest' }],
		['meta', { name: 'theme-color', content: '#1769e0' }],
	],

	themeConfig: {
		logo: '/logo.svg',

		nav: nav(),

		socialLinks: [
			{ icon: 'github', link: 'https://github.com/OrbixtarTechnologies/orbixtar-panel' },
		],

		sidebar: { '/docs/': sidebarDocs() },

		outline: [2, 3],

		editLink: {
			pattern: 'https://github.com/OrbixtarTechnologies/orbixtar-panel/edit/main/docs/:path',
			text: 'Edit this page on GitHub',
		},

		footer: {
			message: 'Released under the GPLv3 License.',
			copyright: 'Copyright © 2026 Orbixtar Technologies and contributors',
		},

		search: {
			provider: 'local',
		},
	},
});

/** @returns {import("vitepress").DefaultTheme.NavItem[]} */
function nav() {
	return [
		{ text: 'Features', link: '/features' },
		{ text: 'Install', link: '/install' },
		{ text: 'Documentation', link: '/docs/introduction/getting-started', activeMatch: '/docs/' },
		{ text: 'Support', link: 'https://github.com/OrbixtarTechnologies/orbixtar-panel/issues' },
		{
			text: `v${version}`,
			items: [
				{
					text: 'Changelog',
					link: 'https://github.com/OrbixtarTechnologies/orbixtar-panel/blob/main/CHANGELOG.md',
				},
				{
					text: 'Contributing',
					link: 'https://github.com/OrbixtarTechnologies/orbixtar-panel/blob/main/CONTRIBUTING.md',
				},
				{
					text: 'Security policy',
					link: 'https://github.com/OrbixtarTechnologies/orbixtar-panel/blob/main/SECURITY.md',
				},
			],
		},
	];
}
/** @returns {import("vitepress").DefaultTheme.SidebarItem[]} */
function sidebarDocs() {
	return [
		{
			text: 'Introduction',
			collapsed: false,
			items: [
				{ text: 'Getting started', link: '/docs/introduction/getting-started' },
				{ text: 'Best practices', link: '/docs/introduction/best-practices' },
			],
		},
		{
			text: 'User guide',
			collapsed: false,
			items: [
				{ text: 'Account', link: '/docs/user-guide/account' },
				{ text: 'Backups', link: '/docs/user-guide/backups' },
				{ text: 'Cron jobs', link: '/docs/user-guide/cron-jobs' },
				{ text: 'Databases', link: '/docs/user-guide/databases' },
				{ text: 'DNS', link: '/docs/user-guide/dns' },
				{ text: 'File manager', link: '/docs/user-guide/file-manager' },
				{ text: 'Mail domains', link: '/docs/user-guide/mail-domains' },
				{ text: 'Notifications', link: '/docs/user-guide/notifications' },
				{ text: 'Packages', link: '/docs/user-guide/packages' },
				{ text: 'Reseller accounts', link: '/docs/user-guide/resellers' },
				{ text: 'Statistics', link: '/docs/user-guide/statistics' },
				{ text: 'Users', link: '/docs/user-guide/users' },
				{ text: 'Web domains', link: '/docs/user-guide/web-domains' },
			],
		},
		{
			text: 'Server administration',
			collapsed: false,
			items: [
				{ text: 'Backup & restore', link: '/docs/server-administration/backup-restore' },
				{ text: 'cPanel migrations', link: '/docs/server-administration/cpanel-migrations' },
				{ text: 'Configuration', link: '/docs/server-administration/configuration' },
				{ text: 'Customisation', link: '/docs/server-administration/customisation' },
				{ text: 'Databases & phpMyAdmin', link: '/docs/server-administration/databases' },
				{ text: 'DNS clusters & DNSSEC', link: '/docs/server-administration/dns' },
				{ text: 'Email', link: '/docs/server-administration/email' },
				{ text: 'File manager', link: '/docs/server-administration/file-manager' },
				{ text: 'Firewall', link: '/docs/server-administration/firewall' },
				{ text: 'Mail queue', link: '/docs/server-administration/mail-queue' },
				{ text: 'OS upgrades', link: '/docs/server-administration/os-upgrades' },
				{ text: 'Server health', link: '/docs/server-administration/server-health' },
				{ text: 'Rest API', link: '/docs/server-administration/rest-api' },
				{ text: 'Runtime profiles', link: '/docs/server-administration/runtime-profiles' },
				{ text: 'Server fleet', link: '/docs/server-administration/server-fleet' },
				{ text: 'SSL certificates', link: '/docs/server-administration/ssl-certificates' },
				{ text: 'Web templates & caching', link: '/docs/server-administration/web-templates' },
				{ text: 'Troubleshooting', link: '/docs/server-administration/troubleshooting' },
			],
		},
		{
			text: 'Contributing',
			collapsed: false,
			items: [
				{ text: 'Building Packages', link: '/docs/contributing/building' },
				{ text: 'Development', link: '/docs/contributing/development' },
				{ text: 'Documentation', link: '/docs/contributing/documentation' },
				{ text: 'Quick install app', link: '/docs/contributing/quick-install-app' },
				{ text: 'Testing', link: '/docs/contributing/testing' },
				{ text: 'Translations', link: '/docs/contributing/translations' },
			],
		},
		{
			text: 'Reference',
			collapsed: false,
			items: [
				{ text: 'API', link: '/docs/reference/api' },
				{ text: 'CLI', link: '/docs/reference/cli' },
				{ text: 'cPanel & WHM parity', link: '/docs/reference/cpanel-whm-parity' },
			],
		},
	];
}
