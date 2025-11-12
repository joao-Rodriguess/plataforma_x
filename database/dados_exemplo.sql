-- Dados de exemplo para teste

-- Usuário 1
INSERT INTO usuarios (nome_completo, cpf, email, telefone, nome_usuario_banco, senha_banco, senha_usuario)
VALUES ('João Silva Santos', '123.456.789-00', 'joao.silva@exemplo.com', '(11) 99999-1111', 'joao_silva', 'senha_banco_123', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P6FSQA');

-- Usuário 2
INSERT INTO usuarios (nome_completo, cpf, email, telefone, nome_usuario_banco, senha_banco, senha_usuario)
VALUES ('Maria Oliveira Costa', '987.654.321-00', 'maria.oliveira@exemplo.com', '(11) 98888-2222', 'maria_costa', 'senha_banco_456', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P6FSQA');

-- Usuário 3
INSERT INTO usuarios (nome_completo, cpf, email, telefone, nome_usuario_banco, senha_banco, senha_usuario)
VALUES ('Pedro Rodrigues Lima', '456.789.123-00', 'pedro.rodrigues@exemplo.com', '(11) 97777-3333', 'pedro_lima', 'senha_banco_789', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P6FSQA');

-- Observação: As senhas dos usuários acima são criptografadas. A senha descriptografada é "password123"
