-- SHOTBASE / Baseball Photo Archive
-- MariaDB / MySQL 8.x compatible
-- Initial schema
--
-- Main concepts:
-- users -> projects -> photos
-- photos <-> tags
-- baseball_divisions -> teams
-- photos can be draft/private/public
-- Project photo limit: 200
-- Per-photo upload limit: 2MB (application-level validation)
-- Max long side: 2000px (application-level resize)

CREATE DATABASE IF NOT EXISTS shotbase
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE shotbase;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS photo_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS baseball_divisions;
DROP TABLE IF EXISTS photos;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- users
-- ------------------------------------------------------------
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    nickname VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    icon_path VARCHAR(500) DEFAULT NULL,
    header_path VARCHAR(500) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    watermark_enabled TINYINT(1) NOT NULL DEFAULT 1,
    watermark_position ENUM(
        'top-left',
        'top-center',
        'top-right',
        'middle-left',
        'center',
        'middle-right',
        'bottom-left',
        'bottom-center',
        'bottom-right'
    ) NOT NULL DEFAULT 'bottom-right',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- projects
-- ------------------------------------------------------------
CREATE TABLE projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    shooting_date DATE DEFAULT NULL,
    cover_photo_id BIGINT UNSIGNED DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_projects_user_id (user_id),
    CONSTRAINT fk_projects_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- photos
-- ------------------------------------------------------------
CREATE TABLE photos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    -- Stored/generated filename and relative path
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(500) NOT NULL,

    title VARCHAR(200) DEFAULT NULL,
    caption TEXT DEFAULT NULL,

    width INT UNSIGNED NOT NULL,
    height INT UNSIGNED NOT NULL,
    file_size INT UNSIGNED NOT NULL,

    -- draft / private / public
    visibility ENUM('draft', 'private', 'public') NOT NULL DEFAULT 'draft',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_photos_project_id (project_id),
    KEY idx_photos_user_id (user_id),
    KEY idx_photos_visibility (visibility),
    CONSTRAINT fk_photos_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_photos_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Add project cover FK after photos exists.
ALTER TABLE projects
    ADD CONSTRAINT fk_projects_cover_photo
    FOREIGN KEY (cover_photo_id) REFERENCES photos(id)
    ON DELETE SET NULL;


-- ------------------------------------------------------------
-- baseball_divisions
-- ------------------------------------------------------------
CREATE TABLE baseball_divisions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_divisions_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- teams
-- Teams belong to one baseball division.
-- Color pair:
--   border_color
--   background_color
-- ------------------------------------------------------------
CREATE TABLE teams (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    division_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(50) DEFAULT NULL,
    border_color VARCHAR(20) DEFAULT NULL,
    background_color VARCHAR(20) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by_user_id BIGINT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_teams_division_id (division_id),
    KEY idx_teams_created_by_user_id (created_by_user_id),
    CONSTRAINT fk_teams_division
        FOREIGN KEY (division_id) REFERENCES baseball_divisions(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_teams_created_by_user
        FOREIGN KEY (created_by_user_id) REFERENCES users(id)
        ON DELETE SET NULL,
    UNIQUE KEY uq_team_in_division (division_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- tags
--
-- User-created free-form tags.
-- kind:
--   player   = 選手
--   place    = 場所
--   activity = 撮影内容
--   other    = その他
--
-- team_id is optional. When present, the tag represents that team.
-- This allows team tags to be displayed as:
--   division -> team
-- with team colors.
-- ------------------------------------------------------------
CREATE TABLE tags (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    name VARCHAR(100) NOT NULL,

    -- Free-form tag category controlled by the service.
    kind ENUM('player', 'place', 'activity', 'other') DEFAULT 'other',

    -- Team-related tag. NULL for normal user-created tags.
    team_id BIGINT UNSIGNED DEFAULT NULL,

    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_tags_user_id (user_id),
    KEY idx_tags_team_id (team_id),
    KEY idx_tags_kind (kind),
    CONSTRAINT fk_tags_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tags_team
        FOREIGN KEY (team_id) REFERENCES teams(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- photo_tags
-- Many-to-many: one photo can have many tags.
-- ------------------------------------------------------------
CREATE TABLE photo_tags (
    photo_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (photo_id, tag_id),
    CONSTRAINT fk_photo_tags_photo
        FOREIGN KEY (photo_id) REFERENCES photos(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_photo_tags_tag
        FOREIGN KEY (tag_id) REFERENCES tags(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- Seed baseball divisions
-- Teams can be added later by administrators or user-facing UI.
-- ------------------------------------------------------------
INSERT INTO baseball_divisions (name, sort_order) VALUES
('プロ', 10),
('独立', 20),
('社会人', 30),
('大学', 40),
('高校', 50),
('中学', 60),
('クラブ', 70),
('代表', 80),
('女子野球', 90),
('海外野球', 100),
('その他', 110);


-- ------------------------------------------------------------
-- Recommended application-level constants
-- These are comments rather than DB constraints because:
-- 1) file size is validated before DB insert;
-- 2) 200-photo project limit is validated transactionally in PHP.
-- ------------------------------------------------------------
-- MAX_UPLOAD_BYTES = 2 * 1024 * 1024
-- MAX_LONG_SIDE_PX = 2000
-- MAX_PHOTOS_PER_PROJECT = 200

-- Optional future table:
-- user_favorites(user_id, favorite_user_id, created_at)
