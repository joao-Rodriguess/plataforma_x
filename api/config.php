<?php
/**
 * Configuração Principal do Sistema
 * Carrega variáveis de ambiente e configura conexão com banco
 */

// Carregar variáveis de ambiente
function loadEnv($path) {
    if (!file_exists($path)) {
        die(json_encode(['erro' => 'Arquivo .env não encontrado. Copie .env.example para .env e configure as credenciais.']));
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Carregar .env
loadEnv(__DIR__ . '/../.env');

// Configurações do Banco de Dados
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'plataforma_x');

// Configurações Administrativas
define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: 'admin123');

// Configurações Root MySQL
define('DB_ROOT_USER', getenv('DB_ROOT_USER') ?: 'root');
define('DB_ROOT_PASSWORD', getenv('DB_ROOT_PASSWORD') ?: '');

// Ambiente
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// Configurações de Segurança
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600);
define('RATE_LIMIT_ENABLED', getenv('RATE_LIMIT_ENABLED') === 'true');
define('RATE_LIMIT_REQUESTS', getenv('RATE_LIMIT_REQUESTS') ?: 100);
define('RATE_LIMIT_WINDOW', getenv('RATE_LIMIT_WINDOW') ?: 3600);

// Configurações de Logs
define('ERROR_LOG_PATH', getenv('ERROR_LOG_PATH') ?: __DIR__ . '/../logs/error.log');

// Configurações de Cache
define('CACHE_ENABLED', getenv('CACHE_ENABLED') === 'true');
define('CACHE_TTL', getenv('CACHE_TTL') ?: 3600);

// Configurações de Upload
define('UPLOAD_MAX_SIZE', getenv('UPLOAD_MAX_SIZE') ?: '50M');
define('POST_MAX_SIZE', getenv('POST_MAX_SIZE') ?: '50M');

// Configurar PHP baseado no ambiente
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Configurações de sessão
ini_set('session.lifetime', SESSION_LIFETIME);
ini_set('session.secure', ENVIRONMENT === 'production');
ini_set('session.httponly', true);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Criar conexão com retry
function conectarBanco($maxTentativas = 3) {
    for ($i = 0; $i < $maxTentativas; $i++) {
        $conexao = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if (!$conexao->connect_error) {
            $conexao->set_charset("utf8");
            return $conexao;
        }

        if ($i < $maxTentativas - 1) {
            sleep(2);
        }
    }

    die(json_encode(['erro' => 'Falha na conexão com o banco de dados após ' . $maxTentativas . ' tentativas']));
}

// Conexão global
$conexao = conectarBanco();

// Criar diretório de logs se não existir
if (!is_dir(dirname(ERROR_LOG_PATH))) {
    mkdir(dirname(ERROR_LOG_PATH), 0755, true);
}

// Função de log de erro
function logError($mensagem, $arquivo = '') {
    $timestamp = date('Y-m-d H:i:s');
    $linha = "[{$timestamp}] {$mensagem}";

    if ($arquivo) {
        $linha .= " - {$arquivo}";
    }

    error_log($linha . PHP_EOL, 3, ERROR_LOG_PATH);
}

// Função de resposta JSON padronizada
function jsonResponse($sucesso, $dados = null, $mensagem = '', $codigo = 200) {
    http_response_code($codigo);

    $resposta = ['sucesso' => $sucesso];

    if ($mensagem) {
        $resposta['mensagem'] = $mensagem;
    }

    if ($dados !== null) {
        if ($sucesso) {
            $resposta['dados'] = $dados;
        } else {
            $resposta['erro'] = $dados;
        }
    }

    echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Rate limiting básico
function verificarRateLimit($chave) {
    if (!RATE_LIMIT_ENABLED) {
        return true;
    }

    session_start();

    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    $timestamp = time();

    if (!isset($_SESSION['rate_limit'][$chave])) {
        $_SESSION['rate_limit'][$chave] = ['count' => 1, 'window_start' => $timestamp];
        return true;
    }

    $limite = $_SESSION['rate_limit'][$chave];
    $tempoDecorrido = $timestamp - $limite['window_start'];

    if ($tempoDecorrido >= RATE_LIMIT_WINDOW) {
        $_SESSION['rate_limit'][$chave] = ['count' => 1, 'window_start' => $timestamp];
        return true;
    }

    $_SESSION['rate_limit'][$chave]['count']++;

    return $_SESSION['rate_limit'][$chave]['count'] <= RATE_LIMIT_REQUESTS;
}
?>
