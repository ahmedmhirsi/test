-- Script SQL pour créer les tables du système de permissions
-- À exécuter manuellement dans phpMyAdmin ou MySQL Workbench

-- Table Permission
CREATE TABLE IF NOT EXISTS `permission` (
  `id_permission` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `resource` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  UNIQUE INDEX `UNIQ_E04992AA5E237E06` (`name`),
  PRIMARY KEY(`id_permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Role
CREATE TABLE IF NOT EXISTS `role` (
  `id_role` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  UNIQUE INDEX `UNIQ_57698A6A5E237E06` (`name`),
  PRIMARY KEY(`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table RolePermission (jonction)
CREATE TABLE IF NOT EXISTS `role_permission` (
  `id_role_permission` INT AUTO_INCREMENT NOT NULL,
  `role_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  `assigned_at` DATETIME NOT NULL,
  INDEX `IDX_6F7DF886D60322AC` (`role_id`),
  INDEX `IDX_6F7DF886FED90CCA` (`permission_id`),
  UNIQUE INDEX `role_permission_unique` (`role_id`, `permission_id`),
  PRIMARY KEY(`id_role_permission`),
  CONSTRAINT `FK_6F7DF886D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id_role`) ON DELETE CASCADE,
  CONSTRAINT `FK_6F7DF886FED90CCA` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id_permission`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table UserPermission
CREATE TABLE IF NOT EXISTS `user_permission` (
  `id_user_permission` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  `resource_type` VARCHAR(50) DEFAULT NULL,
  `resource_id` INT DEFAULT NULL,
  `granted` TINYINT(1) NOT NULL DEFAULT 1,
  `granted_at` DATETIME NOT NULL,
  `granted_by_id` INT DEFAULT NULL,
  INDEX `IDX_472E5446A76ED395` (`user_id`),
  INDEX `IDX_472E5446FED90CCA` (`permission_id`),
  INDEX `IDX_472E54465DA0FB8` (`granted_by_id`),
  PRIMARY KEY(`id_user_permission`),
  CONSTRAINT `FK_472E5446A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `FK_472E5446FED90CCA` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id_permission`) ON DELETE CASCADE,
  CONSTRAINT `FK_472E54465DA0FB8` FOREIGN KEY (`granted_by_id`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table user_role (jonction many-to-many)
CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  INDEX `IDX_2DE8C6A3A76ED395` (`user_id`),
  INDEX `IDX_2DE8C6A3D60322AC` (`role_id`),
  PRIMARY KEY(`user_id`, `role_id`),
  CONSTRAINT `FK_2DE8C6A3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `FK_2DE8C6A3D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id_role`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter les colonnes à user_channel
ALTER TABLE `user_channel` 
  ADD COLUMN `can_invite` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `can_manage_messages` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `can_create_meetings` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `can_pin_messages` TINYINT(1) NOT NULL DEFAULT 0;
