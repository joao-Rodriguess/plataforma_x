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

header('Location: pagina_adm.php');
