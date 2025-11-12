<?php
/**
 * Script de Teste - Verifica a conexão com o banco de dados
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║   TESTE DE CONEXÃO COM BANCO DE DADOS                 ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";
echo "\n";

// Verificar se config.php existe
if (!file_exists('config.php')) {
    echo "❌ ERRO: Arquivo config.php não encontrado!\n";
    echo "   Execute primeiro: php setup.php\n";
    exit(1);
}

echo "[1/4] Carregando configuração...\n";
require 'config.php';
echo "   ✓ Config.php carregado\n";
echo "   └─ Host: " . DB_HOST . "\n";
echo "   └─ Usuário: " . DB_USER . "\n";
echo "   └─ Banco: " . DB_NAME . "\n";
echo "\n";

// Testar conexão
echo "[2/4] Testando conexão MySQL...\n";
$testConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($testConn->connect_error) {
    echo "❌ ERRO DE CONEXÃO!\n";
    echo "   Mensagem: " . $testConn->connect_error . "\n";
    echo "\n";
    echo "Causas possíveis:\n";
    echo "  1. MySQL não está rodando\n";
    echo "  2. Usuário não foi criado\n";
    echo "  3. Credenciais estão erradas\n";
    echo "\n";
    echo "Solução:\n";
    echo "  Execute: php create_user.php\n";
    exit(1);
}

echo "   ✓ Conectado com sucesso!\n";
echo "\n";

// Verificar se banco existe
echo "[3/4] Verificando banco de dados...\n";
$resultado = $testConn->query("SELECT DATABASE() as db_name");
if ($resultado) {
    $row = $resultado->fetch_assoc();
    echo "   ✓ Banco ativo: " . $row['db_name'] . "\n";
} else {
    echo "❌ Erro ao verificar banco: " . $testConn->error . "\n";
    exit(1);
}
echo "\n";

// Verificar tabelas
echo "[4/4] Verificando tabelas...\n";
$resultado = $testConn->query("SHOW TABLES");

if ($resultado) {
    $tabelas = [];
    while ($row = $resultado->fetch_assoc()) {
        $tabelas[] = $row['Tables_in_' . DB_NAME];
    }
    
    if (count($tabelas) > 0) {
        echo "   ✓ Tabelas encontradas:\n";
        foreach ($tabelas as $tabela) {
            echo "      └─ {$tabela}\n";
        }
    } else {
        echo "   ⚠️ Nenhuma tabela encontrada!\n";
        echo "   Execute: php install_database.php\n";
    }
} else {
    echo "❌ Erro ao listar tabelas: " . $testConn->error . "\n";
    exit(1);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║   ✅ TUDO OK - BANCO PRONTO PARA USAR!                ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";
echo "\n";

// Testar API
echo "Testando API...\n";
$resultado = $testConn->query("SELECT COUNT(*) as total FROM usuarios");
if ($resultado) {
    $row = $resultado->fetch_assoc();
    echo "   ✓ Total de usuários: " . $row['total'] . "\n";
}

$testConn->close();
echo "\n✅ Banco de dados está funcional!\n";
echo "\n";
?>
