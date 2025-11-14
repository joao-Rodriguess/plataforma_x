<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /plataforma_x/');
    exit;
}

require_once __DIR__ . '/../api/config.php';

$userId = intval($_SESSION['user_id']);
$stmt = $conexao->prepare('SELECT id, nome_completo, email, telefone, data_criacao, nome_usuario_banco FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel do Usuário - Plataforma X</title>
  <link rel="stylesheet" href="/plataforma_x/css/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>👤 Painel do Usuário</h1>
      <p>Bem-vindo, <?php echo htmlspecialchars($user['nome_completo']); ?>!</p>
    </div>

    <div class="alert-container">
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success show">
          ✅ Operação realizada com sucesso!
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error show">
          ❌ Erro: <?php echo htmlspecialchars(urldecode($_GET['error'] ?? 'Erro desconhecido')); ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2 style="margin-bottom: 20px; color: #333;">📋 Suas Informações</h2>

      <div class="user-info-grid">
        <div class="info-item">
          <strong>👤 Nome Completo:</strong><br>
          <?php echo htmlspecialchars($user['nome_completo']); ?>
        </div>

        <div class="info-item">
          <strong>📧 Email:</strong><br>
          <?php echo htmlspecialchars($user['email']); ?>
        </div>

        <div class="info-item">
          <strong>📞 Telefone:</strong><br>
          <?php echo htmlspecialchars($user['telefone'] ?? 'Não informado'); ?>
        </div>

        <div class="info-item">
          <strong>📅 Registrado em:</strong><br>
          <?php echo htmlspecialchars($user['data_criacao']); ?>
        </div>

        <div class="info-item">
          <strong>🗄️ Usuário do Banco:</strong><br>
          <code><?php echo htmlspecialchars($user['nome_usuario_banco'] ?? 'Não definido'); ?></code>
        </div>
      </div>

      <div class="user-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <h3 style="margin-bottom: 15px; color: #333;">⚙️ Ações Disponíveis</h3>

        <div class="action-buttons">
          <button type="button" class="btn btn-primary" onclick="testDatabaseConnection()">
            🔗 Testar Conexão DB
          </button>

          <a href="/plataforma_x/api/logout.php" class="btn btn-logout">
            🚪 Sair
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Teste de Conexão -->
  <div id="connectionModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>🔗 Teste de Conexão ao Banco</h2>
        <button class="modal-close" onclick="closeConnectionModal()">&times;</button>
      </div>
      <div id="connectionResult" style="margin-bottom: 20px;">
        <p style="color: #666;">Testando conexão...</p>
      </div>
      <div class="btn-group">
        <button type="button" class="btn btn-secondary" onclick="closeConnectionModal()">❌ Fechar</button>
      </div>
    </div>
  </div>

  <style>
    .user-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }

    .info-item {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      border-left: 4px solid #007bff;
    }

    .info-item strong {
      color: #333;
      font-size: 0.9em;
    }

    .info-item code {
      background-color: #e9ecef;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 0.9em;
    }

    .user-actions {
      text-align: center;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      font-size: 0.9em;
      transition: background-color 0.3s;
    }

    .btn-primary {
      background-color: #007bff;
      color: white;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .btn-logout {
      background-color: #dc3545;
      color: white;
    }

    .btn-logout:hover {
      background-color: #c82333;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }

    .btn-secondary:hover {
      background-color: #545b62;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
      background-color: white;
      margin: 15% auto;
      padding: 20px;
      border-radius: 8px;
      width: 80%;
      max-width: 500px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .modal-close {
      background: none;
      border: none;
      font-size: 1.5em;
      cursor: pointer;
      color: #666;
    }

    .btn-group {
      text-align: right;
    }
  </style>

  <script>
    function testDatabaseConnection() {
      document.getElementById('connectionResult').innerHTML = '<p style="color: #666;">🔄 Testando conexão com o banco de dados...</p>';
      document.getElementById('connectionModal').style.display = 'block';

      // Simular teste de conexão (em produção, isso seria uma chamada AJAX para uma API)
      setTimeout(() => {
        document.getElementById('connectionResult').innerHTML = `
          <div style="padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">
            <strong>✅ Conexão bem-sucedida!</strong><br>
            Seu usuário de banco está funcionando corretamente.
          </div>
        `;
      }, 2000);
    }

    function closeConnectionModal() {
      document.getElementById('connectionModal').style.display = 'none';
    }

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
      const modal = document.getElementById('connectionModal');
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    }
  </script>
</body>
</html>
