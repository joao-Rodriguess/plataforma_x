
<?php
/**
 * Script para criar usuário no MySQL
 * VERSÃO NÃO-INTERATIVA - Para uso com scripts batch
 */

class CreateDatabaseUser {
    private $rootUser = 'root';
    private $rootPassword = '';
    private $host = 'localhost';
    private $configFile;
    
    public function __construct() {
        $this->configFile = __DIR__ . '/config.php';
    }
    
    /**
     * Lê as credenciais do arquivo de configuração
     */
    private function getCredentialsFromConfig() {
        if (!file_exists($this->configFile)) {
            throw new Exception("Arquivo de configuração não encontrado. Execute setup.php primeiro.");
        }
        
        // Ler arquivo sem executar (para evitar conexão)
        $content = file_get_contents($this->configFile);
        
        // Extrair DB_USER
        if (preg_match("/define\('DB_USER',\s*'([^']+)'\)/", $content, $matches)) {
            $dbUser = $matches[1];
        } else {
            throw new Exception("Não foi possível extrair o usuário do banco.");
        }
        
        // Extrair DB_PASSWORD
        if (preg_match("/define\('DB_PASSWORD',\s*'([^']+)'\)/", $content, $matches)) {
            $dbPassword = $matches[1];
        } else {
            throw new Exception("Não foi possível extrair a senha do banco.");
        }
        
        return [
            'user' => $dbUser,
            'password' => $dbPassword
        ];
    }
    
    /**
     * Conecta ao MySQL com credenciais root
     */
    private function connectAsRoot() {
        try {
            $conn = @new mysqli(
                $this->host,
                $this->rootUser,
                $this->rootPassword
            );
            
            if ($conn->connect_error) {
                throw new Exception($conn->connect_error);
            }
            
            return $conn;
        } catch (Exception $e) {
            throw new Exception("Erro ao conectar como root: " . $e->getMessage());
        }
    }
    
    /**
     * Executa o SQL para criar o usuário
     */
    public function createUser() {
        try {
            echo "\n";
            echo "╔════════════════════════════════════════════════════════╗\n";
            echo "║   CRIANDO USUÁRIO DO BANCO DE DADOS                    ║\n";
            echo "╚════════════════════════════════════════════════════════╝\n";
            echo "\n";
            
            // Obter credenciais
            echo "[1/3] Lendo credenciais geradas...\n";
            $creds = $this->getCredentialsFromConfig();
            echo "   ✓ Usuário: " . $creds['user'] . "\n";
            echo "   ✓ Senha: " . str_repeat('*', strlen($creds['password'])) . "\n";
            echo "\n";
            
            // Conectar como root
            echo "[2/3] Conectando ao MySQL como root...\n";
            $conn = $this->connectAsRoot();
            echo "   ✓ Conectado com sucesso\n";
            echo "\n";
            
            // Criar usuário
            echo "[3/3] Criando usuário do banco de dados...\n";
            
            // Escapar valores para SQL
            $user = $conn->real_escape_string($creds['user']);
            $password = $conn->real_escape_string($creds['password']);
            
            // Criar usuário
            $sqlCreateUser = "CREATE USER IF NOT EXISTS '$user'@'$this->host' IDENTIFIED BY '$password'";
            
            if (!$conn->query($sqlCreateUser)) {
                throw new Exception("Erro ao criar usuário: " . $conn->error);
            }
            echo "   ✓ Usuário criado\n";
            
            // Criar banco de dados
            if (!$conn->query("CREATE DATABASE IF NOT EXISTS plataforma_x CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                throw new Exception("Erro ao criar banco: " . $conn->error);
            }
            echo "   ✓ Banco de dados criado\n";
            
            // Conceder privilégios
            $sqlGrants = "GRANT ALL PRIVILEGES ON plataforma_x.* TO '$user'@'$this->host'";
            
            if (!$conn->query($sqlGrants)) {
                throw new Exception("Erro ao conceder privilégios: " . $conn->error);
            }
            echo "   ✓ Privilégios concedidos\n";
            
            // Aplicar mudanças
            if (!$conn->query("FLUSH PRIVILEGES")) {
                throw new Exception("Erro ao aplicar privilégios: " . $conn->error);
            }
            echo "   ✓ Privilégios aplicados\n";
            
            $conn->close();
            
            echo "\n";
            echo "╔════════════════════════════════════════════════════════╗\n";
            echo "║   ✓ USUÁRIO CRIADO COM SUCESSO!                       ║\n";
            echo "╚════════════════════════════════════════════════════════╝\n";
            echo "\n";
            
            echo "PRÓXIMO PASSO:\n";
            echo "Execute: php install_database.php\n";
            echo "Para criar as tabelas do banco de dados\n";
            echo "\n";
            
            return true;
            
        } catch (Exception $e) {
            echo "\n";
            echo "╔════════════════════════════════════════════════════════╗\n";
            echo "║   ✗ ERRO NA CRIAÇÃO DO USUÁRIO                        ║\n";
            echo "╚════════════════════════════════════════════════════════╝\n";
            echo "\n";
            echo "Erro: " . $e->getMessage() . "\n";
            echo "\n";
            
            return false;
        }
    }
}

// Executar se chamado diretamente
if (php_sapi_name() === 'cli') {
    $creator = new CreateDatabaseUser();
    $result = $creator->createUser();
    exit($result ? 0 : 1);
}
?>
