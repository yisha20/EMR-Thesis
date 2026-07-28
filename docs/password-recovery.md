# Password recovery mail configuration

Production reset links use Laravel's configured mail transport. Configure
`MAIL_DRIVER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`,
`MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`.

For safe local development, set `MAIL_DRIVER=log` and inspect the application
log in a non-production environment. Never publish or commit a reset link.
Automated tests use Laravel's fake notification transport and do not deliver
real email.
