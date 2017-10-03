CREATE TABLE `schema` (
  `version` INTEGER NOT NULL,
  `created_on` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`version`)
);
INSERT INTO `schema`(`version`) VALUES (1);
