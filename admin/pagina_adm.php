<?php
session_start();
require_once __DIR__ . '/../api/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Buscar todos os usuários
$sql = "SELECT id, nome_completo, cpf, email, telefone, nome_usuario_banco, senha_banco, data_criacao, ativo FROM usuarios ORDER BY data_criacao DESC";
$result = $conexao->query($sql);
$usuarios = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Plataforma X</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Admin Dashboard - Plataforma X</h1>
            <p>Sistema de Gerenciamento de Usuários</p>
        </div>

        <div class="alert-container">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success show">
                    <?php if ($_GET['success'] === 'privileges_updated'): ?>
                        ✅ Privilégios atualizados com sucesso!
                    <?php elseif ($_GET['success'] === 'user_created'): ?>
                        ✅ Usuário cadastrado com sucesso!
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="admin-actions" style="margin-bottom: 20px;">
                <a href="cadastrar_usuario.php" class="btn btn-add">➕ Cadastrar Usuário</a>
                <form method="post" action="actions.php" style="display:inline">
                    <button type="submit" name="action" value="logout" class="btn btn-logout">🚪 Logout</button>
                </form>
            </div>

            <h2 style="margin-bottom: 20px; color: #333;">👥 Usuários Cadastrados</h2>
            <p style="color: #666; margin-bottom: 20px;">Aqui você pode visualizar todas as credenciais de banco de dados dos usuários.</p>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>👤 Nome</th>
                            <th>🆔 CPF</th>
                            <th>📧 Email</th>
                            <th>📞 Telefone</th>
                            <th>🗄️ DB Usuario</th>
                            <th>🔑 DB Senha</th>
                            <th>📅 Data</th>
                            <th>⚙️ Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($u['nome_completo'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($u['cpf'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($u['telefone'] ?? '-'); ?></td>
                            <td><code><?php echo htmlspecialchars($u['nome_usuario_banco'] ?? '-'); ?></code></td>
                            <td><span style="font-family: monospace;">••••••••</span></td>
                            <td><?php echo htmlspecialchars($u['data_criacao'] ?? ''); ?></td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-edit" onclick="openPrivilegesModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nome_completo']); ?>')">✏️ Editar</button>
                                    <form method="post" action="actions.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>" />
                                        <button type="submit" name="action" value="apply_db_user" class="btn btn-success">✅ Aplicar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Privilégios -->
    <div id="privilegesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⚙️ Editar Privilégios do Usuário</h2>
                <button class="modal-close" onclick="closePrivilegesModal()">&times;</button>
            </div>
            <p id="userNameDisplay" style="margin-bottom: 20px; color: #666;"></p>

            <form id="privilegesForm" method="post" action="actions.php">
                <input type="hidden" name="action" value="update_privileges">
                <input type="hidden" name="user_id" id="userIdInput">

                <div class="form-group">
                    <label for="privileges">🔐 Privilégios no Banco de Dados:</label>
                    <select name="privileges" id="privilegesSelect" required>
                        <option value="SELECT">👁️ SELECT (Somente leitura)</option>
                        <option value="SELECT,INSERT">📝 SELECT, INSERT (Leitura e inserção)</option>
                        <option value="SELECT,INSERT,UPDATE">✏️ SELECT, INSERT, UPDATE (Leitura, inserção e atualização)</option>
                        <option value="SELECT,INSERT,UPDATE,DELETE">🗑️ SELECT, INSERT, UPDATE, DELETE (Controle total)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tables">📋 Tabelas Específicas (opcional):</label>
                    <input type="text" name="tables" id="tablesInput" placeholder="Ex: usuarios,produtos (deixe vazio para todas)">
                    <small style="color: #666; font-size: 0.9em;">Deixe vazio para aplicar em todas as tabelas</small>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-success">💾 Salvar Privilégios</button>
                    <button type="button" class="btn btn-secondary" onclick="closePrivilegesModal()">❌ Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPrivilegesModal(userId, userName) {
            document.getElementById('userIdInput').value = userId;
            document.getElementById('userNameDisplay').textContent = 'Usuário: ' + userName;
            document.getElementById('privilegesModal').style.display = 'block';
        }

        function closePrivilegesModal() {
            document.getElementById('privilegesModal').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('privilegesModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
