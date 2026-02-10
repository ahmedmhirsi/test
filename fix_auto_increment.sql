-- Script pour corriger les tables de permissions
-- Exécuter dans phpMyAdmin

-- Vérifier et corriger la table permission
ALTER TABLE `permission` MODIFY `id` INT AUTO_INCREMENT;

-- Vérifier et corriger la table role  
ALTER TABLE `role` MODIFY `id` INT AUTO_INCREMENT;

-- Vérifier et corriger la table role_permission
ALTER TABLE `role_permission` MODIFY `id` INT AUTO_INCREMENT;

-- Vérifier et corriger la table user_permission
ALTER TABLE `user_permission` MODIFY `id` INT AUTO_INCREMENT;
