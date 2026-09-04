# cPanel account migrations

OrbixPanel administrators can import a full cPanel account backup from **Server Manager → Migration Center**. The workflow runs independently from the web request, records its state, and exposes the latest migration log in the panel.

## Prepare an archive

1. Generate a full account backup on the source cPanel server.
2. Copy the resulting `.tar.gz` or `.tgz` file directly into `/backup` on the OrbixPanel server.
3. Keep the original archive until the imported account has been verified.

Archive filenames may contain letters, numbers, periods, underscores, and hyphens. Symlinked archives and files outside `/backup` are not offered to the panel.

## Start the migration

Open **Migration Center**, select the archive, choose whether to retain the source MX records, and select **Run Preflight and Start**.

The panel returns a job immediately. The locked background worker then verifies that:

- the selected file is still an available server-side archive;
- the gzip and tar structures can be read;
- archive paths are not absolute and do not traverse parent directories;
- the cPanel account metadata and domain configuration are present;
- another migration is not already using the same archive.

The importer creates the account name stored in the cPanel backup. It stops if that account already exists.

## Track and verify the result

Migration Center shows queued, running, completed, failed, or interrupted jobs. Open any job to inspect its latest log output.

After a completed import, verify:

- account package and quota assignment;
- website document roots and PHP versions;
- databases and application credentials;
- DNS zones, nameservers, and MX routing;
- mailboxes, forwarders, and message data;
- SSL certificates and HTTPS behavior;
- scheduled tasks and their PHP executable paths.

The cPanel backup format changes over time. Certificate or DKIM data may require manual repair after a migration; the job log calls out detected failures rather than treating the transfer as fully verified.

## Command-line operations

The same guarded workflow is available to server operators:

```bash
v-list-cpanel-backups json
v-start-cpanel-import cpmove-example.tar.gz no
v-list-cpanel-imports json
v-get-cpanel-import-log 20260904123000-1234-5678
```

Use the returned job identifier when reading the migration log.
