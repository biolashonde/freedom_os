USE freedomos;

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
