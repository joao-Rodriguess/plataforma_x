<?php
session_start();
require_once __DIR__ . '/../api/config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    
    // Authenticate against usuarios table (both admin and regular users)
    $sql = "SELECT id, email, nome_completo, senha_usuario, role FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        $err = 'Erro ao preparar consulta';
    } else {
        $stmt->bind_param('s', $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($pass, $row['senha_usuario'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['nome_completo'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_role'] = $row['role'];

                if ($row['role'] === 'admin') {
                    $_SESSION['is_admin'] = true;
                    header('Location: pagina_adm.php');
                } else {
                    header('Location: ../user/dashboard.php');
                }
                exit;
            } else {
                $err = 'Credenciais inválidas';
            }
        } else {
            $err = 'Credenciais inválidas';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Plataforma X</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="home-box">
    <h1>🔐 Admin Login</h1>
    <p>Plataforma X - Painel Administrativo</p>
    <?php if ($err): ?>
        <div class="alert alert-error show" style="margin-bottom: 20px;"><?php echo $err; ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="user">Email do Admin</label>
        <input name="user" id="user" required placeholder="admin@plataforma.local" />
        <label for="pass">Senha</label>
        <input name="pass" id="pass" type="password" required placeholder="Sua senha" />
        <button type="submit" class="btn-admin">Entrar</button>
    </form>
    <p class="note">Use as credenciais do admin criado via <code>api/seed_admin.php</code></p>
</div>
</body>
</html>
