-- Add columns for login attempt tracking
ALTER TABLE users ADD COLUMN failed_attempts INT DEFAULT 0;
ALTER TABLE users ADD COLUMN locked_until DATETIME NULL;