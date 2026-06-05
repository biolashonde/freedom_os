USE freedomos;

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
