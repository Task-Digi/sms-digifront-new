CREATE TABLE IF NOT EXISTS `portu_sms`.`templates` (
  `id` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` VARCHAR(20) NULL DEFAULT NULL,
  `slug` VARCHAR(60) NULL DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `body` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `templates_slug_unique` (`slug` ASC),
  INDEX `templates_sender_id_index` (`sender_id` ASC));

INSERT INTO `portu_sms`.`migrations` (`migration`, `batch`)
VALUES ('2026_05_12_000000_create_templates_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `portu_sms`.`migrations`) AS m));
claude --resume 1cbd04a9-6c4b-4ef4-9301-dd25822a6368