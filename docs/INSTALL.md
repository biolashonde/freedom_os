# FreedomOS Installer

Open `/install` on a fresh deployment to run the web installer.

The installer:

- checks PHP extensions and writable folders
- creates the MySQL database
- imports `database/schema.sql`
- applies SQL migration files
- writes `.env`
- creates or promotes the first `superadmin`
- writes `storage/install.lock`

After installation, `/install` is locked. To intentionally rerun setup, add this to `.env`:

```env
APP_INSTALL_UNLOCK=true
```

Remove that value again after maintenance.

Superadmins can open `/admin/analytics` for platform analytics. Regular admins and mentors can still use `/admin`.
