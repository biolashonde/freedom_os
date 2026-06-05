document.addEventListener('DOMContentLoaded', restore);
document.getElementById('settings-form').addEventListener('submit', save);
document.getElementById('test-button').addEventListener('click', testConnection);

async function restore() {
  const settings = await chrome.storage.sync.get(['appUrl', 'deviceToken', 'enabled']);
  document.getElementById('appUrl').value = settings.appUrl || '';
  document.getElementById('deviceToken').value = settings.deviceToken || '';
  document.getElementById('enabled').checked = settings.enabled !== false;
}

async function save(event) {
  event.preventDefault();
  await chrome.storage.sync.set({
    appUrl: normalize(document.getElementById('appUrl').value),
    deviceToken: document.getElementById('deviceToken').value.trim(),
    enabled: document.getElementById('enabled').checked
  });
  showStatus('Settings saved.');
}

async function testConnection() {
  const appUrl = normalize(document.getElementById('appUrl').value);
  const token = document.getElementById('deviceToken').value.trim();
  const output = document.getElementById('test-output');
  output.textContent = 'Testing...';

  try {
    const response = await fetch(`${appUrl}/blocker/check`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, url: 'https://example.com' })
    });
    output.textContent = JSON.stringify(await response.json(), null, 2);
  } catch (error) {
    output.textContent = `Connection failed: ${error.message}`;
  }
}

function normalize(value) {
  return String(value || '').trim().replace(/\/+$/, '');
}

function showStatus(message) {
  const status = document.getElementById('status');
  status.textContent = message;
  window.setTimeout(() => {
    status.textContent = '';
  }, 3000);
}
