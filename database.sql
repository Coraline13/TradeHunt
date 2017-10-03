PRAGMA writable_schema = 1;
DELETE FROM sqlite_master WHERE type = 'table' AND name NOT IN ('schema', 'sqlite_sequence');
DELETE FROM sqlite_master WHERE type = 'index';
PRAGMA writable_schema = 0;

CREATE TABLE `users` (
  `id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `username`	TEXT NOT NULL,
  `email`	TEXT NOT NULL,
  `password_hash`	TEXT NOT NULL
);
CREATE TABLE `sessions` (
  `id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `user_id`	INTEGER NOT NULL,
  `token`	TEXT NOT NULL,
  `expiration`	DATETIME NOT NULL,
  CONSTRAINT `sessions_users_id_fk` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE UNIQUE INDEX `users_username_uindex` ON `users` (
  `username`
);
CREATE UNIQUE INDEX `users_email_uindex` ON `users` (
  `email`
);
CREATE UNIQUE INDEX `sessions_token_uindex` ON `sessions` (
  `token`
);
