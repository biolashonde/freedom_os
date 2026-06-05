CREATE DATABASE IF NOT EXISTS freedomos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freedomos;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','mentor','admin','superadmin') DEFAULT 'user',
  phone VARCHAR(20) DEFAULT NULL,
  timezone VARCHAR(60) DEFAULT 'Europe/London',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS streaks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  current_days INT DEFAULT 0,
  longest_days INT DEFAULT 0,
  last_clean_date DATE DEFAULT NULL,
  total_relapses INT DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS check_ins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  mood TINYINT NOT NULL DEFAULT 3,
  urge_level TINYINT NOT NULL DEFAULT 1,
  note TEXT,
  relapsed TINYINT(1) DEFAULT 0,
  prayer_done TINYINT(1) DEFAULT 0,
  scripture_read TINYINT(1) DEFAULT 0,
  checked_in_at DATE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_date (user_id, checked_in_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS sos_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  trigger_note TEXT,
  partner_alerted TINYINT(1) DEFAULT 0,
  resolved TINYINT(1) DEFAULT 0,
  resolved_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notification_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) NOT NULL,
  recipient VARCHAR(150) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  status VARCHAR(40) DEFAULT 'queued',
  meta JSON DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS smtp_accounts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(120) NOT NULL,
  host VARCHAR(180) NOT NULL,
  port INT NOT NULL DEFAULT 587,
  encryption ENUM('tls','ssl','none') DEFAULT 'tls',
  username VARCHAR(180) DEFAULT NULL,
  password TEXT,
  from_email VARCHAR(180) NOT NULL,
  from_name VARCHAR(120) DEFAULT 'FreedomOS',
  reply_to VARCHAR(180) DEFAULT NULL,
  priority INT DEFAULT 100,
  daily_limit INT DEFAULT 500,
  sent_today INT DEFAULT 0,
  last_sent_date DATE DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  last_error TEXT,
  last_tested_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_smtp_active_priority (active, priority)
);

