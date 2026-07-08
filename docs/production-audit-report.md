# MSU-IIT Clinic EMR Production Audit Report

Audit date: 2026-07-08

Scope covered in this pass: Laravel routes, authentication, authorization, controllers, high-risk Blade output, migrations, configuration, deployment cache behavior, prescription access, student ownership checks, and selected UI/mobile/print areas already changed in this branch.

## Executive Summary

Deployment readiness score: 78/100.

The system is materially safer after this pass, especially around password reset, clinical record mutation, student prescription permissions, route caching, and production environment defaults. It is not yet at a final hospital-grade release bar because the PHPUnit suite is failing and a full browser/device QA pass was not possible from this terminal-only session.

## Critical Issues Fixed

- Password reset could previously be triggered by visiting `/forgot-password/verify/{email}` with only an email address. This is now disabled, and reset email sending uses Laravel's tokenized password broker.
- The route name `password.update` was reused for forced password changes, conflicting with Laravel's reset-password route. Forced password changes now use `password.change.update`.
- Medical record store/update accepted broad request data. The controller now uses explicit validation and a controlled field whitelist.
- Medical record edit/update/delete routes are now limited to Administrator/Doctor where appropriate.
- Student export restrictions for prescriptions remain enforced server-side; students can view only their own prescriptions and cannot print/download/export.
- The unprotected `student-complaints/{complaint}/link-record` mutation route is now limited to Administrator/Nurse/Staff.
- Patient archive/restore/permanent-delete routes are now Administrator-only.
- Service create/edit/archive/delete/restore routes are now Administrator-only.
- Archived patient/service search query grouping was fixed so archive-only searches cannot leak active rows through ungrouped `orWhere` clauses.
- Hardcoded Gmail sender addresses were removed from legacy mail classes; mail now uses Laravel mail configuration.
- `remember_token` is hidden from serialized user output.

## High Issues Fixed

- Admin-created/updated users now require unique email/username and minimum password length.
- Internal forced password change now invalidates the session and regenerates the CSRF token after logout.
- Password reset completion now redirects students to the student dashboard and staff/admin users to the staff dashboard.
- A production-safe `password_resets` migration was added for Laravel's reset broker.
- Route closures blocking `route:cache` were removed in earlier deployment work; route caching now succeeds.
- Friendly 403/404/500 pages and optional campus network restriction were added in the prior deployment pass.

## Medium Issues Fixed

- Medical-record file uploads now store relative `Storage::url()` paths instead of host-derived absolute URLs.
- Medical-record attachments validate type and size.
- Medical-record vital signs now have basic numeric bounds.
- Service update no longer accepts `added_by` from the request.
- `.env.example` now uses production-safe placeholders and includes mail-from and campus-network settings.

## Remaining Issues / Recommendations

- PHPUnit is not green. The current run produced 15 failures. Most failures are test-suite setup issues returning 419 CSRF responses, but one test expects non-doctor access to medical-record edit and now conflicts with stricter role controls.
- The test suite should be updated to use Laravel testing CSRF conventions or disabled middleware where appropriate, then rerun until green.
- Browser/device QA still needs to be performed at 320, 360, 390, 412, 430, 768, 1024, 1280, 1440, and 1920 px.
- Full database migration testing should be run against a copy of production-like data before ICTC deployment.
- File upload storage should be reviewed on the server so private medical attachments are not directly browsable unless intentionally public.
- Existing contact/about pages still contain personal/static email addresses. Confirm whether those are official before production.
- Laravel 6 and PHP 7.4 platform constraints are old. ICTC should confirm supported PHP/security patch policy before deployment.
- Some legacy Blade pages still use inline scripts and repeated table/search code. This is maintainability debt, not an immediate blocker.
- A formal policy layer would be preferable long term. Current authorization is middleware/controller based.

## Files Modified In This Audit Continuation

- `.env.example`
- `app/Http/Controllers/Auth/PasswordChangeController.php`
- `app/Http/Controllers/Auth/ResetPasswordController.php`
- `app/Http/Controllers/ForgotPasswordController.php`
- `app/Http/Controllers/MedicalRecordController.php`
- `app/Http/Controllers/PatientController.php`
- `app/Http/Controllers/ServiceController.php`
- `app/Http/Controllers/UserController.php`
- `app/Mail/ForgotPasswordMail.php`
- `app/Mail/GeneratedPassword.php`
- `app/User.php`
- `database/migrations/2026_07_04_000001_create_password_resets_table.php`
- `resources/views/auth/change_password.blade.php`
- `resources/views/auth/passwords/email.blade.php`
- `resources/views/auth/passwords/reset.blade.php`
- `routes/web.php`

## Database Changes

- Added `password_resets` table migration for secure, tokenized password reset.
- No destructive schema changes were made in this pass.

## Verification Results

Passed:

- `php -l app\Http\Controllers\MedicalRecordController.php`
- `php -l app\Http\Controllers\ForgotPasswordController.php`
- `php -l app\Http\Controllers\Auth\PasswordChangeController.php`
- `php -l app\Http\Controllers\Auth\ResetPasswordController.php`
- `php artisan route:cache`
- `php artisan view:cache`
- `php artisan config:cache`
- Scan for old mail password, old Gmail sender, debug default, local Windows project path, localhost port URLs, and broad `request()->all()`

Failed / Needs Follow-Up:

- `php artisan test` is unavailable because this is Laravel 6.
- `vendor\bin\phpunit.bat` ran but failed: 27 tests, 15 failures. Major category: 419 CSRF responses in feature tests. One access-control expectation must be updated after stricter Doctor/Admin medical-record edit rules.

## Deployment Notes

- Before deploying, run `php artisan migrate --force` on ICTC after database backup.
- After `.env` is finalized, run:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

- For continued local development after cache checks, run:

```bash
php artisan optimize:clear
```

## Final Recommendation

Do not mark the system as final production-ready until the PHPUnit suite is corrected and a manual browser QA pass is completed. From a security and deployment-readiness standpoint, the highest-risk flaws found in this pass have been fixed.
