<?php
// Configuração do banco de dados (GERADO AUTOMATICAMENTE)
// Data de criação: 2025-11-12 20:03:20

define('DB_HOST', 'localhost');
define('DB_USER', 'plat_904c2cf4');
define('DB_PASSWORD', 'gk3yF%0PbBM$Fue27vby');
define('DB_NAME', 'plataforma_x');

// Criar conexão
$conexao = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conexao->connect_error) {
    die(json_encode(['erro' => 'Falha na conexão: ' . $conexao->connect_error]));
}

// Definir charset
$conexao->set_charset("utf8");
?>