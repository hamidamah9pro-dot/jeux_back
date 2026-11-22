# Backend – Qui a balancé ?

## En local
1. `composer install`
2. Importer `database.sql`
3. `php -S localhost:8000` depuis ce dossier → http://localhost:8000/api/*.php

## En production (hébergement PHP + MySQL, ex. cPanel)
1. Créer une base MySQL + un utilisateur dans cPanel, puis importer `database.sql` via phpMyAdmin
   (retirer les 2 premières lignes CREATE DATABASE / USE si la base existe déjà).
2. Copier `config/local.example.php` en `config/local.php` et renseigner les accès + `allowed_origin` (URL du front).
3. Uploader tout le dossier (avec `vendor/`) pour que `https://ton-domaine/api/game_state.php` réponde.
4. Le site doit être en HTTPS (obligatoire, le front Vercel est en HTTPS).
