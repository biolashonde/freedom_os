USE freedomos;

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

INSERT INTO blocker_rules (user_id, rule_type, pattern, reason, active, created_at) VALUES
(NULL, 'keyword', 'porn', 'adult content', 1, NOW()),
(NULL, 'keyword', 'xxx', 'adult content', 1, NOW()),
(NULL, 'keyword', 'hentai', 'adult content', 1, NOW()),
(NULL, 'keyword', 'onlyfans', 'adult content', 1, NOW());
