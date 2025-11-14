<?php
session_start();
require_once __DIR__ . '/../api/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Buscar todos os usuários
$sql = "SELECT id, nome_completo, cpf, email, telefone, nome_usuario_banco, senha_banco, data_criacao, ativo, role FROM usuarios ORDER BY data_criacao DESC";
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
                    <?php elseif ($_GET['success'] === 'test_connection_success'): ?>
                        ✅ Conexão testada com sucesso! Credenciais do usuário funcionam corretamente.
                    <?php elseif ($_GET['success'] === 'user_deleted'): ?>
                        ✅ Usuário excluído com sucesso!
                    <?php elseif ($_GET['success'] === 'role_toggled'): ?>
                        ✅ Role alterado com sucesso! Novo role: <?php echo htmlspecialchars($_GET['new_role'] ?? ''); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error show">
                    <?php if ($_GET['error'] === 'test_invalid_id'): ?>
                        ❌ Erro: ID de usuário inválido para teste de conexão.
                    <?php elseif ($_GET['error'] === 'test_user_not_found'): ?>
                        ❌ Erro: Usuário não encontrado para teste de conexão.
                    <?php elseif ($_GET['error'] === 'test_credentials_not_set'): ?>
                        ❌ Erro: Credenciais de banco não foram definidas para este usuário. Use o botão "Aplicar" primeiro.
                    <?php elseif ($_GET['error'] === 'test_connection_failed'): ?>
                        ❌ Falha na conexão: <?php echo htmlspecialchars(urldecode($_GET['message'] ?? 'Erro desconhecido')); ?>
                    <?php elseif ($_GET['error'] === 'test_query_failed'): ?>
                        ❌ Falha na query de teste: <?php echo htmlspecialchars(urldecode($_GET['message'] ?? 'Erro desconhecido')); ?>
                    <?php elseif ($_GET['error'] === 'delete_invalid_id'): ?>
                        ❌ Erro: ID de usuário inválido para exclusão.
                    <?php elseif ($_GET['error'] === 'delete_self'): ?>
                        ❌ Erro: Você não pode excluir sua própria conta!
                    <?php elseif ($_GET['error'] === 'delete_user_not_found'): ?>
                        ❌ Erro: Usuário não encontrado para exclusão.
                    <?php elseif ($_GET['error'] === 'delete_failed'): ?>
                        ❌ Falha na exclusão: <?php echo htmlspecialchars(urldecode($_GET['message'] ?? 'Erro desconhecido')); ?>
                    <?php elseif ($_GET['error'] === 'toggle_invalid_id'): ?>
                        ❌ Erro: ID de usuário inválido para alteração de role.
                    <?php elseif ($_GET['error'] === 'toggle_self'): ?>
                        ❌ Erro: Você não pode alterar seu próprio role!
                    <?php elseif ($_GET['error'] === 'toggle_user_not_found'): ?>
                        ❌ Erro: Usuário não encontrado para alteração de role.
                    <?php elseif ($_GET['error'] === 'toggle_failed'): ?>
                        ❌ Falha na alteração de role: <?php echo htmlspecialchars(urldecode($_GET['message'] ?? 'Erro desconhecido')); ?>
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
            <p style="color: #666; margin-bottom: 20px;">Aqui você pode visualizar todas as credenciais de banco de dados dos usuários. Use "✅ Aplicar" para criar o usuário MySQL, "🔗 Testar" para verificar se funciona, "👤 Toggle Role" para alternar admin/usuário, "🔐 Ver Senha" para visualizar a senha descriptografada e "🗑️ Excluir" para remover usuários.</p>

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
                            <th>👤 Role</th>
                            <th>🔐 Senha</th>
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
                                <span class="role-badge <?php echo ($u['role'] === 'admin') ? 'role-admin' : 'role-user'; ?>">
                                    <?php echo ($u['role'] === 'admin') ? '👑 Admin' : '👤 Usuário'; ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-info" onclick="openPasswordModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nome_completo']); ?>')">🔐 Ver Senha</button>
                            </td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-edit" onclick="openPrivilegesModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nome_completo']); ?>')">✏️ Editar</button>
                                    <form method="post" action="actions.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>" />
                                        <button type="submit" name="action" value="apply_db_user" class="btn btn-success">✅ Aplicar</button>
                                    </form>
                                    <form method="post" action="actions.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>" />
                                        <button type="submit" name="action" value="test_connection" class="btn btn-primary" title="Testar conexão com credenciais do usuário">🔗 Testar</button>
                                    </form>
                                    <form method="post" action="actions.php" style="display:inline" onsubmit="return confirm('Tem certeza que deseja alterar o role deste usuário?')">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>" />
                                        <button type="submit" name="action" value="toggle_role" class="btn btn-warning" title="Alternar entre admin e usuário">👤 Toggle Role</button>
                                    </form>
                                    <form method="post" action="actions.php" style="display:inline" onsubmit="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita!')">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>" />
                                        <button type="submit" name="action" value="delete_user" class="btn btn-danger" title="Excluir usuário permanentemente">🗑️ Excluir</button>
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

    <!-- Modal para Visualizar Senha -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔐 Senha do Usuário</h2>
                <button class="modal-close" onclick="closePasswordModal()">&times;</button>
            </div>
            <div id="passwordContent" style="margin-bottom: 20px;">
                <p style="color: #666;">Carregando senha...</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">❌ Fechar</button>
            </div>
        </div>
    </div>

    <style>
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .role-admin {
            background-color: #ff6b6b;
            color: white;
        }
        .role-user {
            background-color: #4ecdc4;
            color: white;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            margin: 2px;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-warning {
            background-color: #f39c12;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            margin: 2px;
        }
        .btn-warning:hover {
            background-color: #e67e22;
        }
        .btn-info {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            margin: 2px;
        }
        .btn-info:hover {
            background-color: #2980b9;
        }
    </style>

    <script>
        function openPrivilegesModal(userId, userName) {
            document.getElementById('userIdInput').value = userId;
            document.getElementById('userNameDisplay').textContent = 'Usuário: ' + userName;
            document.getElementById('privilegesModal').style.display = 'block';
        }

        function closePrivilegesModal() {
            document.getElementById('privilegesModal').style.display = 'none';
        }

        function openPasswordModal(userId, userName) {
            document.getElementById('passwordContent').innerHTML = '<p style="color: #666;">Carregando senha...</p>';
            document.getElementById('passwordModal').style.display = 'block';

            // Fazer requisição AJAX para buscar a senha
            fetch('actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_password&id=' + userId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('passwordContent').innerHTML = `
                        <p><strong>Usuário:</strong> ${userName}</p>
                        <p><strong>Senha:</strong></p>
                        <div style="background-color: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;">
                            <code style="font-family: monospace; font-size: 1.1em; color: #495057;">${data.password}</code>
                        </div>
                        <p style="color: #6c757d; font-size: 0.9em; margin-top: 10px;">
                            ⚠️ Esta senha está criptografada no banco de dados. Esta é a versão descriptografada apenas para visualização.
                        </p>
                    `;
                } else {
                    document.getElementById('passwordContent').innerHTML = `
                        <p style="color: #dc3545;">❌ Erro ao carregar senha: ${data.error}</p>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('passwordContent').innerHTML = `
                    <p style="color: #dc3545;">❌ Erro de comunicação: ${error.message}</p>
                `;
            });
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const privilegesModal = document.getElementById('privilegesModal');
            const passwordModal = document.getElementById('passwordModal');
            if (event.target === privilegesModal) {
                privilegesModal.style.display = 'none';
            }
            if (event.target === passwordModal) {
                passwordModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
