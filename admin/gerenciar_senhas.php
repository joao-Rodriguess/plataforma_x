<?php
session_start();
require_once __DIR__ . '/../api/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$senhas_file = __DIR__ . '/../senhas_plain.txt';

// Função para ler senhas do arquivo
function lerSenhas() {
    global $senhas_file;
    $senhas = [];
    if (file_exists($senhas_file)) {
        $lines = file($senhas_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue; // Ignorar comentários
            $parts = explode('|', $line);
            if (count($parts) >= 5) {
                $senhas[] = [
                    'id' => $parts[0],
                    'nome' => $parts[1],
                    'email' => $parts[2],
                    'senha' => $parts[3],
                    'data' => $parts[4]
                ];
            }
        }
    }
    return $senhas;
}

// Função para salvar senhas no arquivo
function salvarSenhas($senhas) {
    global $senhas_file;
    $content = "# Arquivo de senhas em texto plano - ATENÇÃO: NÃO USE EM PRODUÇÃO!\n";
    $content .= "# Este arquivo contém senhas sem criptografia para fins de desenvolvimento\n";
    $content .= "# Formato: ID|Nome|Email|Senha_Plana|Data\n\n";

    foreach ($senhas as $senha) {
        $content .= $senha['id'] . '|' . $senha['nome'] . '|' . $senha['email'] . '|' . $senha['senha'] . '|' . $senha['data'] . "\n";
    }

    file_put_contents($senhas_file, $content);
}

// Processar ações
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add_password') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $plain_password = $_POST['plain_password'] ?? '';

    if ($user_id > 0 && !empty($plain_password)) {
        // Buscar dados do usuário
        $stmt = $conexao->prepare("SELECT nome_completo, email FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();

            $senhas = lerSenhas();

            // Verificar se já existe
            $exists = false;
            foreach ($senhas as &$senha) {
                if ($senha['id'] == $user_id) {
                    $senha['senha'] = $plain_password;
                    $senha['data'] = date('Y-m-d H:i:s');
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $senhas[] = [
                    'id' => $user_id,
                    'nome' => $user['nome_completo'],
                    'email' => $user['email'],
                    'senha' => $plain_password,
                    'data' => date('Y-m-d H:i:s')
                ];
            }

            salvarSenhas($senhas);
            header('Location: gerenciar_senhas.php?success=password_added');
            exit;
        }
    }
    header('Location: gerenciar_senhas.php?error=invalid_data');
    exit;
}

if ($action === 'delete_password') {
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        $senhas = lerSenhas();
        $senhas = array_filter($senhas, function($senha) use ($user_id) {
            return $senha['id'] != $user_id;
        });

        salvarSenhas($senhas);
        header('Location: gerenciar_senhas.php?success=password_deleted');
        exit;
    }
    header('Location: gerenciar_senhas.php?error=invalid_id');
    exit;
}

$senhas = lerSenhas();

// Buscar usuários para o dropdown
$sql = "SELECT id, nome_completo, email FROM usuarios ORDER BY nome_completo";
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
<title>Gerenciar Senhas - Plataforma X</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Gerenciar Senhas em Texto Plano</h1>
            <p>Sistema de gerenciamento de senhas não criptografadas</p>
            <a href="pagina_adm.php" class="btn btn-secondary" style="margin-top: 10px;">← Voltar ao Painel Admin</a>
        </div>

        <div class="alert-container">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success show">
                    <?php if ($_GET['success'] === 'password_added'): ?>
                        ✅ Senha adicionada/atualizada com sucesso!
                    <?php elseif ($_GET['success'] === 'password_deleted'): ?>
                        ✅ Senha removida com sucesso!
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error show">
                    <?php if ($_GET['error'] === 'invalid_data'): ?>
                        ❌ Dados inválidos fornecidos.
                    <?php elseif ($_GET['error'] === 'invalid_id'): ?>
                        ❌ ID de usuário inválido.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px; color: #333;">➕ Adicionar Senha em Texto Plano</h2>
            <form method="post" action="gerenciar_senhas.php">
                <input type="hidden" name="action" value="add_password">

                <div class="form-group">
                    <label for="user_id">👤 Selecionar Usuário:</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">Selecione um usuário...</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nome_completo'] . ' (' . $u['email'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="plain_password">🔑 Senha em Texto Plano:</label>
                    <input type="text" name="plain_password" id="plain_password" required placeholder="Digite a senha sem criptografia">
                    <small style="color: #dc3545; font-size: 0.9em;">⚠️ ATENÇÃO: Esta senha será armazenada sem criptografia!</small>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-success">💾 Salvar Senha</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px; color: #333;">📋 Senhas Armazenadas</h2>
            <p style="color: #666; margin-bottom: 20px;">Aqui estão todas as senhas armazenadas em texto plano. Use com extrema cautela!</p>

            <?php if (empty($senhas)): ?>
                <p style="color: #666; font-style: italic;">Nenhuma senha armazenada ainda.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>👤 Nome</th>
                                <th>📧 Email</th>
                                <th>🔑 Senha</th>
                                <th>📅 Data</th>
                                <th>⚙️ Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($senhas as $senha): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($senha['id']); ?></td>
                                <td><?php echo htmlspecialchars($senha['nome']); ?></td>
                                <td><?php echo htmlspecialchars($senha['email']); ?></td>
                                <td><code style="background-color: #f8f9fa; padding: 2px 4px; border-radius: 3px;"><?php echo htmlspecialchars($senha['senha']); ?></code></td>
                                <td><?php echo htmlspecialchars($senha['data']); ?></td>
                                <td>
                                    <form method="post" action="gerenciar_senhas.php" style="display:inline" onsubmit="return confirm('Tem certeza que deseja remover esta senha?')">
                                        <input type="hidden" name="action" value="delete_password">
                                        <input type="hidden" name="user_id" value="<?php echo $senha['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="font-size: 0.8em; padding: 4px 8px;">🗑️ Remover</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
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
    </style>
</body>
</html>
