USE freedomos;

ALTER TABLE blocker_rules
  ADD COLUMN IF NOT EXISTS action ENUM('block','allow') DEFAULT 'block' AFTER pattern;
