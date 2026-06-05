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
