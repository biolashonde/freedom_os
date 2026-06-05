USE freedomos;

ALTER TABLE users
  MODIFY COLUMN role ENUM('user','mentor','admin','superadmin') DEFAULT 'user';
