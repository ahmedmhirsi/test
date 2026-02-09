-- Migration: Add missing columns to utilisateur table
-- Date: 2026-02-09
-- Description: Add OAuth, email verification, password reset, SMS verification, and 2FA columns

USE smartnexus;

-- OAuth Google Integration
ALTER TABLE utilisateur 
ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER expertise,
ADD COLUMN oauth_provider VARCHAR(50) NULL AFTER google_id;

-- Email Verification
ALTER TABLE utilisateur 
ADD COLUMN verification_token VARCHAR(100) NULL AFTER oauth_provider,
ADD COLUMN verification_token_expires_at DATETIME NULL AFTER verification_token;

-- Password Reset
ALTER TABLE utilisateur 
ADD COLUMN reset_password_token VARCHAR(100) NULL AFTER verification_token_expires_at,
ADD COLUMN reset_password_token_expires_at DATETIME NULL AFTER reset_password_token;

-- SMS Verification
ALTER TABLE utilisateur 
ADD COLUMN sms_verification_code VARCHAR(6) NULL AFTER reset_password_token_expires_at,
ADD COLUMN sms_code_expires_at DATETIME NULL AFTER sms_verification_code;

-- Two-Factor Authentication (2FA)
ALTER TABLE utilisateur 
ADD COLUMN totp_secret VARCHAR(255) NULL AFTER sms_code_expires_at,
ADD COLUMN is_2fa_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER totp_secret,
ADD COLUMN backup_codes LONGTEXT NULL AFTER is_2fa_enabled,
ADD COLUMN two_factor_confirmed_at DATETIME NULL AFTER backup_codes,
ADD COLUMN last_2fa_check_at DATETIME NULL AFTER two_factor_confirmed_at;

-- Verify the changes
SELECT 'Migration completed successfully!' as status;
