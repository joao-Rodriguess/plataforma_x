<?php
/**
 * Arquivo de Configuração Avançada (OPCIONAL)
 * 
 * Este arquivo contém configurações opcionais para melhorar
 * o funcionamento da aplicação em diferentes ambientes.
 * 
 * Copie para api/config-advanced.php e customize conforme necessário.
 */

// ===================================================================
// 1. AMBIENTE (development, testing, production)
// ===================================================================
define('ENVIRONMENT', 'development'); // Alterar para 'production' em produção

// ===================================================================
// 2. LOG DE ERROS
// ===================================================================
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Caminho do arquivo de log
define('ERROR_LOG_PATH', __DIR__ . '/../logs/error.log');

// ===================================================================
// 3. RATE LIMITING (Proteção contra brute force)
// ===================================================================
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100);      // Máximo de requisições
define('RATE_LIMIT_WINDOW', 3600);        // Em 1 hora

// ===================================================================
// 4. PAGINAÇÃO
// ===================================================================
define('ITEMS_PER_PAGE', 10);             // Itens por página

// ===================================================================
// 5. CRIPTOGRAFIA
// ===================================================================
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 10);         // Custo do bcrypt (10-12)

// ===================================================================
// 6. SESSÃO
// ===================================================================
ini_set('session.lifetime', 3600);        // 1 hora
ini_set('session.secure', true);          // HTTPS only
ini_set('session.httponly', true);        // Sem acesso JavaScript

// ===================================================================
// 7. TIMEZONE
// ===================================================================
date_default_timezone_set('America/Sao_Paulo');

// ===================================================================
// 8. CACHE
// ===================================================================
define('CACHE_ENABLED', false);
define('CACHE_TTL', 3600);               // 1 hora

// ===================================================================
// 9. CARACTERES ESPECIAIS (ENCODING)
// ===================================================================
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');

// ===================================================================
// 10. FUNÇÃO DE LOG
// ===================================================================
function logError($mensagem, $arquivo = '') {
    if (!is_dir(dirname(ERROR_LOG_PATH))) {
        mkdir(dirname(ERROR_LOG_PATH), 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $linha = "[{$timestamp}] {$mensagem}";
    
    if ($arquivo) {
        $linha .= " - {$arquivo}";
    }
    
    error_log($linha . PHP_EOL, 3, ERROR_LOG_PATH);
}

// ===================================================================
// 11. FUNÇÃO DE RESPOSTA PADRONIZADA
// ===================================================================
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
    
    return json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// ===================================================================
// 12. FUNÇÃO DE RATE LIMITING
// ===================================================================
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
    $tempo_decorrido = $timestamp - $limite['window_start'];
    
    // Se passou a janela, reseta o contador
    if ($tempo_decorrido >= RATE_LIMIT_WINDOW) {
        $_SESSION['rate_limit'][$chave] = ['count' => 1, 'window_start' => $timestamp];
        return true;
    }
    
    // Incrementa o contador
    $_SESSION['rate_limit'][$chave]['count']++;
    
    // Verifica se ultrapassou o limite
    return $_SESSION['rate_limit'][$chave]['count'] <= RATE_LIMIT_REQUESTS;
}

// ===================================================================
// 13. FUNÇÃO DE SANITIZAÇÃO AVANÇADA
// ===================================================================
function sanitizarEntrada($entrada) {
    if (is_array($entrada)) {
        return array_map('sanitizarEntrada', $entrada);
    }
    
    // Remove espaços em branco no início e fim
    $entrada = trim($entrada);
    
    // Remove tags HTML
    $entrada = strip_tags($entrada);
    
    // Escapa para HTML
    $entrada = htmlspecialchars($entrada, ENT_QUOTES, 'UTF-8');
    
    return $entrada;
}

// ===================================================================
// 14. FUNÇÃO DE BACKUP AUTOMÁTICO
// ===================================================================
function criarBackupAutomatico() {
    $timestamp = date('Y-m-d_H-i-s');
    $arquivo_backup = __DIR__ . "/../backups/backup_{$timestamp}.sql";
    
    if (!is_dir(dirname($arquivo_backup))) {
        mkdir(dirname($arquivo_backup), 0755, true);
    }
    
    // Nota: Requer mysqldump instalado
    $comando = sprintf(
        'mysqldump -u%s -p%s %s > %s',
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASSWORD),
        escapeshellarg(DB_NAME),
        escapeshellarg($arquivo_backup)
    );
    
    exec($comando, $output, $return_var);
    
    return $return_var === 0;
}

// ===================================================================
// 15. FUNÇÃO DE AUDITORIA
// ===================================================================
function registrarAuditoria($acao, $usuario_id, $descricao) {
    global $conexao;
    
    // Nota: Requer tabela auditoria
    $sql = "INSERT INTO auditoria (acao, usuario_id, descricao, ip, timestamp)
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conexao->prepare($sql);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    
    $stmt->bind_param("siss", $acao, $usuario_id, $descricao, $ip);
    $stmt->execute();
}

// ===================================================================
// 16. FUNÇÃO DE VALIDAÇÃO AVANÇADA
// ===================================================================
function validarDados($dados, $regras) {
    $erros = [];
    
    foreach ($regras as $campo => $validacoes) {
        $valor = $dados[$campo] ?? null;
        
        foreach ($validacoes as $validacao) {
            switch ($validacao) {
                case 'required':
                    if (empty($valor)) {
                        $erros[$campo][] = "{$campo} é obrigatório";
                    }
                    break;
                    
                case 'email':
                    if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                        $erros[$campo][] = "Email inválido";
                    }
                    break;
                    
                case 'url':
                    if (!filter_var($valor, FILTER_VALIDATE_URL)) {
                        $erros[$campo][] = "URL inválida";
                    }
                    break;
            }
        }
    }
    
    return $erros;
}

// ===================================================================
// 17. CONEXÃO COM RETRY
// ===================================================================
function conectarComRetry($max_tentativas = 3) {
    for ($i = 0; $i < $max_tentativas; $i++) {
        $conexao = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        if (!$conexao->connect_error) {
            return $conexao;
        }
        
        if ($i < $max_tentativas - 1) {
            sleep(2); // Aguarda 2 segundos antes de tentar novamente
        }
    }
    
    throw new Exception('Falha ao conectar ao banco de dados após ' . $max_tentativas . ' tentativas');
}

// ===================================================================
// 18. GZIP COMPRESSION
// ===================================================================
if (extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
}

// ===================================================================
// 19. CONFIGURAÇÃO DE MEMÓRIA
// ===================================================================
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300); // 5 minutos

// ===================================================================
// 20. CONFIGURAÇÃO DE UPLOAD
// ===================================================================
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');

?>
