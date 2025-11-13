<?php
/**
 * Seed Admin User
 * 
 * Execute this script to create/update the admin user in the usuarios table.
 * Usage: php api/seed_admin.php
 * 
 * Default admin credentials (CHANGE THESE IN PRODUCTION):
 *   Email: admin@plataforma.local
 *   Senha: admin123
 * 
 * To change: edit $ADMIN_EMAIL and $ADMIN_PASSWORD below before running.
 */

require_once __DIR__ . '/config.php';

// Admin credentials to seed (CHANGE IN PRODUCTION!)
$ADMIN_EMAIL = 'admin@plataforma.local';
$ADMIN_PASSWORD = 'admin123';
$ADMIN_NAME = 'Administrador';

// Hash the password
$hashed_password = password_hash($ADMIN_PASSWORD, PASSWORD_BCRYPT);

// Check if admin already exists
$sql_check = "SELECT id FROM usuarios WHERE email = ? AND role = 'admin'";
$stmt_check = $conexao->prepare($sql_check);
if (!$stmt_check) {
    echo "[ERRO] Erro ao preparar consulta: " . $conexao->error . "\n";
    exit(1);
}
$stmt_check->bind_param('s', $ADMIN_EMAIL);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result && $result->num_rows > 0) {
    // Admin exists; update password
    $sql_update = "UPDATE usuarios SET senha_usuario = ?, role = 'admin' WHERE email = ?";
    $stmt = $conexao->prepare($sql_update);
    if (!$stmt) {
        echo "[ERRO] Erro ao preparar update: " . $conexao->error . "\n";
        exit(1);
    }
    $stmt->bind_param('ss', $hashed_password, $ADMIN_EMAIL);
    if ($stmt->execute()) {
        echo "[✓] Admin já existia. Senha atualizada.\n";
    } else {
        echo "[ERRO] Erro ao atualizar admin: " . $stmt->error . "\n";
        exit(1);
    }
    $stmt->close();
} else {
    // Admin doesn't exist; insert new
    // Generate DB credentials
    $nome_usuario_banco = 'u_' . substr(bin2hex(random_bytes(4)), 0, 8);
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $senha_banco = '';
    for ($i = 0; $i < 16; $i++) {
        $senha_banco .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    $sql_insert = "INSERT INTO usuarios (nome_completo, email, senha_usuario, nome_usuario_banco, senha_banco, role, ativo) 
                   VALUES (?, ?, ?, ?, ?, 'admin', 1)";
    $stmt = $conexao->prepare($sql_insert);
    if (!$stmt) {
        echo "[ERRO] Erro ao preparar insert: " . $conexao->error . "\n";
        exit(1);
    }
    $stmt->bind_param('sssss', $ADMIN_NAME, $ADMIN_EMAIL, $hashed_password, $nome_usuario_banco, $senha_banco);
    if ($stmt->execute()) {
        echo "[✓] Admin criado com sucesso!\n";
        echo "    Email: $ADMIN_EMAIL\n";
        echo "    Senha: $ADMIN_PASSWORD (mude em produção!)\n";
        echo "    DB User: $nome_usuario_banco\n";
    } else {
        echo "[ERRO] Erro ao inserir admin: " . $stmt->error . "\n";
        exit(1);
    }
    $stmt->close();
}

$stmt_check->close();
echo "\n[OK] Seed de admin concluído.\n";
?>
