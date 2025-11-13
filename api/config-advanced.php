<?php
/**
 * Arquivo de Configuração Avançada
 *
 * Este arquivo contém funções utilitárias avançadas para o sistema.
 * Agora integrado com o sistema de variáveis de ambiente.
 */

// ===================================================================
// 1. PAGINAÇÃO
// ===================================================================
define('ITEMS_PER_PAGE', 10);             // Itens por página

// ===================================================================
// 2. CRIPTOGRAFIA
// ===================================================================
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 10);         // Custo do bcrypt (10-12)

// ===================================================================
// 3. CARACTERES ESPECIAIS (ENCODING)
// ===================================================================
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');

// ===================================================================
// 4. FUNÇÃO DE SANITIZAÇÃO AVANÇADA
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
// 5. FUNÇÃO DE BACKUP AUTOMÁTICO
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
// 6. FUNÇÃO DE AUDITORIA
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
// 7. FUNÇÃO DE VALIDAÇÃO AVANÇADA
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
// 8. GZIP COMPRESSION
// ===================================================================
if (extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
}

// ===================================================================
// 9. CONFIGURAÇÃO DE MEMÓRIA
// ===================================================================
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300); // 5 minutos

// ===================================================================
// 10. CONFIGURAÇÃO DE UPLOAD
// ===================================================================
ini_set('upload_max_filesize', UPLOAD_MAX_SIZE);
ini_set('post_max_size', POST_MAX_SIZE);

// ===================================================================
// 11. FUNÇÃO DE AUTENTICAÇÃO DE ADMIN
// ===================================================================
function verificarAutenticacaoAdmin() {
    session_start();
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: login.php');
        exit;
    }
}

// ===================================================================
// 12. FUNÇÃO DE AUTENTICAÇÃO DE USUÁRIO
// ===================================================================
function verificarAutenticacaoUsuario() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.html');
        exit;
    }
}

// ===================================================================
// 13. FUNÇÃO PARA GERAR SENHA SEGURA
// ===================================================================
function gerarSenhaSegura($tamanho = 16) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $senha;
}

// ===================================================================
// 14. FUNÇÃO PARA GERAR USUÁRIO BANCO ÚNICO
// ===================================================================
function gerarUsuarioBanco() {
    return 'u_' . substr(bin2hex(random_bytes(4)), 0, 8);
}



// ===================================================================
// 16. FUNÇÃO DE CACHE SIMPLES
// ===================================================================
function getCache($chave) {
    if (!CACHE_ENABLED) {
        return false;
    }

    $arquivo_cache = __DIR__ . '/../cache/' . md5($chave) . '.cache';

    if (file_exists($arquivo_cache)) {
        $dados = unserialize(file_get_contents($arquivo_cache));
        if (time() - $dados['timestamp'] < CACHE_TTL) {
            return $dados['valor'];
        }
        unlink($arquivo_cache);
    }

    return false;
}

function setCache($chave, $valor) {
    if (!CACHE_ENABLED) {
        return false;
    }

    $arquivo_cache = __DIR__ . '/../cache/' . md5($chave) . '.cache';

    if (!is_dir(dirname($arquivo_cache))) {
        mkdir(dirname($arquivo_cache), 0755, true);
    }

    $dados = [
        'timestamp' => time(),
        'valor' => $valor
    ];

    return file_put_contents($arquivo_cache, serialize($dados)) !== false;
}





// ===================================================================
// 19. LOG DE SEGURANÇA
// ===================================================================
function logSecurity($message, $level = 'INFO') {
    $log_file = __DIR__ . '/../logs/security.log';
    $dir_logs = dirname($log_file);
    if (!is_dir($dir_logs)) {
        mkdir($dir_logs, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $log_entry = "[$timestamp] [$level] [$ip] $message" . PHP_EOL;

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?>
