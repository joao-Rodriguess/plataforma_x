<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';
require_once 'config-advanced.php';

// Verificar autenticação para operações sensíveis
function verificarAutenticacaoAPI() {
    session_start();

    // Verificar se é uma requisição autenticada
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
        jsonResponse(false, null, 'Acesso não autorizado', 401);
    }

    // Rate limiting
    if (!verificarRateLimit($_SERVER['REMOTE_ADDR'])) {
        jsonResponse(false, null, 'Muitas requisições. Tente novamente mais tarde.', 429);
    }
}

$metodo = $_SERVER['REQUEST_METHOD'];
$acao = isset($_GET['acao']) ? $_GET['acao'] : '';

// Tratamento de CORS preflight
if ($metodo === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure JSON response for all errors
function sendJsonError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['sucesso' => false, 'erro' => $message]);
    exit();
}

try {
    switch ($metodo) {
        case 'GET':
            if ($acao === 'listar') {
                listarUsuarios();
            } elseif ($acao === 'obter' && isset($_GET['id'])) {
                obterUsuario($_GET['id']);
            } else {
                http_response_code(400);
                echo json_encode(['erro' => 'Ação não especificada']);
            }
            break;

        case 'POST':
            verificarAutenticacaoAPI();
            criarUsuario();
            break;

        case 'PUT':
            verificarAutenticacaoAPI();
            atualizarUsuario();
            break;

        case 'DELETE':
            verificarAutenticacaoAPI();
            if (isset($_GET['id'])) {
                deletarUsuario($_GET['id']);
            } else {
                http_response_code(400);
                echo json_encode(['erro' => 'ID não especificado']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}

function listarUsuarios() {
    global $conexao;

    $sql = "SELECT id, nome_completo, cpf, email, telefone, senha_usuario, data_criacao, ativo FROM usuarios ORDER BY data_criacao DESC";
    $resultado = $conexao->query($sql);

    if ($resultado) {
        $usuarios = [];
        while ($linha = $resultado->fetch_assoc()) {
            $usuarios[] = $linha;
        }
        echo json_encode(['sucesso' => true, 'dados' => $usuarios]);
    } else {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao listar usuários: ' . $conexao->error]);
    }
}

function obterUsuario($id) {
    global $conexao;
    
    $id = intval($id);
    $sql = "SELECT id, nome_completo, cpf, email, telefone, nome_usuario_banco, data_criacao, ativo FROM usuarios WHERE id = ?";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        echo json_encode(['sucesso' => true, 'dados' => $usuario]);
    } else {
        http_response_code(404);
        echo json_encode(['erro' => 'Usuário não encontrado']);
    }
    $stmt->close();
}

function criarUsuario() {
    global $conexao;
    
    $dados = json_decode(file_get_contents("php://input"), true);

    // Validar campos obrigatórios (DB credentials are generated server-side)
    if (!isset($dados['nome_completo']) || !isset($dados['cpf']) || !isset($dados['email']) || !isset($dados['senha_usuario'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Campos obrigatórios faltando']);
        return;
    }

    // Sanitizar entradas
    $nome_completo = $conexao->real_escape_string($dados['nome_completo']);
    $cpf = $conexao->real_escape_string($dados['cpf']);
    $email = $conexao->real_escape_string($dados['email']);
    $telefone = isset($dados['telefone']) ? $conexao->real_escape_string($dados['telefone']) : '';
    // Gerar credenciais do banco automaticamente
    $nome_usuario_banco = 'u_' . substr(bin2hex(random_bytes(4)), 0, 8);
    // Gera senha segura
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $senha_banco = '';
    for ($i = 0; $i < 16; $i++) {
        $senha_banco .= $chars[random_int(0, strlen($chars) - 1)];
    }
    $nome_usuario_banco = $conexao->real_escape_string($nome_usuario_banco);
    $senha_banco_escaped = $conexao->real_escape_string($senha_banco);
    $senha_usuario = password_hash($dados['senha_usuario'], PASSWORD_BCRYPT);

    // Validar CPF simples
    if (!validarCPF($cpf)) {
        http_response_code(400);
        echo json_encode(['erro' => 'CPF inválido']);
        return;
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Email inválido']);
        return;
    }

    $sql = "INSERT INTO usuarios (nome_completo, cpf, email, telefone, nome_usuario_banco, senha_banco, senha_usuario) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao preparar statement: ' . $conexao->error]);
        return;
    }

    $stmt->bind_param("sssssss", $nome_completo, $cpf, $email, $telefone, $nome_usuario_banco, $senha_banco_escaped, $senha_usuario);

    if ($stmt->execute()) {
        $novo_id = $conexao->insert_id;
        http_response_code(201);
        // Não retornar senha do banco ao cliente padrão; only admin can view. Return masked info.
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário criado com sucesso', 'id' => $novo_id, 'db_user' => $nome_usuario_banco, 'db_password_masked' => str_repeat('*', 8)]);
    } else {
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao criar usuário: ' . $stmt->error]);
    }
    $stmt->close();
}

function atualizarUsuario() {
    global $conexao;
    
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID não especificado']);
        return;
    }

    $id = intval($dados['id']);
    
    // Montar query dinâmica com apenas os campos fornecidos
    $campos = [];
    $valores = [];
    $tipos = '';

    if (isset($dados['nome_completo'])) {
        $campos[] = "nome_completo = ?";
        $valores[] = $conexao->real_escape_string($dados['nome_completo']);
        $tipos .= 's';
    }

    if (isset($dados['email'])) {
        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Email inválido']);
            return;
        }
        $campos[] = "email = ?";
        $valores[] = $conexao->real_escape_string($dados['email']);
        $tipos .= 's';
    }

    if (isset($dados['telefone'])) {
        $campos[] = "telefone = ?";
        $valores[] = $conexao->real_escape_string($dados['telefone']);
        $tipos .= 's';
    }

    if (isset($dados['senha_usuario'])) {
        $campos[] = "senha_usuario = ?";
        $valores[] = password_hash($dados['senha_usuario'], PASSWORD_BCRYPT);
        $tipos .= 's';
    }

    if (empty($campos)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nenhum campo para atualizar']);
        return;
    }

    $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = ?";
    $valores[] = $id;
    $tipos .= 'i';

    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao preparar statement: ' . $conexao->error]);
        return;
    }

    $stmt->bind_param($tipos, ...$valores);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário atualizado com sucesso']);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Usuário não encontrado']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao atualizar usuário: ' . $stmt->error]);
    }
    $stmt->close();
}

function deletarUsuario($id) {
    global $conexao;
    
    $id = intval($id);
    $sql = "DELETE FROM usuarios WHERE id = ?";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário deletado com sucesso']);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Usuário não encontrado']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao deletar usuário: ' . $stmt->error]);
    }
    $stmt->close();
}

function validarCPF($cpf) {
    // Remove caracteres especiais
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);

    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se não é uma sequência repetida
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Calcula o primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;

    // Calcula o segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;

    // Verifica se os dígitos correspondem
    return ($digito1 == intval($cpf[9]) && $digito2 == intval($cpf[10]));
}
?>
