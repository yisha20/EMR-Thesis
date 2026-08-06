# System Monitoring Operations

The Admin-only System Monitoring module is local-first. It uses Laravel routes and storage paths, so no localhost port or future university server address is embedded in the code.

## Local use

Open `/admin/system-monitoring` while signed in as an Administrator, or run:

```text
php artisan clinic:monitor --full
php artisan clinic:monitor --critical
php artisan clinic:monitor --full --json
php artisan clinic:monitor --full --no-write
php artisan clinic:monitor --daily-report
```

`--no-write` performs checks without creating incidents or report files. Monitoring checks never update clinical records.

Daily JSON reports are private and are stored under:

```text
storage/app/private/monitoring/daily-monitoring-YYYY-MM-DD.json
```

For local scheduler testing, use `php artisan schedule:run` as needed. Laravel 6 does not provide the newer `schedule:work` command.

## Future university server

Set the normal production environment values during deployment; the monitoring code does not need URL changes. Configure this cron entry with the real deployment path:

```text
* * * * * cd /path/to/EMR-main && php artisan schedule:run >> /dev/null 2>&1
```

The schedule runs critical checks every 15 minutes, full checks hourly, and creates the daily report at 5:05 PM. Confirm that the server user can write `storage` and `bootstrap/cache`, and separately verify that the existing 5:00 PM backup task succeeds.

## Privacy

Incident records contain numeric resource references and sanitized technical summaries only. Do not enter patient names, symptoms, diagnoses, SOAP content, prescriptions, passwords, or reset tokens in resolution notes or problem reports.

## Rollback

Each implementation phase is isolated in Git. Revert commits from newest to oldest with `git revert <commit>`. If the data-model commit is reverted, first retain any monitoring reports or incident metadata required by clinic policy, then run `php artisan migrate:rollback --step=1`; this removes only `workflow_action_logs` and `system_incidents`.
