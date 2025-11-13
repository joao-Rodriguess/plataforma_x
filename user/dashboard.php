<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /plataforma_x/');
    exit;
}

require_once __DIR__ . '/../api/config.php';

$userId = intval($_SESSION['user_id']);
$stmt = $conexao->prepare('SELECT id, nome_completo, email, telefone, data_criacao FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Área do Usuário - Plataforma X</title>
  <link rel="stylesheet" href="/plataforma_x/css/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Área do Usuário</h1>
      <p>Bem-vindo, <?php echo htmlspecialchars($user['nome_completo']); ?></p>
    </div>

    <div class="card">
      <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
      <p><strong>Telefone:</strong> <?php echo htmlspecialchars($user['telefone']); ?></p>
      <p><strong>Registrado em:</strong> <?php echo htmlspecialchars($user['data_criacao']); ?></p>

      <p><a href="/plataforma_x/api/logout.php">Sair</a></p>
    </div>
  </div>
</body>
</html>
