<?php
require_once __DIR__ . '/config.php';

echo "[*] Executando migração: adicionar coluna 'role' à tabela 'usuarios'...\n";

$sql = "ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'user'";
if ($conexao->query($sql)) {
    echo "[✓] Coluna 'role' adicionada com sucesso!\n";
} else {
    // Check if column already exists
    if (strpos($conexao->error, "Duplicate column name") !== false || strpos($conexao->error, "already exists") !== false) {
        echo "[!] Coluna 'role' já existe. Continuando...\n";
    } else {
        echo "[ERRO] Erro ao adicionar coluna: " . $conexao->error . "\n";
        exit(1);
    }
}

echo "[OK] Migração concluída.\n";
?>
