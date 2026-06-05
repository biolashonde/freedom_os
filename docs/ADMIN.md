# Admin Console

The admin console is available at:

```text
http://localhost/freedom_os/admin
```

Only users with `role = admin` or `role = mentor` can access it.
Users with `role = superadmin` can also access the admin console plus superadmin-only analytics, donation settings, and infrastructure settings.

## Promote A Local User

Replace the email address:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "USE freedomos; UPDATE users SET role='admin' WHERE email='you@example.com';"
```

Then log out and log back in.

For superadmin access, use:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "USE freedomos; UPDATE users SET role='superadmin' WHERE email='you@example.com';"
```

## Current Capabilities

- Runtime stats
- Recent users
- Public testimony visibility review
- Pending FreedomGuard override review
- Recent blocker logs
- Superadmin analytics at `/admin/analytics`
- Superadmin donation settings at `/admin/donations`
- Superadmin app settings at `/admin/settings` for app URL and mail settings
- Superadmin Content Studio at `/admin/content` for custom devotionals, SOS resources, videos, games, and online meetings
- User AI keys are managed by each logged-in user at `/settings/ai`
