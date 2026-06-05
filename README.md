# FreedomOS

FreedomOS is an open-source recovery operating system for people fighting compulsive sexual habits, relapse cycles, and private battles that need more than willpower. It combines daily recovery rhythms, SOS support, accountability, devotionals, purpose planning, privacy controls, community care, and a browser guard into one calm, practical product.

The product is intentionally faith-aware, privacy-first, and self-hostable. Users bring their own AI keys. Admins control their own donation settings, content, meetings, resources, and outreach.

## Why This Exists

Most recovery tools only help after the person has already fallen or only offer content without real support in the urgent moment. FreedomOS is built around the gap where a decision is still reachable:

- a check-in before the day drifts
- an SOS flow before isolation wins
- an accountability partner before shame goes silent
- a browser guard before the pattern starts
- devotionals, prayers, music, games, meetings, and community resources when the body needs a different direction

The goal is not surveillance. The goal is freedom with dignity.

## Highlights

- Public storytelling homepage and donation workflow
- Guided installer for database setup and first superadmin creation
- Register, login, logout, roles, admin, mentor, and superadmin access
- Daily check-ins, mood and urge tracking, relapse reset, streaks, and progress analytics
- SOS mode with grounding flow, scripture, calming resources, and partner alerts
- 100 seeded devotionals and prayers for recovery
- Superadmin Content Studio for devotionals, music, games, videos, resources, meetings, and community moderation
- Community chatroom plus online meeting links
- Accountability invite and partner dashboard
- Purpose map with safety plan, goals, and testimony draft
- Privacy export and account anonymization
- User-owned AI devotional generation through Anthropic, OpenAI, Gemini, or OpenRouter
- AI settings page where each user stores their own provider keys
- Donation settings for manual transfer details and donation platform links
- Superadmin email workflow with multiple SMTP senders, queue processing, fallback delivery, bulk email, and password recovery
- PWA manifest, service worker, offline SOS fallback, and mobile install support
- FreedomGuard browser extension source with device tokens, rules, logs, and override flow
- Superadmin analytics dashboard
- Security headers, CSRF protection, login throttling, and install lock

## Tech Stack

- Native PHP 8.2+
- MySQL or MariaDB
- Apache rewrite support, or Nginx pointed at `public/`
- No framework required
- Vanilla JavaScript and CSS

## Quick Start

Clone the repository:

```bash
git clone https://github.com/biolashonde/freedom_os.git
cd freedom_os
```

Copy the environment example:

```bash
cp .env.example .env
```

For XAMPP on Windows, open:

```text
http://localhost/freedom_os/install
```

The installer will:

- create or migrate the database
- write environment settings
- seed core recovery content
- create or promote the first superadmin
- write `storage/install.lock`

Manual migration is also supported:

```powershell
C:\xampp\php\php.exe database\migrate.php
```

Then visit:

```text
http://localhost/freedom_os/
```

## Environment

Use `.env.example` as the public-safe template. Never commit `.env`.

Important values:

```env
APP_URL=http://localhost/freedom_os
APP_DEBUG=false
APP_KEY=replace-this-with-a-long-random-secret

DB_HOST=localhost
DB_NAME=freedomos
DB_USER=root
DB_PASS=

MAIL_HOST=
MAIL_PORT=587
MAIL_USER=
MAIL_PASS=
MAIL_FROM=hello@example.com
MAIL_FROM_NAME=FreedomOS
```

AI provider keys are intentionally blank in the repository. Users add their own keys inside the app at:

```text
/settings/ai
```

Supported user-owned providers:

- Anthropic
- OpenAI
- Gemini
- OpenRouter

## Production Notes

- Point your web root to `public/` whenever possible.
- Keep `config/`, `core/`, `controllers/`, `database/`, `storage/`, and `views/` outside direct public web access.
- Set `APP_DEBUG=false`.
- Replace `APP_KEY`.
- Use HTTPS before relying on PWA installation.
- Configure SMTP before inviting real users.
- Run the installer once, then keep `storage/install.lock`.
- Keep backups of the database and storage folders.

If the homepage does not load after deployment, check:

- the installer has run
- `.env` points to the right database
- `APP_URL` matches the live domain or subfolder
- `storage/` is writable
- Apache rewrite rules are enabled
- the browser is not holding an old service worker cache

## FreedomGuard Extension

The extension source lives in:

```text
freedomguard/extension
```

See [docs/FREEDOMGUARD_EXTENSION.md](docs/FREEDOMGUARD_EXTENSION.md) for installation and testing notes.

## Admin Areas

- `/admin` for admin and mentor oversight
- `/admin/analytics` for superadmin analytics
- `/admin/donations` for manual transfer and donation platform settings
- `/admin/email` for SMTP senders, email queue, bulk email, and fallback delivery
- `/admin/content` for devotionals, music, games, videos, resources, meetings, and moderation
- `/admin/settings` for app URL and mail settings

## Donation Workflow

FreedomOS includes a public `/donate` page and superadmin-managed donation settings. A project owner can enable:

- manual bank transfer details
- donation platform links
- custom donation copy

For this public repository, donation details are intentionally not hardcoded. Configure them from the superadmin dashboard after installation.

To support this upstream project, use the repository contact channels below or the live donation page when published by the maintainer.

## Documentation

- [Install Guide](docs/INSTALL.md)
- [Admin Guide](docs/ADMIN.md)
- [Cron Jobs](docs/CRON.md)
- [Smoke Test](docs/SMOKE_TEST.md)
- [FreedomGuard Extension](docs/FREEDOMGUARD_EXTENSION.md)
- [Beta Readiness](BETA_READINESS.md)
- [Implementation Plan](IMPLEMENTATION_PLAN.md)

## Contributing

This project welcomes thoughtful collaborators: builders, designers, recovery mentors, pastors, privacy/security reviewers, QA testers, and people with lived experience who want the product to be safer and more useful.

Start with [CONTRIBUTING.md](CONTRIBUTING.md).

Good first contribution areas:

- accessibility improvements
- mobile UX
- safer moderation workflows
- documentation
- tests and smoke checks
- FreedomGuard extension polish
- content review for devotionals, prayers, music, and calming resources
- privacy and security hardening

## Security and Privacy

Please do not open public issues for vulnerabilities or private user data concerns. See [SECURITY.md](SECURITY.md).

This repository should not contain real credentials, API keys, personal user data, donation bank details, runtime logs, outbox files, or install locks. The `.gitignore` is set up to keep those out.

## Contact

- GitHub repository: https://github.com/biolashonde/freedom_os
- Issues: https://github.com/biolashonde/freedom_os/issues
- Pull requests: https://github.com/biolashonde/freedom_os/pulls

For donation partnerships, deployment help, or collaboration, open a GitHub issue or discussion in the repository.

## License

This project is released under the MIT License. See [LICENSE](LICENSE).
