# Patient Portal Expansion Report

## Status

This change establishes the compatibility-safe foundation and core patient workflows. It is not a claim that all 20 requested phases are complete. The remaining work is listed below.

## Implemented

- Added a unified `patient_accounts` classification for student, faculty, and dependent patients without changing administrative roles.
- Backfilled all existing students, linked matching legacy patient records, and grandfathered existing portal access. No student or medical record was deleted.
- Added validated account-type selection at login and student/faculty self-registration.
- Added mandatory, non-bypassable onboarding for newly registered patient accounts.
- Added draft/submission assessment states, revision versions, normalized medical/family/medication/intervention storage, automatic age calculation, and patient/staff field separation in storage.
- Added sponsor-managed dependents, duplicate checks, proof upload support, and pending/verified/rejected/inactive verification states.
- Added configurable database-backed common complaint options and an Administrator management page.
- Made symptom details and attachments optional and stopped accepting patient-selected clinical urgency. Staff triage defaults to `unassigned`.
- Added idempotent counter/consultation ticket generation, priority ordering data, queue status logs, patient-only status JSON, 25-second polling, call/completion notifications, and staff status controls.
- Added an ownership/role-protected A4 health-assessment PDF.
- Added a compact health assessment summary to staff complaint review.
- Removed fixed-height/overflow constraints from the changed portal layout and added responsive assessment/complaint controls.
- Added safe audit events without logging full clinical content.

## Migration and backfill

Migration: `2026_07_25_000001_expand_patient_portal_foundation.php`

New tables:

- `patient_accounts`
- `patient_dependents`
- `health_assessments`
- `health_assessment_medical_histories`
- `health_assessment_family_histories`
- `health_assessment_medications`
- `health_assessment_nursing_interventions`
- `common_complaint_options`
- `complaint_option_selections`
- `clinic_queues`
- `queue_status_logs`

Safe additions were made to `student_complaints` and `notifications`. Existing students are inserted as verified `student` patient accounts. The migration uses `updateOrInsert` for complaint options and has rollback methods. Do not use `migrate:fresh`.

Local verification after migration:

- Existing students: 6
- Backfilled student patient accounts: 6
- Duplicate user-linked patient accounts: 0

## Routes and authorization

Added health assessment draft/submit/PDF routes, dependent management routes, patient queue status, staff queue mutations, dependent verification, and Administrator complaint-option routes. The health-assessment completion middleware applies only to patient portal content routes. Ownership checks protect patient PDF and queue access.

## Automated verification

`PatientPortalExpansionTest`: 4 tests, 23 assertions, passing.

The legacy suite currently has five failures: two expectations intentionally conflict with the new onboarding/triage requirements, and three pre-existing workflow/wording/authorization expectations still require reconciliation. Therefore the strict completion rule is not satisfied.

The asset build could not run because local JavaScript dependencies are absent (`cross-env` is unavailable). The changed responsive styles are loaded directly from `public/css/side.css` and do not require compilation.

## Remaining work before complete sign-off

- Build full staff editors for physical examination, vital signs, repeatable nursing interventions, and physician assessment/recommendations.
- Add independent adult-dependent account invitation/login configuration; current default is sponsor-managed.
- Add a dedicated dependent verification worklist.
- Add transfer semantics that complete/transfer a counter ticket and atomically create the linked consultation ticket.
- Add staff queue board, Call Next priority selection, override-reason workflow, recall behavior, and clinic queue configuration.
- Integrate digital assessments into the legacy permanent health-examination timeline as explicit revisions.
- Complete all requested feature tests, including IDOR, PDF rendering, all roles, notifications, transfer races, and dependent policy.
- Reconcile and pass the complete legacy test suite.
- Install local frontend dependencies, run the production build, and perform browser/PDF/manual responsive testing at every requested width and zoom.

## Rollback

Back up the database, then run:

```text
php artisan migrate:rollback --step=1
```

This removes only the expansion tables/columns. Do not roll back if new patient assessments, dependents, or queue records must be retained; export them first. Existing legacy student, patient, medical-record, consultation, prescription, notification, and activity-log records are not deleted by the forward migration.
