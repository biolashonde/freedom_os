USE freedomos;

ALTER TABLE sos_resources
  MODIFY COLUMN type ENUM('music','game','video','resource') NOT NULL;

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
