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
  (1, 1, 1, 'Rubik cube collection', 'rubik-cube-collection',
   'I have this awesome collection of rubik cubes and I''m willing to give them to a passionate rubik cube master.' || x'0a' || x'0a' || 'In exchange for them I would appreciate any golden card from the Duel Masters collection.',
   1, '2017-10-08 19:11:31', 1),
  (2, 1, 2, 'Pokémon card collection', 'pokemon-card-collection',
   'In good condition.' || x'0a' || x'0a' || 'I want the Pokémon card with Suicuno.', 1, '2017-10-08 19:16:33', 1),
  (3, 1, 1, 'Old radios', 'old-radios', 'Very good condition.' || x'0a' || x'0a' || 'Pick one and surprise me with an old lamp', 1,
   '2017-10-08 19:11:31', 1),
  (4, 1, 2, 'Napkin collection', 'napkins-collection',
   'Old, but gold. Took them from my mom''s collection.' || x'0a' || x'0a' || 'I do not have any special requests.', 1,
   '2017-10-08 19:16:33', 1),
  (5, 2, 2, 'Special insect', 'special-insect',
   'I have this awesome insect collection ''cause I''m in love with any kind of insects, including bugs and flies. But I am missing this gorgeous flying little odododo in the picture above. Can you help me complete my collection?',
   1, '2017-10-08 19:11:31', 1),
  (6, 1, 1, 'Awesome stamps', 'awesome-stamps',
   'So, as you see in the picture, I have a wonderful stamp collection.' || x'0a' || x'0a' || 'Surprise me with your offers!', 1,
   '2017-10-08 19:16:33', 1);
INSERT INTO `trades` (id, recipient_id, sender_id, message, status) VALUES
  (1, 1, 2, 'Hope you are okay with my trade :)', 1);
INSERT INTO `trade_offers` (id, trade_id, listing_id) VALUES
  (1, 1, 4),
  (2, 1, 6);
INSERT INTO `tags` (id, name) VALUES
  (1, 'cards'),
  (2, 'radios'),
  (3, 'insects'),
  (4, 'napkins'),
  (5, 'rubik'),
  (6, 'magazines'),
  (7, 'stamps');
INSERT INTO `listing_tags` (listing_id, tag_id) VALUES
  (1, 5),
  (2, 1),
  (3, 2),
  (4, 4),
  (5, 3),
  (6, 7);
INSERT INTO `bookmarks` (id, user_id, listing_id, added) VALUES
  (1, 2, 1, '2017-10-08 20:31:44');
INSERT INTO `images` (path, listing_id) VALUES
  ('rubik.jpg', 1),
  ('pokemon_cards.jpg', 2),
  ('radio.jpg', 3),
  ('napkins.jpg', 4),
  ('insect.jpg', 5),
  ('stamp.jpg', 6);
