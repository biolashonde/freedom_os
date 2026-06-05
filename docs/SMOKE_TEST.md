# FreedomOS Smoke Test

Use this after setup or before a beta handoff.

## Public Routes

- `http://localhost/freedom_os/login` returns 200.
- `http://localhost/freedom_os/register` returns 200.
- `http://localhost/freedom_os/manifest.json` returns 200.
- `http://localhost/freedom_os/sw.js` returns 200.
- `http://localhost/freedom_os/offline.html` returns 200.

## Core User Flow

1. Register a new user.
2. Confirm redirect to `/dashboard`.
3. Submit a daily check-in.
4. Confirm current streak updates.
5. Open `/sos`.
6. Trigger SOS.
7. Resolve SOS.

## Accountability Flow

1. Open `/accountability`.
2. Invite a partner with SOS alerts and weekly digest enabled.
3. Copy the generated invite link.
4. Open the invite link in a separate session.
5. Accept the invite and set partner password.
6. Open `/partner`.
7. Confirm the supported user appears.
8. Queue encouragement.

## Purpose And Devotional

1. Open `/purpose`.
2. Save safety plan.
3. Add a purpose goal.
4. Mark the goal complete.
5. Save testimony draft.
6. Open `/devotional`.
7. Open `/devotional/archive`.

## Privacy

1. Open `/privacy`.
2. Export data and confirm JSON downloads.
3. For a throwaway account only, anonymize account with password plus `DELETE`.
4. Confirm original email cannot log in.

## Cron

Run:

```powershell
C:\xampp\php\php.exe cron\weekly_digest.php --force
C:\xampp\php\php.exe cron\weekly_digest.php
```

The first command should queue digests for active pairs. The second should queue `0` for the same week because of duplicate protection.
