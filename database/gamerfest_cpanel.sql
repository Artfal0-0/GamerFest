-- GamerFest schema and initial data for a NEW, EMPTY MySQL/MariaDB database.
-- Select the target database in phpMyAdmin before importing this file.
-- This file contains no DROP TABLE, DELETE FROM, or TRUNCATE statements.
-- ON DELETE CASCADE foreign keys are retained where the Laravel migrations define them.
--
-- BEFORE IMPORT:
-- 1. Replace @ADMIN_EMAIL with the real administrator email.
-- 2. Replace @ADMIN_BCRYPT_HASH with a bcrypt hash generated from a strong password.
--    Example on the server (type the password, then press Enter):
--      php -r 'echo password_hash(trim(fgets(STDIN)), PASSWORD_BCRYPT, ["cost" => 12]), PHP_EOL;'
--    Paste only the resulting $2y$... hash between the SQL quotes below.
-- 3. Import once into the empty database created in cPanel.

SET NAMES utf8mb4;

SET @ADMIN_EMAIL = 'admin@TU-DOMINIO.COM';
SET @ADMIN_BCRYPT_HASH = 'REEMPLAZAR_POR_HASH_BCRYPT';

CREATE TABLE `migrations` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) NOT NULL,
    `batch` int NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) NULL DEFAULT '',
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `remember_token` varchar(100) NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
    `id` varchar(255) NOT NULL,
    `user_id` bigint unsigned NULL,
    `ip_address` varchar(45) NULL,
    `user_agent` text NULL,
    `payload` longtext NOT NULL,
    `last_activity` int NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
    `key` varchar(255) NOT NULL,
    `value` mediumtext NOT NULL,
    `expiration` int NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
    `key` varchar(255) NOT NULL,
    `owner` varchar(255) NOT NULL,
    `expiration` int NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `queue` varchar(255) NOT NULL,
    `payload` longtext NOT NULL,
    `attempts` tinyint unsigned NOT NULL,
    `reserved_at` int unsigned NULL,
    `available_at` int unsigned NOT NULL,
    `created_at` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
    `id` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `total_jobs` int NOT NULL,
    `pending_jobs` int NOT NULL,
    `failed_jobs` int NOT NULL,
    `failed_job_ids` longtext NOT NULL,
    `options` mediumtext NULL,
    `cancelled_at` int NULL,
    `created_at` int NOT NULL,
    `finished_at` int NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `uuid` varchar(255) NOT NULL,
    `connection` text NOT NULL,
    `queue` text NOT NULL,
    `payload` longtext NOT NULL,
    `exception` longtext NOT NULL,
    `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `juegos` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `nombre` varchar(50) NOT NULL,
    `genero` varchar(50) NOT NULL,
    `costo_inscripcion` decimal(10,2) NOT NULL,
    `fecha_limite_inscripcion` date NOT NULL,
    `modalidad` enum('individual','grupo') NOT NULL,
    `img_id` varchar(255) NOT NULL DEFAULT 'sample_img',
    `reglamentos_pdf` varchar(500) NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `max_participantes` int NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sponsors` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `nombre` varchar(255) NOT NULL,
    `url_pagina` varchar(255) NULL,
    `url_imagen` varchar(255) NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ad_models` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `title` varchar(255) NOT NULL,
    `description` varchar(255) NULL,
    `image_url` varchar(255) NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `guard_name` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `guard_name` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
    `permission_id` bigint unsigned NOT NULL,
    `model_type` varchar(255) NOT NULL,
    `model_id` bigint unsigned NOT NULL,
    PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
    KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
    CONSTRAINT `model_has_permissions_permission_id_foreign`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
    `role_id` bigint unsigned NOT NULL,
    `model_type` varchar(255) NOT NULL,
    `model_id` bigint unsigned NOT NULL,
    PRIMARY KEY (`role_id`, `model_id`, `model_type`),
    KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
    CONSTRAINT `model_has_roles_role_id_foreign`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
    `permission_id` bigint unsigned NOT NULL,
    `role_id` bigint unsigned NOT NULL,
    PRIMARY KEY (`permission_id`, `role_id`),
    CONSTRAINT `role_has_permissions_permission_id_foreign`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `role_has_permissions_role_id_foreign`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_user` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `role_id` bigint unsigned NOT NULL,
    `user_id` bigint unsigned NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `role_user_role_id_index` (`role_id`),
    KEY `role_user_user_id_index` (`user_id`),
    CONSTRAINT `role_user_role_id_foreign`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `role_user_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equipos` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint unsigned NOT NULL,
    `id_juego` bigint unsigned NOT NULL,
    `nombre_equipo` varchar(255) NOT NULL,
    `miembros` text NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `equipos_id_juego_foreign` (`id_juego`),
    KEY `equipos_user_id_foreign` (`user_id`),
    CONSTRAINT `equipos_id_juego_foreign`
        FOREIGN KEY (`id_juego`) REFERENCES `juegos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `equipos_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ganadores_grupales` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_juego` bigint unsigned NOT NULL,
    `id_equipo` bigint unsigned NOT NULL,
    `posicion` int NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ganadores_grupales_id_juego_foreign` (`id_juego`),
    KEY `ganadores_grupales_id_equipo_foreign` (`id_equipo`),
    CONSTRAINT `ganadores_grupales_id_juego_foreign`
        FOREIGN KEY (`id_juego`) REFERENCES `juegos` (`id`),
    CONSTRAINT `ganadores_grupales_id_equipo_foreign`
        FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ganadores_individuales` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_juego` bigint unsigned NOT NULL,
    `user_id` bigint unsigned NOT NULL,
    `posicion` int NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ganadores_individuales_id_juego_foreign` (`id_juego`),
    KEY `ganadores_individuales_user_id_foreign` (`user_id`),
    CONSTRAINT `ganadores_individuales_id_juego_foreign`
        FOREIGN KEY (`id_juego`) REFERENCES `juegos` (`id`),
    CONSTRAINT `ganadores_individuales_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inscripciones_grupales` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_equipo` bigint unsigned NOT NULL,
    `id_juego` bigint unsigned NOT NULL,
    `estado` enum('inscrito','asistido','cancelado') NOT NULL,
    `comprobante_pago` varchar(255) NULL,
    `nro_comprobante` varchar(255) NULL,
    `valor_comprobante` double NULL,
    `estado_pago` enum('verificado','pendiente','cancelado') NOT NULL DEFAULT 'pendiente',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `inscripciones_grupales_id_equipo_foreign` (`id_equipo`),
    KEY `inscripciones_grupales_id_juego_foreign` (`id_juego`),
    CONSTRAINT `inscripciones_grupales_id_equipo_foreign`
        FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `inscripciones_grupales_id_juego_foreign`
        FOREIGN KEY (`id_juego`) REFERENCES `juegos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inscripciones_individuales` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint unsigned NOT NULL,
    `id_juego` bigint unsigned NOT NULL,
    `estado` enum('inscrito','asistido','cancelado') NOT NULL,
    `comprobante_pago` varchar(255) NULL,
    `nro_comprobante` varchar(255) NULL,
    `valor_comprobante` double NULL,
    `estado_pago` enum('verificado','pendiente','cancelado') NOT NULL DEFAULT 'pendiente',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `inscripciones_individuales_user_id_foreign` (`user_id`),
    KEY `inscripciones_individuales_id_juego_foreign` (`id_juego`),
    CONSTRAINT `inscripciones_individuales_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    CONSTRAINT `inscripciones_individuales_id_juego_foreign`
        FOREIGN KEY (`id_juego`) REFERENCES `juegos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fixed roles and permissions from RolePermissionsSeeder.
INSERT IGNORE INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('admin', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('jugador', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('create_juego', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('delete_juego', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('update_juego', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('create_ganador_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('delete_ganador_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('update_ganador_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('create_ganador_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('delete_ganador_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('update_ganador_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_self_user', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('logout_self', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_participantes', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_self_participante', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('create_participante', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('delete_self_participante', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('update_self_participante', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_equipos', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_self_equipo', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('create_equipo', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('delete_self_equipo', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('update_self_equipo', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_ganadores_individuales', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_self_ganador_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_ganadores_grupales', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_self_ganador_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_inscripciones_individuales', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_self_inscripcion_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('create_inscripcion_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('delete_self_inscripcion_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('update_self_inscripcion_individual', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_inscripciones_grupales', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('view_self_inscripcion_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('create_inscripcion_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('delete_self_inscripcion_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('update_self_inscripcion_grupal', 'web', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT `permissions`.`id`, `roles`.`id`
FROM `permissions`
JOIN `roles` ON `roles`.`name` = 'admin' AND `roles`.`guard_name` = 'web'
WHERE `permissions`.`guard_name` = 'web'
  AND `permissions`.`name` IN (
      'create_juego',
      'delete_juego',
      'update_juego',
      'create_ganador_individual',
      'delete_ganador_individual',
      'update_ganador_individual',
      'create_ganador_grupal',
      'delete_ganador_grupal',
      'update_ganador_grupal'
  );

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT `permissions`.`id`, `roles`.`id`
FROM `permissions`
JOIN `roles` ON `roles`.`name` = 'jugador' AND `roles`.`guard_name` = 'web'
WHERE `permissions`.`guard_name` = 'web'
  AND `permissions`.`name` IN (
      'view_self_user',
      'logout_self',
      'view_participantes',
      'view_self_participante',
      'create_participante',
      'delete_self_participante',
      'update_self_participante',
      'view_equipos',
      'view_self_equipo',
      'create_equipo',
      'delete_self_equipo',
      'update_self_equipo',
      'view_ganadores_individuales',
      'view_self_ganador_individual',
      'view_ganadores_grupales',
      'view_self_ganador_grupal',
      'view_inscripciones_individuales',
      'view_self_inscripcion_individual',
      'create_inscripcion_individual',
      'delete_self_inscripcion_individual',
      'update_self_inscripcion_individual',
      'view_inscripciones_grupales',
      'view_self_inscripcion_grupal',
      'create_inscripcion_grupal',
      'delete_self_inscripcion_grupal',
      'update_self_inscripcion_grupal'
  );

-- Fixed games from GamesSeeder. Matching modality and image id avoids duplicate games if only this block is rerun.
INSERT INTO `juegos` (
    `nombre`, `genero`, `costo_inscripcion`, `fecha_limite_inscripcion`,
    `modalidad`, `img_id`, `reglamentos_pdf`, `active`, `created_at`, `updated_at`
)
SELECT `seed`.`nombre`, `seed`.`genero`, `seed`.`costo_inscripcion`, `seed`.`fecha_limite_inscripcion`,
       `seed`.`modalidad`, `seed`.`img_id`, `seed`.`reglamentos_pdf`, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT 'COD Mobile' AS `nombre`, 'Shooter' AS `genero`, 25.00 AS `costo_inscripcion`,
           '2026-12-07' AS `fecha_limite_inscripcion`, 'grupo' AS `modalidad`,
           '/imgGames/cod-mobile.webp' AS `img_id`, '/reglamento/cod-mobile.pdf' AS `reglamentos_pdf`
    UNION ALL SELECT 'Valorant', 'Shooter', 25.00, '2026-12-07', 'grupo',
           '/imgGames/valorant.webp', '/reglamento/valorant.pdf'
    UNION ALL SELECT 'Left 4 Dead 2', 'Shooter', 20.00, '2026-12-07', 'grupo',
           '/imgGames/left-4-dead-2.webp', '/reglamento/left-4-dead-2.pdf'
    UNION ALL SELECT 'FIFA 26', 'Deportes', 3.00, '2026-12-07', 'individual',
           '/imgGames/fifa-26.webp', '/reglamento/fifa-26.pdf'
    UNION ALL SELECT 'Clash Royale', 'Estrategia', 3.00, '2026-12-07', 'individual',
           '/imgGames/clash-royale.webp', '/reglamento/clash-royale.pdf'
    UNION ALL SELECT 'Dragon Ball Sparking Zero', 'Peleas', 3.00, '2026-12-07', 'individual',
           '/imgGames/dragon-ball-sparking-zero.webp', '/reglamento/dragon-ball-sparking-zero.pdf'
    UNION ALL SELECT 'Mortal Kombat XL', 'Peleas', 3.00, '2026-12-07', 'individual',
           '/imgGames/mortal-kombat-xl.webp', '/reglamento/mortal-kombat-xl.pdf'
    UNION ALL SELECT 'Fortnite', 'Battle Royale', 3.00, '2026-12-07', 'individual',
           '/imgGames/fortnite.webp', '/reglamento/fortnite.pdf'
    UNION ALL SELECT 'Free Fire', 'Battle Royale', 3.00, '2026-12-07', 'individual',
           '/imgGames/free-fire.webp', '/reglamento/free-fire.pdf'
    UNION ALL SELECT 'King of Fighters', 'Peleas', 3.00, '2026-12-07', 'individual',
           '/imgGames/king-of-fighters.webp', '/reglamento/king-of-fighters.pdf'
    UNION ALL SELECT 'Smash Bros Ultimate', 'Peleas', 3.00, '2026-12-07', 'individual',
           '/imgGames/smash-bros-ultimate.webp', '/reglamento/smash-bros-ultimate.pdf'
    UNION ALL SELECT '40 (cartas físicas)', 'Cartas', 3.00, '2026-12-07', 'individual',
           '/imgGames/juego-de-40.webp', '/reglamento/juego-de-40.pdf'
) AS `seed`
WHERE NOT EXISTS (
    SELECT 1
    FROM `juegos`
    WHERE `juegos`.`modalidad` = `seed`.`modalidad`
      AND `juegos`.`img_id` = `seed`.`img_id`
);

-- Initial administrator; replace both values above before importing.
INSERT IGNORE INTO `users` (
    `name`, `email`, `phone`, `email_verified_at`, `password`,
    `remember_token`, `created_at`, `updated_at`
) VALUES (
    'Admin', @ADMIN_EMAIL, NULL, CURRENT_TIMESTAMP, @ADMIN_BCRYPT_HASH,
    NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
);

INSERT IGNORE INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`)
SELECT `roles`.`id`, `users`.`id`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM `roles`
JOIN `users` ON `users`.`email` = @ADMIN_EMAIL
WHERE `roles`.`name` = 'admin'
  AND `roles`.`guard_name` = 'web';

-- Mark all current Laravel migrations as applied so a future `php artisan migrate`
-- runs only migrations added after this SQL was generated.
INSERT INTO `migrations` (`migration`, `batch`) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2025_04_28_001_create_juegos_table', 1),
    ('2025_04_28_002_create_equipos_table', 1),
    ('2025_04_28_003_create_ganador_grupals_table', 1),
    ('2025_04_28_004_create_ganador_individuals_table', 1),
    ('2025_04_28_005_create_inscripcion_grupals_table', 1),
    ('2025_04_28_006_create_inscripcion_individuals_table', 1),
    ('2025_04_28_007_create_sponsors_table', 1),
    ('2025_04_28_008_create_permission_tables', 1),
    ('2025_04_28_009_create_role_user_table', 1),
    ('2025_05_21_071942_create_ad_models_table', 1),
    ('2025_05_21_101907_juegos_img_update', 1),
    ('2025_05_26_194049_add_reglamentos_pdf_to_juegos_table', 1),
    ('2025_05_28_113346_phone_on_users', 1),
    ('2025_05_28_145844_active_status', 1),
    ('2026_11_10_000001_make_sponsor_links_nullable', 1);
