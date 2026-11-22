-- À exécuter UNE FOIS sur la base déjà déployée (phpMyAdmin → SQL), après avoir sélectionné ta base.
ALTER TABLE games ADD COLUMN `round` INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE games MODIFY status ENUM('waiting','submitting','playing','round_over','finished') NOT NULL DEFAULT 'waiting';
ALTER TABLE secrets ADD COLUMN `round` INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE secrets
  ADD UNIQUE KEY uq_game_round_author (game_id, `round`, author_id),
  DROP INDEX uq_game_author;
