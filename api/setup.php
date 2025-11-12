<?php
/**
 * Script de Setup - Gera credenciais do banco automaticamente
 * Execute uma única vez no início da instalação
 */

class DatabaseSetup {
    private $configFile = __DIR__ . '/config.php';
    private $configAdvancedFile = __DIR__ . '/config-advanced.php';
    
    /**
     * Gera uma string aleatória segura
     */
    public function generateSecurePassword($length = 16) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $password;
    }
    
    /**
     * Gera um nome de usuário único
     */
    public function generateUsername($prefix = 'user_') {
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        return $prefix . substr($random, 0, 8);
    }
    
    /**
     * Cria o arquivo de configuração com credenciais geradas
     */
    public function createConfigFile($dbUser, $dbPassword, $dbName = 'plataforma_x', $dbHost = 'localhost') {
        $dataAtual = date('Y-m-d H:i:s');
        $configContent = <<<PHP
<?php
// Configuração do banco de dados (GERADO AUTOMATICAMENTE)
// Data de criação: {$dataAtual}

define('DB_HOST', '{$dbHost}');
define('DB_USER', '{$dbUser}');
define('DB_PASSWORD', '{$dbPassword}');
define('DB_NAME', '{$dbName}');

// Criar conexão
\$conexao = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Verificar conexão
if (\$conexao->connect_error) {
    die(json_encode(['erro' => 'Falha na conexão: ' . \$conexao->connect_error]));
}

// Definir charset
\$conexao->set_charset("utf8");
?>
PHP;
        
        file_put_contents($this->configFile, $configContent);
        return true;
    }
    
    /**
     * Cria um arquivo com as credenciais para referência (protegido)
     */
    public function createCredentialsFile($dbUser, $dbPassword) {
        $credentialsFile = __DIR__ . '/../.credentials.txt';
        $credentialsContent = <<<TXT
CREDENCIAIS DO BANCO DE DADOS - PLATAFORMA X
Gerado em: " . date('Y-m-d H:i:s') . "

Usuário: $dbUser
Senha: $dbPassword

⚠️  IMPORTANTE:
- Guarde essas credenciais em local seguro
- Não compartilhe este arquivo
- Este arquivo deveria estar em .gitignore
- Delete-o após memorizar as credenciais (opcional)

Instruções de acesso:
mysql -u $dbUser -p
(quando solicitado, digite a senha acima)

TXT;
        
        file_put_contents($credentialsFile, $credentialsContent);
        chmod($credentialsFile, 0600); // Apenas leitura do proprietário
        
        return $credentialsFile;
    }
    
    /**
     * Executa o setup completo
     */
    public function setup() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║   SETUP AUTOMÁTICO - PLATAFORMA X                     ║\n";
        echo "║   Geração de Credenciais do Banco de Dados            ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        // Gerar credenciais
        $dbUser = $this->generateUsername('plat_');
        $dbPassword = $this->generateSecurePassword(20);
        
        echo "[1/3] Gerando credenciais...\n";
        echo "   ✓ Usuário: $dbUser\n";
        echo "   ✓ Senha: " . str_repeat('*', strlen($dbPassword)) . "\n";
        echo "\n";
        
        // Criar arquivo de configuração
        echo "[2/3] Criando arquivo de configuração...\n";
        if ($this->createConfigFile($dbUser, $dbPassword)) {
            echo "   ✓ Arquivo api/config.php criado\n";
        } else {
            echo "   ✗ Erro ao criar arquivo de configuração\n";
            return false;
        }
        echo "\n";
        
        // Criar arquivo de credenciais
        echo "[3/3] Salvando credenciais para referência...\n";
        $credFile = $this->createCredentialsFile($dbUser, $dbPassword);
        echo "   ✓ Credenciais salvas em: .credentials.txt\n";
        echo "\n";
        
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║   ✓ SETUP CONCLUÍDO COM SUCESSO!                      ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        echo "PRÓXIMOS PASSOS:\n";
        echo "1. Execute: php api/create_user.php\n";
        echo "   Para criar o usuário no MySQL\n";
        echo "\n";
        echo "2. Depois execute: php install_database.php\n";
        echo "   Para criar o banco de dados e tabelas\n";
        echo "\n";
        echo "3. Delete .credentials.txt após guardar as credenciais\n";
        echo "\n";
        
        return true;
    }
}

// Executar se chamado diretamente
if (php_sapi_name() === 'cli' || (isset($_GET['action']) && $_GET['action'] === 'setup')) {
    $setup = new DatabaseSetup();
    $setup->setup();
}
?>
