# Contributing to FreedomOS

Thank you for helping build a recovery product with care. FreedomOS deals with sensitive human moments, so contributions should optimize for privacy, dignity, clarity, and reliability.

## Ways To Help

- Fix bugs and routing issues
- Improve mobile and PWA behavior
- Add tests, smoke checks, and documentation
- Improve accessibility and keyboard navigation
- Review privacy and security risks
- Strengthen moderation and community safety workflows
- Improve the FreedomGuard extension
- Add better recovery resources, devotionals, prayers, games, and calm activities
- Refine installer and deployment flows

## Local Setup

1. Copy `.env.example` to `.env`.
2. Open `/install` or run `database/migrate.php`.
3. Create a local superadmin through the installer.
4. Run the smoke test in `docs/SMOKE_TEST.md`.

## Pull Request Guidelines

- Keep changes focused.
- Do not commit `.env`, real keys, runtime storage, logs, outbox messages, local cache files, install locks, or generated archives.
- Include setup or migration notes when database schema changes.
- Add or update documentation for new user-facing workflows.
- Treat recovery content carefully; avoid shaming language.
- Prefer simple native PHP patterns already used in the app.

## Code Style

- PHP 8.2+ with strict types where existing files use them.
- Keep controllers thin and use core helpers/classes where possible.
- Escape rendered user content with `e()`.
- Use CSRF protection for state-changing forms.
- Keep public web access rooted at `public/`.

## Testing

Before opening a pull request, run the most relevant checks:

```powershell
C:\xampp\php\php.exe -l path\to\changed.php
node --check public\assets\js\app.js
node --check public\sw.js
```

Then walk through `docs/SMOKE_TEST.md` for larger changes.
