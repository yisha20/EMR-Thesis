# Medical Certificate Server Requirements

Always inspect the installed PHP version before selecting a package:

```bash
php -v
php --ini
php -m | grep -Ei "gd|mbstring|dom|xml|fileinfo"
composer check-platform-reqs
php artisan clinic:health-check
```

## Windows/XAMPP

The audited local installation uses `C:\xampp\php\php.ini`. Change:

```ini
;extension=gd
```

to:

```ini
extension=gd
```

Restart Apache completely, then verify both CLI and a web request:

```powershell
php -m | findstr /I gd
php -r "var_dump(extension_loaded('gd'));"
php artisan clinic:health-check
```

## Debian/Ubuntu server accessed through Termius

Do not guess the PHP package suffix. Check `php -v`, install the matching GD package (for example `php8.2-gd` when PHP 8.2 is installed), then restart the service actually serving the application:

```bash
sudo apt update
sudo apt install php8.2-gd
sudo systemctl restart apache2
```

For Nginx/PHP-FPM, restart the matching FPM service and Nginx instead:

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

Run `php artisan clinic:health-check` again as the deployment user. Also verify through the web server because CLI and FPM/Apache can load different `php.ini` files.

Production must use `APP_DEBUG=false`. The application returns a safe operational message when certificate PDF generation is unavailable and logs the technical exception privately.

## Rollback

Reverting the health-check/PDF-environment commit removes the Artisan check and graceful PDF fallback. It does not alter certificate records or database schema.
