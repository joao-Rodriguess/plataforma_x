<?php
session_start();
header('Content-Type: application/json');

// Accept JSON or form POST
$input = json_decode(file_get_contents('php://input'), true);
$login = $input['login'] ?? $_POST['login'] ?? null;
$password = $input['password'] ?? $_POST['password'] ?? null;

require_once __DIR__ . '/config.php';

if (!$login || !$password) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Login e senha são obrigatórios']);
    exit;
}

// Authenticate against usuarios table (both admin and regular users)
$sql = "SELECT id, nome_completo, email, senha_usuario, role FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conexao->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao preparar consulta: ' . $conexao->error]);
    exit;
}
$stmt->bind_param('s', $login);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    if (password_verify($password, $row['senha_usuario'])) {
        // Successful login
        session_regenerate_id(true);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['nome_completo'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['user_role'] = $row['role'];
        
        if ($row['role'] === 'admin') {
            $_SESSION['is_admin'] = true;
            echo json_encode(['sucesso' => true, 'redirect' => '/plataforma_x/admin/pagina_adm.php']);
        } else {
            echo json_encode(['sucesso' => true, 'redirect' => '/plataforma_x/user/dashboard.php']);
        }
        exit;
    }
}

http_response_code(401);
echo json_encode(['sucesso' => false, 'erro' => 'Credenciais inválidas']);
exit;

?>
