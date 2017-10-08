CREATE TABLE `locations` (
  `id`      INTEGER PRIMARY KEY AUTOINCREMENT,
  `country` TEXT NOT NULL,
  `city`    TEXT NOT NULL
);
CREATE TABLE `profiles` (
  `id`          INTEGER PRIMARY KEY AUTOINCREMENT,
  `location_id` INTEGER NOT NULL,
  `first_name`  TEXT    NOT NULL,
  `last_name`   TEXT    NOT NULL,
  `tel`         TEXT    NOT NULL,
  FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE TABLE `users` (
  `id`            INTEGER PRIMARY KEY AUTOINCREMENT,
  `username`      TEXT NOT NULL UNIQUE,
  `email`         TEXT NOT NULL UNIQUE,
  `password_hash` TEXT NOT NULL,
  `profile_id`    INTEGER NOT NULL,
  FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE TABLE `sessions` (
  `id`         INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id`    INTEGER  NOT NULL,
  `token`      TEXT     NOT NULL,
  `expiration` DATETIME NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE TABLE listings
(
  `id`          INTEGER PRIMARY KEY AUTOINCREMENT,
  `type`        INTEGER  NOT NULL,
  `user_id`     INTEGER  NOT NULL,
  `title`       TEXT     NOT NULL,
  `slug`        TEXT     NOT NULL UNIQUE,
  `description` TEXT     NOT NULL,
  `status`      INTEGER  NOT NULL,
  `added`       DATETIME NOT NULL   DEFAULT CURRENT_TIMESTAMP,
  `location_id` INTEGER  NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE TABLE `images` (
  `id`         INTEGER PRIMARY KEY AUTOINCREMENT,
  `path`       TEXT    NOT NULL UNIQUE,
  `listing_id` INTEGER NOT NULL,
  FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE TABLE `tags` (
  `id`   INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE
);
CREATE TABLE `listing_tags` (
  `listing_id` INTEGER NOT NULL,
  `tag_id`     INTEGER NOT NULL,
  PRIMARY KEY (`tag_id`, `listing_id`),
  FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE UNIQUE INDEX `pk_reverse_listing_tags`
  ON `listing_tags` (`tag_id`, `listing_id`);
CREATE TABLE `bookmarks` (
  `id`         INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id`    INTEGER  NOT NULL,
  `listing_id` INTEGER  NOT NULL,
  `added`      DATETIME NOT NULL   DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE UNIQUE INDEX `uk_bookmarks_user_listing`
  ON `bookmarks` (`user_id`, `listing_id`);
CREATE TABLE `trades` (
  `id`           INTEGER PRIMARY KEY AUTOINCREMENT,
  `recipient_id` INTEGER NOT NULL,
  `sender_id`    INTEGER NOT NULL,
  `requestor_id` INTEGER NOT NULL,
  `message`      TEXT    NOT NULL    DEFAULT '',
  `accepted`     BOOLEAN NOT NULL    DEFAULT 0,
  FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`requestor_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CHECK (`requestor_id` = `recipient_id` OR `requestor_id` = `sender_id`),
  CHECK (`recipient_id` <> `sender_id`)
);
CREATE TABLE `trade_offers` (
  `id`         INTEGER PRIMARY KEY AUTOINCREMENT,
  `trade_id`   INTEGER NOT NULL,
  `listing_id` INTEGER NOT NULL,
  FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`trade_id`) REFERENCES `trades` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TRIGGER validate_trade_offer
  BEFORE
  INSERT
  ON `trade_offers`
BEGIN
  SELECT RAISE(ABORT, 'Offered listing must belong to either sender or recipient')
  WHERE
    (SELECT user_id
     FROM listings
     WHERE listings.id = NEW.listing_id)
    NOT IN
    (SELECT
       recipient_id,
       sender_id
     FROM trades
     WHERE trades.id = NEW.trade_id);
END;
