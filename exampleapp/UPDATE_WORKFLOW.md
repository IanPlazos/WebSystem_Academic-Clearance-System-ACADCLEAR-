# Update Workflow (Two Laptops)

This project now uses a tracked `VERSION` file for app version.
That means the version is synced through Git (same value on all laptops after pull).

## Do I need to deploy first?
Yes.

For another laptop to be notified about a newer version, you need to:
1. Push your code to GitHub.
2. Push a newer Git tag (example: `v1.0.3`).

The app checks your GitHub repo tags/releases and compares them to local `VERSION`.

## Laptop A (Developer) - Release v1.0.3
From project root (`exampleapp`):

```powershell
cd .\scripts
.\release-version.ps1 -Version v1.0.3 -Branch master
```

What this does:
1. Verifies clean working tree.
2. Updates `VERSION`.
3. Commits release version.
4. Creates tag.
5. Pushes commit + tag to GitHub.

## Laptop B (Older Version) - See Notification + Update
1. Login as `school_admin`.
2. Open **Update** page in the app (`/admin/update`).
3. If GitHub has newer tag (like `v1.0.3`), app shows **New version available**.
4. Click **Install New Version**. The button runs the same updater script below: it pulls the latest code, installs dependencies, builds assets, runs migrations, and clears Laravel caches.

If you need to run the updater manually instead, use:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\apply-latest-update.ps1 -Branch master
```

5. Refresh app and confirm footer/version matches latest `VERSION`.

## Notes
- The updater refuses to run if `exampleapp` has uncommitted changes, because pulling over local edits can overwrite work.
- The Update page shows the exact changed files and disables install until those local changes are committed or stashed.
- Use `apply-latest-update.ps1` directly if you want to see the command output in a terminal.
- Keep branch name consistent (`master` in current repo).
