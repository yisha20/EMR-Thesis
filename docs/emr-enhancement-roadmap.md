# EMR Enhancement Roadmap

This roadmap improves the clinic EMR without destabilizing the working pilot. Ship one small release at a time. Every release must have an owner, acceptance criteria, a database backup when data may change, automated tests, manual role testing, and a rollback procedure.

## Release rules

1. Keep production and development `.env` values separate.
2. Never use `migrate:fresh`, `db:wipe`, destructive seeders, or unreviewed data rewrites on clinic data.
3. Test migrations against a recent sanitized database copy before production.
4. Run the full PHPUnit suite before and after each change.
5. Manually test Administrator, Doctor, Nurse, Staff, Student, Faculty/Employee, and Dependent permissions when relevant.
6. Deploy outside clinic hours and verify backups before changing schema or stored records.
7. Release security and correctness fixes separately from visual or feature changes.
8. Record the deployed commit, migration state, operator, date, verification result, and rollback decision.

## Phase 0: Pilot protection

- Configure exact ICTC-approved campus CIDR ranges and verify access from WiFi and mobile data.
- Configure HTTPS and secure session cookies.
- Verify private attachment storage and authorized downloads.
- Automate encrypted database and attachment backups; complete a restore drill.
- Test all roles against a written permission matrix.
- Complete a fictional end-to-end patient journey on actual clinic devices.
- Add server availability, disk capacity, database, backup, and application-error monitoring.
- Establish downtime, incident, and privacy-response procedures.

Acceptance: no cross-patient access, no role bypass, successful restore, correct WiFi restriction, valid HTTPS, green tests, and clinic-lead sign-off.

## Phase 1: Data and workflow safety

- Add duplicate-patient detection and a controlled merge workflow.
- Add draft, signed, finalized, amended, and voided clinical-record states.
- Preserve original signed content and require reasons for amendments or voids.
- Add allergy and adverse-reaction visibility at consultation and prescribing points.
- Add autosaved drafts and idempotent protection for critical submissions.
- Add queue reconciliation, stale-state handling, and an end-of-day unresolved queue report.
- Expand audit coverage for record views, exports, prints, permission denials, role changes, and sensitive corrections.

Acceptance: no silent clinical overwrites, retrying cannot duplicate a record, merges preserve history, and concurrent queue tests pass.

## Phase 2: Usability and accessibility

- Complete phone, tablet, laptop, and desktop QA for every role.
- Standardize responsive tables, modal scrolling, form validation, loading states, and empty states.
- Meet WCAG 2.2 AA goals for keyboard use, focus, labels, contrast, zoom, and error announcements.
- Add safe unsaved-change warnings and clear connection-recovery messages.
- Validate prescription and clinical-summary printing on clinic printers.

Acceptance: critical workflows work at 320, 360, 390, 430, 768, 1024, and desktop widths without page-level horizontal overflow or blocked actions.

## Phase 3: Clinical completeness

- Add medication reconciliation and active/inactive medication status.
- Add clinician-approved allergy, duplicate-medication, and abnormal-vital warnings.
- Add structured diagnoses, referrals, follow-up instructions, and vital-sign trends.
- Standardize clinic-approved terminology, units, ranges, and prescription templates.
- Clearly distinguish patient-reported information from clinician-verified information.

Acceptance: clinic leadership approves every clinical field, alert, range, and workflow; automated alerts are advisory and traceable.

## Phase 4: Operations and reporting

- Add an administrator health dashboard without patient information.
- Add volume, waiting-time, missed-queue, follow-up, registration, and system-reliability reports.
- Apply minimum-necessary access and de-identification to reports.
- Add controlled exports with audit events and approved retention rules.

Acceptance: report totals reconcile with source records, exports are authorized and audited, and operational alerts contain no clinical data.

## Phase 5: Platform modernization

- Increase characterization and browser test coverage before framework changes.
- Upgrade PHP, Laravel, JavaScript, and CSS dependencies through supported intermediate versions.
- Replace repeated inline code with tested reusable components.
- Add automated build, dependency scanning, static analysis, test, and deployment checks.
- Introduce versioned APIs and HL7 FHIR mappings only after authorization and audit controls are mature.

Acceptance: staging upgrade rehearsals preserve data and behavior, dependency checks pass, performance does not regress, and rollback is proven.

## Pilot change log template

- Change identifier and owner:
- Problem and affected roles:
- Clinical/privacy risk:
- Files and migrations involved:
- Backup required and verified:
- Automated tests:
- Manual acceptance scenarios:
- Deployment window:
- Monitoring plan:
- Rollback steps:
- Clinic approver:
- Result and follow-up:
