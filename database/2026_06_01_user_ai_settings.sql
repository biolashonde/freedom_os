USE freedomos;

CREATE TABLE IF NOT EXISTS user_ai_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  provider ENUM('auto','anthropic','openai','gemini','openrouter') DEFAULT 'auto',
  anthropic_key TEXT,
  anthropic_model VARCHAR(120) DEFAULT 'claude-sonnet-4-20250514',
  openai_key TEXT,
  openai_model VARCHAR(120) DEFAULT 'gpt-4o-mini',
  gemini_key TEXT,
  gemini_model VARCHAR(120) DEFAULT 'gemini-2.5-flash',
  openrouter_key TEXT,
  openrouter_model VARCHAR(160) DEFAULT 'openai/gpt-4o-mini',
  openrouter_site_url VARCHAR(255) DEFAULT NULL,
  openrouter_app_name VARCHAR(120) DEFAULT 'FreedomOS',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_ai_settings (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

DELETE FROM app_settings
WHERE setting_key IN (
  'ai_provider',
  'anthropic_key',
  'anthropic_model',
  'openai_key',
  'openai_model',
  'gemini_key',
  'gemini_model',
  'openrouter_key',
  'openrouter_model',
  'openrouter_site_url',
  'openrouter_app_name'
);
