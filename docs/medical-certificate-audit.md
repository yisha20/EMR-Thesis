# Medical Certificate Root-Cause Audit

Audit baseline: Git commit `3cca817`.

## Local PHP environment

- CLI executable: `C:\xampp\php\php.exe`
- CLI version: PHP 8.0.30
- CLI configuration: `C:\xampp\php\php.ini`
- Apache module: `C:/xampp/php/php8apache2_4.dll`
- Apache `PHPINIDir`: `C:/xampp/php`
- GD setting: `;extension=gd` (disabled)
- GD loaded in CLI: no
- Imagick loaded in CLI: no
- Required DOM, XML, mbstring, and fileinfo extensions: loaded
- Temporary directory: `C:\Users\USER\AppData\Local\Temp`

Apache and CLI point to the same XAMPP PHP directory. Apache must be restarted after changing `php.ini`; CLI commands started afterward read the change immediately.

## PDF implementation

- Laravel wrapper: `barryvdh/laravel-dompdf` 2.0.1
- PDF engine: `dompdf/dompdf` 2.0.8
- Logo: `public/img/msu-iit-logo.png`, readable, 22,956 bytes
- Existing PDF view: `resources/views/certificates/document.blade.php`
- Existing controller uses `PDF::loadView(...)` and a local `public_path()` image.

Dompdf lists GD as optional generally, but PNG processing paths require an image-processing extension. The local logo is a PNG and GD is disabled, which accounts for the reported PDF error.

## Print root cause

Legacy compiled CSS in `public/css/app.css` contains a global print rule that applies `visibility: hidden` to `body *` and restores only `#printSection`. The medical certificate preview does not use `#printSection`, so calling `window.print()` on the application page hides the certificate and produces a blank or nearly blank preview.

The correction is a dedicated authorized print route with a standalone document, avoiding application navigation, legacy print CSS, and hidden dashboard ancestors.

## Data and authorization

The existing `medical_certificates` table already stores patient and doctor snapshots, clinical fields, status, issue time, cancellation fields, and a replacement reference. No destructive migration or duplicate clinical fields are needed for the layout/PDF/print correction.

Current server-side policy allows the issuing Doctor to create/edit/issue; clinical staff may view issued certificates; and a patient account may view its own issued certificate. These rules require explicit regression tests for PDF and print routes.

## Platform warning

`composer check-platform-reqs` reports that the installed PHP 8.0.30 does not satisfy `askedio/laravel-soft-cascade`'s declared PHP `<8.0` constraint. This is pre-existing platform debt and is separate from GD, but must be resolved or formally accepted before server deployment.

## Rollback

This audit adds documentation only. Revert its commit to remove the report; no runtime behavior or data changes are involved.
