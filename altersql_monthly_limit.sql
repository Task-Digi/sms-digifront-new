-- Adds the monthly SMS quota column to the users table.
-- Run this once against the production database (e.g. via phpMyAdmin).
-- NULL = unlimited, 0 = blocked, positive integer = cap.

ALTER TABLE `portu_sms`.`users`
  ADD COLUMN `monthly_sms_limit` INT UNSIGNED NULL DEFAULT NULL AFTER `is_active`;

-- Mark the migration as applied so `php artisan migrate` won't try to re-run it.
INSERT INTO `portu_sms`.`migrations` (`migration`, `batch`)
VALUES ('2026_05_25_000000_add_monthly_sms_limit_to_users',
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `portu_sms`.`migrations`) AS m));
