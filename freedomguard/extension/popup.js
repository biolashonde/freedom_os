document.addEventListener('DOMContentLoaded', async () => {
  const settings = await chrome.storage.sync.get(['appUrl', 'deviceToken', 'enabled']);
  const ready = Boolean(settings.deviceToken);
  document.getElementById('status').textContent = settings.enabled === false
    ? 'Blocking is paused.'
    : ready
      ? 'Blocking is enabled.'
      : 'Add a device token to enable blocking.';
});

document.getElementById('open-options').addEventListener('click', () => {
  chrome.runtime.openOptionsPage();
});
