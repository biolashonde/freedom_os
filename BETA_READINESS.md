# Beta Readiness Checklist

## Ready Enough For Local Beta

- Auth, dashboard, check-ins, streaks
- SOS and offline fallback
- Accountability invite/accept flow
- Partner dashboard
- Weekly digest generation
- Proactive missed-check-in and high-risk nudges
- Devotional archive
- AI devotional generation with multiple providers
- Purpose map
- Privacy export and account anonymization
- Security headers and login throttling
- FreedomGuard browser extension package and server-side blocker APIs
- Superadmin analytics and admin/mentor console

## Before Real Users

- Configure SMTP or PHPMailer transport.
- Put the app behind HTTPS.
- Set `APP_DEBUG=false`.
- Replace `APP_KEY`.
- Create or rotate a real superadmin account through the installer before shared beta.
- Confirm `/install` is locked.
- Point production web root at `public/`.
- Confirm backups for MySQL and `storage/`.
- Review copy for pastoral/clinical safety boundaries.
- Add a clear disclaimer that this is a support tool, not emergency medical care.

## Still Outside MVP

- Native mobile packaging.
- Official browser-store publication for FreedomGuard.
- OS-level blocker sidecar daemon.
- Production SMTP provider integration.
