# FreedomGuard Browser Extension

The extension lives in:

```text
freedomguard/extension
```

## Local Install In Chrome/Edge

1. Open `chrome://extensions` or `edge://extensions`.
2. Enable Developer Mode.
3. Choose "Load unpacked".
4. Select `C:\xampp\htdocs\freedom_os\freedomguard\extension`.
5. Open FreedomOS at `/guard`.
6. Create a device token.
7. Download the extension ZIP from the Guard page.
8. Unzip it.
9. Load the unzipped folder as an unpacked extension.
10. Open the extension options page.
11. Set:
   - App URL: `http://localhost/freedom_os`
   - Device token: the token shown after creation
12. Run the test connection.

## Why Not True One-Click Local Install?

Desktop browsers intentionally block silent/private extension installation from arbitrary websites. For real one-click style install, publish the extension in the browser's official store and link users there.

## How It Works

- The background service worker listens to top-level navigations.
- It skips FreedomOS URLs to avoid blocking the app itself.
- It sends `{ token, url }` to `POST /blocker/check`.
- If the API returns `blocked: true`, the tab redirects to the provided `/blocked` URL.
- If the API is unavailable, it fails open for now to avoid breaking all browsing during development.

## Next Hardening Steps

- Add Firefox manifest variant if needed.
- Add local emergency cache rules for fail-closed mode.
- Add time-limited active allow rules after approved overrides.
- Add admin-level override review for users without partners.
