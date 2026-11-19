-- Import dans phpMyAdmin (onglet SQL) ou : mysql -u root < database.sql
CREATE DATABASE IF NOT EXISTS social_secret_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE social_secret_game;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  avatar VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS games (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_code CHAR(5) NOT NULL UNIQUE,
  status ENUM('waiting','submitting','playing','finished') NOT NULL DEFAULT 'waiting'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS game_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  game_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  is_host TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_game_user (game_id, user_id),
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS secrets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  game_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  guessed_by INT UNSIGNED NULL,
  is_correct TINYINT(1) NULL,
  UNIQUE KEY uq_game_author (game_id, author_id),
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (target_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
