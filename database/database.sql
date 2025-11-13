-- Criar banco de dados plataforma_x
CREATE DATABASE IF NOT EXISTS plataforma_x;
USE plataforma_x;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_completo VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    nome_usuario_banco VARCHAR(50) NOT NULL UNIQUE,
    senha_banco VARCHAR(255) NOT NULL,
    senha_usuario VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);


