<?php
session_start();
require_once __DIR__ . '/../api/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$action = $_POST['action'] ?? '';
if ($action === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($action === 'apply_db_user') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        die('ID inválido');
    }

    // Buscar credenciais do usuário
    $stmt = $conexao->prepare("SELECT nome_usuario_banco, senha_banco FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        die('Usuário não encontrado');
    }
    $row = $res->fetch_assoc();
    $dbUser = $row['nome_usuario_banco'];
    $dbPass = $row['senha_banco'];
    $stmt->close();

    // Conectar como root usando constantes do config.php
    $rootUser = DB_ROOT_USER;
    $rootPass = DB_ROOT_PASSWORD;
    $mysqli = new mysqli('localhost', $rootUser, $rootPass);
    if ($mysqli->connect_error) {
        die('Erro ao conectar como root: ' . $mysqli->connect_error);
    }

    // Criar usuário MySQL e conceder privilégios
    $userEsc = $mysqli->real_escape_string($dbUser);
    $passEsc = $mysqli->real_escape_string($dbPass);
    $sql1 = "CREATE USER IF NOT EXISTS '$userEsc'@'localhost' IDENTIFIED BY '$passEsc'";
    $sql2 = "GRANT SELECT, INSERT, UPDATE, DELETE ON plataforma_x.* TO '$userEsc'@'localhost'";

    if (!$mysqli->query($sql1)) {
        die('Erro ao criar usuário MySQL: ' . $mysqli->error);
    }
    if (!$mysqli->query($sql2)) {
        die('Erro ao aplicar privilégios: ' . $mysqli->error);
    }
    $mysqli->query('FLUSH PRIVILEGES');

    header('Location: pagina_adm.php');
    exit;
}

if ($action === 'test_connection') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        header('Location: pagina_adm.php?error=test_invalid_id');
        exit;
    }

    // Buscar credenciais do usuário
    $stmt = $conexao->prepare("SELECT nome_usuario_banco, senha_banco FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        header('Location: pagina_adm.php?error=test_user_not_found');
        exit;
    }
    $row = $res->fetch_assoc();
    $dbUser = $row['nome_usuario_banco'];
    $dbPass = $row['senha_banco'];
    $stmt->close();

    // Verificar se as credenciais estão definidas
    if (empty($dbUser) || empty($dbPass)) {
        header('Location: pagina_adm.php?error=test_credentials_not_set');
        exit;
    }

    // Verificar se o usuário MySQL existe primeiro
    $rootUser = DB_ROOT_USER;
    $rootPass = DB_ROOT_PASSWORD;
    $rootConn = new mysqli('localhost', $rootUser, $rootPass);
    if ($rootConn->connect_error) {
        header('Location: pagina_adm.php?error=test_connection_failed&message=' . urlencode('Erro ao conectar como root: ' . $rootConn->connect_error));
        exit;
    }

    // Verificar se o usuário existe no MySQL
    $checkUserSql = "SELECT User FROM mysql.user WHERE User = '$dbUser' AND Host = 'localhost'";
    $checkResult = $rootConn->query($checkUserSql);
    if (!$checkResult || $checkResult->num_rows === 0) {
        $rootConn->close();
        header('Location: pagina_adm.php?error=test_connection_failed&message=' . urlencode('Usuário MySQL não existe. Use o botão "Aplicar" primeiro.'));
        exit;
    }
    $rootConn->close();

    // Tentar conectar com as credenciais do usuário
    try {
        $testConn = new mysqli('localhost', $dbUser, $dbPass, 'plataforma_x');
        if ($testConn->connect_error) {
            header('Location: pagina_adm.php?error=test_connection_failed&message=' . urlencode($testConn->connect_error));
            exit;
        }

        // Testar uma query simples
        $testQuery = $testConn->query("SELECT 1");
        if (!$testQuery) {
            $testConn->close();
            header('Location: pagina_adm.php?error=test_query_failed&message=' . urlencode($testConn->error));
            exit;
        }

        $testConn->close();
        header('Location: ../api/usuarios.php?success=test_connection_success');
    } catch (Exception $e) {
        header('Location: pagina_adm.php?error=test_connection_failed&message=' . urlencode($e->getMessage()));
    }
    exit;
}

