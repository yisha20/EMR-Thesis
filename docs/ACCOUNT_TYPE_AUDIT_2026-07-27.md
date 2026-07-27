# Patient account-type audit — 27 July 2026

Pre-repair report (read-only query):

- Student patient accounts: 7
- Faculty patient accounts: 1
- Dependent patient accounts: 0
- Faculty accounts using the legacy Student portal authorization role: 1
- Null/ambiguous patient classifications: 0

Root cause: the application historically uses the `Student` authorization role as its
general patient-portal role. Admin Manage Users rendered `users.role` as though it were
the patient classification, even though `patient_accounts.patient_type` correctly stored
`faculty`. No record required classification backfill. The UI now presents the legacy
portal role as System Role `Patient` and displays Account Type separately.

No staff role was changed and no account was automatically reclassified.
