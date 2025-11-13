-- Add role column to usuarios table if not exists
ALTER TABLE usuarios ADD COLUMN role VARCHAR(50) DEFAULT 'user' AFTER ativo;
