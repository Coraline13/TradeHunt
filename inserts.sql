-- Populate database with mock data

INSERT INTO `locations` (id, country, city) VALUES
  (1, 'France', 'Lille');
INSERT INTO `profiles` (id, location_id, first_name, last_name, tel) VALUES
  (1, 1, 'Cristi', 'Vîjdea', '+40720527093'),
  (2, 1, 'Coralia', 'Bodea', '+40765980589');
INSERT INTO `users` (id, username, email, password_hash, profile_id) VALUES
  (1, 'axnsan', 'cristi@cvjd.me', '$2y$10$kx7DGHlkKghppE/F5xPv5OPwtNZm9uJOg78X5HKUg1a.pbHZ13Cbu', 1),
  (2, 'Coraline', 'coralia.bodea@gmail.com', '$2y$10$xG01P2p1wwKTNqLvtkrR4.iY8x9Rfd7aymieyB8yV1W3hH9JdfRFy', 2);
INSERT INTO `listings` (id, type, user_id, title, slug, description, status, added, location_id) VALUES
  (1, 1, 1, 'Test item 1', 'test-item-1', 'This is a test item posted as an offer', 1, '2017-10-08 19:11:31', 1),
  (2, 2, 2, 'Test item 2', 'test-item-2', 'This is a test item posted as a wish', 1, '2017-10-08 19:16:33', 1);
INSERT INTO `trades` (id, recipient_id, sender_id, message, status) VALUES
  (1, 1, 2, 'This is a test trade', 1);
INSERT INTO `trade_offers` (id, trade_id, listing_id) VALUES
  (1, 1, 1),
  (2, 1, 2);
INSERT INTO `tags` (id, name) VALUES
  (1, 'tag1'),
  (2, 'tag2'),
  (3, 'tag3');
INSERT INTO `listing_tags` (listing_id, tag_id) VALUES
  (1, 2),
  (1, 3),
  (2, 1),
  (2, 3);
INSERT INTO `bookmarks` (id, user_id, listing_id, added) VALUES
  (1, 2, 1, '2017-10-08 20:31:44');
