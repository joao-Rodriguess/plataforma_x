<?php
session_start();
require_once __DIR__ . '/../api/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_completo = trim($_POST['nome_completo'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha_usuario = trim($_POST['senha_usuario'] ?? '');

    $errors = [];

    // Validações
    if (empty($nome_completo)) {
        $errors[] = 'Nome completo é obrigatório';
    }

    if (empty($email)) {
        $errors[] = 'Email é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    } else {
        // Verificar se email já existe
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email já cadastrado';
        }
        $stmt->close();
    }

    if (!empty($cpf)) {
        // Validar CPF (formato básico)
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11) {
            $errors[] = 'CPF deve ter 11 dígitos';
        }
    }

    if (empty($senha_usuario)) {
        $errors[] = 'Senha do usuário é obrigatória';
    } elseif (strlen($senha_usuario) < 6) {
        $errors[] = 'Senha do usuário deve ter pelo menos 6 caracteres';
    }

    if (empty($errors)) {
        // Gerar credenciais do banco
        $nome_usuario_banco = 'u_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $senha_banco = bin2hex(random_bytes(8));
        $senha_usuario_hash = password_hash($senha_usuario, PASSWORD_BCRYPT);

        // Inserir usuário
        $stmt = $conexao->prepare("INSERT INTO usuarios (nome_completo, cpf, email, telefone, nome_usuario_banco, senha_banco, senha_usuario, data_criacao, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
        $stmt->bind_param('sssssss', $nome_completo, $cpf, $email, $telefone, $nome_usuario_banco, $senha_banco, $senha_usuario_hash);

        if ($stmt->execute()) {
            $user_id = $conexao->insert_id;
            $stmt->close();

            // Redirecionar para dashboard com sucesso
            header('Location: pagina_adm.php?success=user_created');
            exit;
        } else {
            $errors[] = 'Erro ao cadastrar usuário: ' . $conexao->error;
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
<title>Cadastrar Usuário - Plataforma X</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>➕ Cadastrar Novo Usuário</h1>
            <p>Plataforma X - Sistema de Gerenciamento</p>
        </div>

        <div class="alert-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error show">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <form method="post" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome_completo">👤 Nome Completo *</label>
                        <input type="text" id="nome_completo" name="nome_completo" value="<?php echo htmlspecialchars($_POST['nome_completo'] ?? ''); ?>" required placeholder="Digite o nome completo">
                    </div>
                    <div class="form-group">
                        <label for="cpf">🆔 CPF</label>
                        <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>" placeholder="000.000.000-00">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">📧 Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="usuario@exemplo.com">
                    </div>
                    <div class="form-group">
                        <label for="telefone">📞 Telefone</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>" placeholder="(11) 99999-9999">
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha_usuario">🔑 Senha do Usuário *</label>
                    <input type="password" id="senha_usuario" name="senha_usuario" required placeholder="Senha do usuário (min. 6 caracteres)">
                </div>

                <div class="form-group">
                    <small style="color:#666;">ℹ️ As credenciais do banco de dados serão geradas automaticamente pelo sistema.</small>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">✅ Cadastrar Usuário</button>
                    <a href="pagina_adm.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                e.target.value = value;
            }
        });

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                } else {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                }
                e.target.value = value;
            }
        });
    </script>
</body>
</html>
