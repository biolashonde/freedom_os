# Cron Setup

## Local Manual Runs

From `C:\xampp\htdocs\freedom_os`:

```powershell
C:\xampp\php\php.exe cron\weekly_digest.php
C:\xampp\php\php.exe cron\proactive_nudges.php
C:\xampp\php\php.exe cron\process_outbox.php
```

Use `--force` only for testing weekly digest generation:

```powershell
C:\xampp\php\php.exe cron\weekly_digest.php --force
C:\xampp\php\php.exe cron\proactive_nudges.php --force
```

## Linux Production Example

```cron
# Weekly accountability digest on Sunday at 8 AM
0 8 * * 0 php /var/www/freedomos/cron/weekly_digest.php >> /var/log/freedomos-weekly.log 2>&1

# Daily missed-check-in and high-risk support nudges at 7 PM
0 19 * * * php /var/www/freedomos/cron/proactive_nudges.php >> /var/log/freedomos-nudges.log 2>&1

# Process queued notifications every minute
* * * * * php /var/www/freedomos/cron/process_outbox.php >> /var/log/freedomos-outbox.log 2>&1
```

## Windows Task Scheduler

Create two tasks:

- Weekly digest:
  - Program: `C:\xampp\php\php.exe`
  - Arguments: `cron\weekly_digest.php`
  - Start in: `C:\xampp\htdocs\freedom_os`

- Outbox processing:
  - Program: `C:\xampp\php\php.exe`
  - Arguments: `cron\process_outbox.php`
  - Start in: `C:\xampp\htdocs\freedom_os`

- Proactive nudges:
  - Program: `C:\xampp\php\php.exe`
  - Arguments: `cron\proactive_nudges.php`
  - Start in: `C:\xampp\htdocs\freedom_os`

For local dev, manual runs are enough.