if ($action === 'update_privileges') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $privileges = $_POST['privileges'] ?? '';
    $tables = trim($_POST['tables'] ?? '');

    if ($user_id <= 0 || empty($privileges)) {
        die('Dados inválidos');
    }

    // Buscar credenciais do usuário
    $stmt = $conexao->prepare("SELECT nome_usuario_banco FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        die('Usuário não encontrado');
    }
    $row = $res->fetch_assoc();
    $dbUser = $row['nome_usuario_banco'];
    $stmt->close();

    // Conectar como root
    $rootUser = DB_ROOT_USER;
    $rootPass = DB_ROOT_PASSWORD;
    $mysqli = new mysqli('localhost', $rootUser, $rootPass);
    if ($mysqli->connect_error) {
        die('Erro ao conectar como root: ' . $mysqli->connect_error);
    }

    // Revogar privilégios existentes
    $revokeSql = "REVOKE ALL PRIVILEGES ON plataforma_x.* FROM '$dbUser'@'localhost'";
    $mysqli->query($revokeSql);

    // Aplicar novos privilégios
    $privilegesArray = explode(',', $privileges);
    $privilegesStr = implode(', ', $privilegesArray);

    if (empty($tables)) {
        // Privilégios em todas as tabelas
        $grantSql = "GRANT $privilegesStr ON plataforma_x.* TO '$dbUser'@'localhost'";
    } else {
        // Privilégios em tabelas específicas
        $tablesArray = array_map('trim', explode(',', $tables));
        $tablesStr = implode('`, `', $tablesArray);
        $grantSql = "GRANT $privilegesStr ON plataforma_x.`$tablesStr` TO '$dbUser'@'localhost'";
    }

    if (!$mysqli->query($grantSql)) {
        die('Erro ao aplicar privilégios: ' . $mysqli->error);
    }

    $mysqli->query('FLUSH PRIVILEGES');
    $mysqli->close();

    header('Location: pagina_adm.php?success=privileges_updated');
    exit;
}

if ($action === 'delete_user') {
    $user_id = intval($_POST['id'] ?? 0);
    if ($user_id <= 0) {
        header('Location: pagina_adm.php?error=delete_invalid_id');
        exit;
    }

    // Não permitir auto-exclusão
    if ($user_id === $_SESSION['user_id']) {
        header('Location: pagina_adm.php?error=delete_self');
        exit;
    }

    // Verificar se usuário existe
    $stmt = $conexao->prepare("SELECT nome_usuario_banco FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        header('Location: pagina_adm.php?error=delete_user_not_found');
        exit;
    }
    $row = $res->fetch_assoc();
    $dbUser = $row['nome_usuario_banco'];
    $stmt->close();

    // Deletar usuário MySQL se existir
    $rootUser = DB_ROOT_USER;
    $rootPass = DB_ROOT_PASSWORD;
    $mysqli = new mysqli('localhost', $rootUser, $rootPass);
    if (!$mysqli->connect_error) {
        $dropUserSql = "DROP USER IF EXISTS '$dbUser'@'localhost'";
        $mysqli->query($dropUserSql);
        $mysqli->query('FLUSH PRIVILEGES');
        $mysqli->close();
    }

    // Deletar usuário da tabela usuarios
    $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: pagina_adm.php?success=user_deleted');
        exit;
    } else {
        $stmt->close();
        header('Location: pagina_adm.php?error=delete_failed&message=' . urlencode($stmt->error));
        exit;
    }
}

if ($action === 'toggle_role') {
    $user_id = intval($_POST['id'] ?? 0);
    if ($user_id <= 0) {
        header('Location: pagina_adm.php?error=toggle_invalid_id');
        exit;
    }

    // Não permitir mudança de próprio role
    if ($user_id === $_SESSION['user_id']) {
        header('Location: pagina_adm.php?error=toggle_self');
        exit;
    }

    // Buscar role atual
    $stmt = $conexao->prepare("SELECT role FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        header('Location: pagina_adm.php?error=toggle_user_not_found');
        exit;
    }
    $row = $res->fetch_assoc();
    $currentRole = $row['role'];
    $stmt->close();

    // Alternar role
    $newRole = ($currentRole === 'admin') ? 'user' : 'admin';

    // Atualizar role
    $stmt = $conexao->prepare("UPDATE usuarios SET role = ? WHERE id = ?");
    $stmt->bind_param('si', $newRole, $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: pagina_adm.php?success=role_toggled&new_role=' . $newRole);
        exit;
    } else {
        $stmt->close();
        header('Location: pagina_adm.php?error=toggle_failed&message=' . urlencode($stmt->error));
        exit;
    }
}

header('Location: pagina_adm.php');
?>
