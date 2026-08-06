# MSU-IIT Clinic EMR Deployment Readiness Checklist

Use this before handing the app to ICTC or moving it to the production server.

## Production environment

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set `APP_URL` to the final ICTC-hosted URL.
- Generate and keep one production `APP_KEY`; do not regenerate it after real data is encrypted.
- Fill production database values in `.env`; do not use local `root` credentials.
- Set mail credentials owned by the clinic or university, not a personal account.
- Keep `.env` outside version control.

## Campus network restriction

The app includes an optional web middleware:

```env
CAMPUS_NETWORK_RESTRICTION=true
CAMPUS_ALLOWED_IPS=10.24.0.0/16
```

The address above is an example only. Leave `CAMPUS_NETWORK_RESTRICTION=false` for local development. Before enabling it in production, ICTC must replace `CAMPUS_ALLOWED_IPS` with the exact approved campus, WiFi, VPN, or intranet ranges. An enabled restriction with an empty allow-list intentionally denies every request.

## Laravel production commands

Run these after dependencies are installed and `.env` is finalized:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run this after code or config changes during maintenance:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Role testing

- Student: register/login, submit a concern, view own concern timeline, view prescriptions without print or download controls, update profile.
- Staff: receive complaints, link/create patient record, forward consultation, view medical records.
- Nurse: review queue, call student, update complaint flow, view records.
- Doctor: view medical records, open prescription modal, create prescription, print prescription, view and edit student profile/history.
- Administrator: manage users, roles, patients, services, archives, and activity logs.

## Responsive testing

- Staff, nurse, doctor, and administrator screens: test desktop, laptop, and tablet widths.
- Student screens: test desktop, tablet, and mobile widths.
- Confirm tables stay inside scroll wrappers and do not create full-page horizontal scrolling.
- Confirm modal forms fit mobile screens and remain scrollable.
- Confirm sidebar/topbar remain usable at tablet and mobile widths.

## Database safety

- Back up production database before every migration.
- Test migrations on a copy of production data before the ICTC deployment window.
- Avoid `migrate:fresh`, `db:wipe`, or seed commands on production.
- Verify file upload storage permissions and backup uploaded medical attachments.

## Security and stability

- Ensure HTTPS is enabled.
- Keep `APP_DEBUG=false`.
- Restrict server file permissions so `.env` and uploaded records are not publicly browsable.
- Confirm CSRF protection remains enabled.
- Review role access after any route changes.
- Monitor Laravel logs after deployment.
