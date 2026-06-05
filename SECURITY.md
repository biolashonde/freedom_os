# Security Policy

FreedomOS handles sensitive recovery data. Please help keep reports careful, private, and actionable.

## Reporting

Do not publish vulnerabilities, leaked data, credentials, or exploit details in public issues.

Use a private GitHub security advisory if available on the repository, or contact the maintainer through the repository contact channels and request a private disclosure path.

## What To Report

- Authentication or authorization bypasses
- CSRF, XSS, SQL injection, SSRF, or unsafe file access
- Exposure of user recovery data
- Unsafe donation, installer, or superadmin workflows
- FreedomGuard token leakage or bypass problems
- Insecure defaults that could affect deployed instances

## Secret Hygiene

The public repository must not contain:

- `.env`
- API keys
- SMTP passwords
- database credentials
- real donation bank details
- user data
- logs
- outbox messages
- cache files
- install locks
- generated release archives

If you accidentally commit a secret, rotate it immediately and remove it from git history before pushing.

## Maintainer Notes

- Keep `APP_DEBUG=false` in production.
- Use a strong random `APP_KEY`.
- Serve the app over HTTPS.
- Point production web root to `public/`.
- Keep `storage/` writable but not directly web-accessible.
- Run dependency and server updates regularly.
