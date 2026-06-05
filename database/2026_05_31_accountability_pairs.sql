USE freedomos;

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
