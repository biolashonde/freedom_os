const CHECK_CACHE_MS = 30_000;
const decisionCache = new Map();

chrome.runtime.onInstalled.addListener(async () => {
  const current = await chrome.storage.sync.get(['appUrl', 'deviceToken', 'enabled']);
  await chrome.storage.sync.set({
    appUrl: current.appUrl || '',
    deviceToken: current.deviceToken || '',
    enabled: current.enabled !== false
  });
});

chrome.webNavigation.onBeforeNavigate.addListener(async (details) => {
  if (details.frameId !== 0 || !details.url.startsWith('http')) {
    return;
  }

  const settings = await chrome.storage.sync.get(['appUrl', 'deviceToken', 'enabled']);
  if (settings.enabled === false || !settings.deviceToken || !settings.appUrl) {
    return;
  }

  const appUrl = normalizeAppUrl(settings.appUrl);
  if (!appUrl) {
    return;
  }

  if (details.url.startsWith(appUrl)) {
    return;
  }

  const decision = await checkUrl(appUrl, settings.deviceToken, details.url);
  if (!decision.blocked || !decision.redirect) {
    return;
  }

  await chrome.tabs.update(details.tabId, { url: decision.redirect });
});

async function checkUrl(appUrl, token, url) {
  const cacheKey = `${token}:${url}`;
  const cached = decisionCache.get(cacheKey);
  if (cached && Date.now() - cached.at < CHECK_CACHE_MS) {
    return cached.decision;
  }

  try {
    const response = await fetch(`${appUrl}/blocker/check`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, url })
    });

    if (!response.ok) {
      return { blocked: false, reason: 'api_unavailable' };
    }

    const decision = await response.json();
    decisionCache.set(cacheKey, { at: Date.now(), decision });
    return decision;
  } catch (error) {
    return { blocked: false, reason: 'api_unavailable' };
  }
}

function normalizeAppUrl(value) {
  return String(value || '').trim().replace(/\/+$/, '');
}