CREATE TABLE IF NOT EXISTS email_queue (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(60) NOT NULL,
  recipient_email VARCHAR(180) NOT NULL,
  recipient_name VARCHAR(120) DEFAULT NULL,
  subject VARCHAR(255) NOT NULL,
  body LONGTEXT NOT NULL,
  status ENUM('queued','sending','sent','failed') DEFAULT 'queued',
  priority INT DEFAULT 100,
  attempts INT DEFAULT 0,
  max_attempts INT DEFAULT 5,
  smtp_account_id BIGINT UNSIGNED DEFAULT NULL,
  last_error TEXT,
  meta JSON DEFAULT NULL,
  scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_queue_status (status, scheduled_at, priority),
  INDEX idx_email_queue_type (type),
  FOREIGN KEY (smtp_account_id) REFERENCES smtp_accounts(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_password_reset_user (user_id, expires_at)
);

CREATE TABLE IF NOT EXISTS safety_plans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  top_trigger VARCHAR(255),
  escape_action VARCHAR(255),
  support_contact VARCHAR(255),
  truth_statement TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_safety_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS goals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  category ENUM('spiritual','relational','physical','vocational','ministry') DEFAULT 'spiritual',
  title VARCHAR(200) NOT NULL,
  description TEXT,
  target_date DATE DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS testimonies (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200),
  body LONGTEXT,
  is_public TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS accountability_pairs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  partner_id BIGINT UNSIGNED NOT NULL,
  status ENUM('pending','active','paused') DEFAULT 'pending',
  invite_token VARCHAR(64),
  sos_alerts TINYINT(1) DEFAULT 1,
  weekly_digest TINYINT(1) DEFAULT 1,
  relapse_visibility TINYINT(1) DEFAULT 0,
  paired_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_partner (user_id, partner_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (partner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS digest_runs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pair_id BIGINT UNSIGNED NOT NULL,
  week_start DATE NOT NULL,
  week_end DATE NOT NULL,
  recipient VARCHAR(150) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pair_week (pair_id, week_start),
  FOREIGN KEY (pair_id) REFERENCES accountability_pairs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS nudge_runs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  nudge_type VARCHAR(60) NOT NULL,
  run_date DATE NOT NULL,
  recipient VARCHAR(150) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_nudge_day (user_id, nudge_type, run_date),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS donation_settings (
  id TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
  manual_enabled TINYINT(1) DEFAULT 0,
  platform_enabled TINYINT(1) DEFAULT 0,
  headline VARCHAR(255) DEFAULT NULL,
  body TEXT,
  bank_name VARCHAR(150) DEFAULT NULL,
  account_name VARCHAR(150) DEFAULT NULL,
  account_number VARCHAR(80) DEFAULT NULL,
  routing_code VARCHAR(80) DEFAULT NULL,
  iban VARCHAR(120) DEFAULT NULL,
  swift VARCHAR(80) DEFAULT NULL,
  reference_note VARCHAR(255) DEFAULT NULL,
  platform_links JSON DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS app_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(80) NOT NULL UNIQUE,
  setting_value TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

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

CREATE TABLE IF NOT EXISTS sos_resources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('music','game','video','resource') NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT,
  url VARCHAR(500) DEFAULT NULL,
  duration_label VARCHAR(40) DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS online_meetings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT,
  meeting_url VARCHAR(500) NOT NULL,
  platform VARCHAR(80) DEFAULT NULL,
  starts_at DATETIME DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS community_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  status ENUM('visible','hidden') DEFAULT 'visible',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_community_created (created_at),
  INDEX idx_community_status (status)
);

CREATE TABLE IF NOT EXISTS devotionals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  day_number SMALLINT DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  theme VARCHAR(80) DEFAULT 'Identity',
  scripture_ref VARCHAR(60),
  scripture_text TEXT,
  body TEXT,
  prayer TEXT,
  ai_generated TINYINT(1) DEFAULT 0,
  published_date DATE DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_global_day (user_id, day_number)
);

CREATE TABLE IF NOT EXISTS blocker_devices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  token_prefix VARCHAR(12) NOT NULL,
  last_seen_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS blocker_rules (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  rule_type ENUM('domain','keyword') NOT NULL DEFAULT 'domain',
  pattern VARCHAR(255) NOT NULL,
  action ENUM('block','allow') DEFAULT 'block',
  reason VARCHAR(100) DEFAULT 'custom',
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rule_lookup (rule_type, active),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS blocker_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  device_id VARCHAR(100),
  blocked_url VARCHAR(500),
  reason VARCHAR(100),
  partner_notified TINYINT(1) DEFAULT 0,
  attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_date (attempted_at)
);

CREATE TABLE IF NOT EXISTS blocker_overrides (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  url VARCHAR(500) NOT NULL,
  reason TEXT,
  status ENUM('pending','approved','denied') DEFAULT 'pending',
  requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  reviewed_at DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS scriptures (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(30) NOT NULL,
  text TEXT NOT NULL,
  category ENUM('sos','identity','purpose','worship','general') DEFAULT 'general'
);

INSERT INTO scriptures (reference, text, category) VALUES
('1 Corinthians 10:13', 'God is faithful, and he will not let you be tempted beyond your ability, but with the temptation he will also provide the way of escape.', 'sos'),
('Psalm 34:18', 'The Lord is near to the brokenhearted and saves the crushed in spirit.', 'sos'),
('Romans 8:1', 'There is therefore now no condemnation for those who are in Christ Jesus.', 'identity')
ON DUPLICATE KEY UPDATE reference = reference;

INSERT INTO devotionals (user_id, day_number, title, theme, scripture_ref, scripture_text, body, prayer, ai_generated, published_date) VALUES
(NULL, 1, 'You Are Not Your Urge', 'Identity', 'Romans 8:1', 'There is therefore now no condemnation for those who are in Christ Jesus.', 'An urge can feel loud, but it is not lord. It can make promises, raise alarms, and ask for your attention, but it cannot name you. In Christ, your identity is not built from yesterday, your browser history, your worst habit, or your strongest temptation. Your identity begins with grace.\n\nToday, practice separating signal from self. You may feel pressure, but you are not pressure. You may feel weakness, but you are not abandoned. You can pause, breathe, change location, and choose the next clean step.', 'Father, remind me that condemnation is not your voice. Help me receive grace and take the next faithful step today.', 0, CURDATE()),
(NULL, 2, 'The Way Of Escape', 'Action', '1 Corinthians 10:13', 'God is faithful, and he will not let you be tempted beyond your ability, but with the temptation he will also provide the way of escape.', 'Freedom often arrives as a practical doorway. A walk. A text. A cold drink of water. A decision to leave the room. The way of escape may not feel dramatic, but it is holy.\n\nDo not wait until the urge is gone before you move. Movement is part of resistance. Today, decide your first escape action before pressure rises. Make it plain enough that you can obey it when your mind feels crowded.', 'God, show me the way of escape and give me the humility to take it quickly.', 0, CURDATE()),
(NULL, 3, 'Tell The Truth Early', 'Accountability', 'James 5:16', 'Confess your sins to one another and pray for one another, that you may be healed.', 'Shame says, hide until you are stronger. Wisdom says, speak while the pressure is still small. You do not need to perform a crisis to deserve support. You can tell the truth early.\n\nAccountability is not surveillance. It is shared courage. One honest message can interrupt an entire pattern. Today, choose one person or one written check-in where you will tell the truth without dressing it up.', 'Lord, give me courage to be honest before I am overwhelmed. Teach me to receive support without shame.', 0, CURDATE()),
(NULL, 4, 'Small Obedience Counts', 'Purpose', 'Luke 16:10', 'One who is faithful in a very little is also faithful in much.', 'A clean day is not empty. It is full of small obediences: closing the tab, making the call, taking the walk, sleeping on time, praying badly but honestly. These are not small to God.\n\nYou are rebuilding trust with yourself one choice at a time. Do not despise the quiet win. Mark it. Thank God for it. Then take the next one.', 'Father, help me value small faithfulness. Build a steadier life in me one choice at a time.', 0, CURDATE()),
(NULL, 5, 'After A Reset', 'Grace', 'Psalm 34:18', 'The Lord is near to the brokenhearted and saves the crushed in spirit.', 'A reset is serious, but it is not the end of your story. The enemy would love to turn a stumble into a spiral. Grace calls you back to the light quickly.\n\nTell the truth. Clean up the environment. Check in. Ask what happened without cruelty. Then begin again. The Lord is near, not waiting at a distance for you to become impressive.', 'God, meet me with mercy. Help me learn clearly, repair quickly, and keep walking with you.', 0, CURDATE()),
(NULL, 6, 'Build What You Are For', 'Purpose', 'Ephesians 2:10', 'For we are his workmanship, created in Christ Jesus for good works.', 'Freedom is not only leaving something behind. It is becoming available for love, work, worship, friendship, service, and joy. If recovery is only subtraction, your heart will stay hungry.\n\nDo one constructive thing today. Make something, serve someone, move your body, study, clean, plan, worship. Build evidence that your life is bigger than the old loop.', 'Lord, show me what I am for. Give me energy to build a life that reflects your goodness.', 0, CURDATE()),
(NULL, 7, 'You Are Still Becoming', 'Hope', 'Philippians 1:6', 'He who began a good work in you will bring it to completion at the day of Jesus Christ.', 'Progress may feel uneven, but God is not confused by the pace of your healing. He is patient, truthful, and persistent. The work he begins, he continues.\n\nLook back over the week and name one place you resisted, one place you learned, and one place you need support. Hope gets stronger when it becomes specific.', 'Father, thank you for continuing your work in me. Help me notice progress and keep surrendering the places still under construction.', 0, CURDATE())
ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body), prayer = VALUES(prayer);

INSERT INTO blocker_rules (user_id, rule_type, pattern, reason, active, created_at) VALUES
(NULL, 'keyword', 'porn', 'adult content', 1, NOW()),
(NULL, 'keyword', 'xxx', 'adult content', 1, NOW()),
(NULL, 'keyword', 'hentai', 'adult content', 1, NOW()),
(NULL, 'keyword', 'onlyfans', 'adult content', 1, NOW())
ON DUPLICATE KEY UPDATE pattern = pattern;
