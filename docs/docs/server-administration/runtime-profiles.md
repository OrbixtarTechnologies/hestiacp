# Runtime profiles

Runtime Profiles is OrbixPanel's component build workflow for servers using the PHP-FPM web backend. It turns the existing MultiPHP package commands into one validated, tracked profile operation.

Open **Server Manager → Runtime Profiles** to see:

- every PHP release supported by the installed OrbixPanel release;
- which releases are installed and which one is the server default;
- direct website assignments that protect a runtime from removal;
- the loaded extension inventory for each installed command-line runtime;
- recent background profile jobs and their current step.

## Apply a profile

1. Select the exact PHP versions the server should retain.
2. Choose one selected version as the server default.
3. Review the install, default-switch, and removal counts.
4. Confirm the plan and choose **Build and Apply Profile**.

OrbixPanel serializes profile jobs across the server. A job installs missing releases first, changes the default only after those installs succeed, and removes unselected releases last. Package and service output is recorded in the job log.

The profile is convergent rather than transactional: a package-manager failure stops the job at that step and preserves completed safe changes. Correct the package or repository problem, then submit the same profile again to continue toward the requested state.

## Removal safeguards

The removal command independently checks safety even when it is called outside the interface. It refuses to remove:

- the current server-default PHP release;
- a release assigned directly to any hosted web domain, including custom backend templates ending in the matching `PHP-X_Y` identifier;
- a release that is not currently installed.

When a profile changes the default, the default switch and domain rebuild complete before the previous default becomes eligible for removal. Directly assigned runtimes remain locked until those websites move to another backend template.

## Command-line use

Apply a profile:

```bash
v-start-runtime-profile 8.3,8.4,8.5 8.4
```

List recent jobs:

```bash
v-list-runtime-profile-jobs
```

Inspect extensions for an installed release:

```bash
v-list-sys-php-extensions 8.4
```
