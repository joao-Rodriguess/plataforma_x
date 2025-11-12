<?php
/**
 * Script para instalar o banco de dados com tabelas
 * Executa o arquivo database.sql
 */

class InstallDatabase {
    private $configFile;
    
    public function __construct() {
        $this->configFile = __DIR__ . '/api/config.php';
    }
    
    /**
     * Lê as credenciais do arquivo de configuração
     */
    private function getCredentials() {
        if (!file_exists($this->configFile)) {
            throw new Exception("Arquivo de configuração não encontrado. Execute api/setup.php primeiro.");
        }
        
        // Ler arquivo sem executar (para evitar conexão)
        $content = file_get_contents($this->configFile);
        
        // Extrair valores
        $credentials = [];
        
        if (preg_match("/define\('DB_HOST',\s*'([^']+)'\)/", $content, $matches)) {
            $credentials['host'] = $matches[1];
        }
        if (preg_match("/define\('DB_USER',\s*'([^']+)'\)/", $content, $matches)) {
            $credentials['user'] = $matches[1];
        }
        if (preg_match("/define\('DB_PASSWORD',\s*'([^']+)'\)/", $content, $matches)) {
            $credentials['password'] = $matches[1];
        }
        if (preg_match("/define\('DB_NAME',\s*'([^']+)'\)/", $content, $matches)) {
            $credentials['name'] = $matches[1];
        }
        
        if (empty($credentials['host']) || empty($credentials['user'])) {
            throw new Exception("Não foi possível ler as credenciais do banco.");
        }
        
        return $credentials;
    }
    
    /**
     * Executa o arquivo SQL
     */
    public function install() {
        try {
            echo "\n";
            echo "╔════════════════════════════════════════════════════════╗\n";
            echo "║   INSTALANDO BANCO DE DADOS                           ║\n";
            echo "╚════════════════════════════════════════════════════════╝\n";
            echo "\n";
            
            // Obter credenciais
            echo "[1/3] Lendo configurações...\n";
            $creds = $this->getCredentials();
            echo "   ✓ Host: " . $creds['host'] . "\n";
            echo "   ✓ User: " . $creds['user'] . "\n";
            echo "   ✓ Database: " . $creds['name'] . "\n";
            echo "\n";
            
            // Conectar
            echo "[2/3] Conectando ao banco de dados...\n";
            $conn = new mysqli(
                $creds['host'],
                $creds['user'],
                $creds['password'],
                $creds['name']
            );
            
            if ($conn->connect_error) {
                throw new Exception("Erro de conexão: " . $conn->connect_error);
            }
            echo "   ✓ Conectado com sucesso\n";
            echo "\n";
            
            // Executar SQL
            echo "[3/3] Executando scripts SQL...\n";
            
            $sqlFile = dirname(__DIR__) . '/plataforma_x/database/database.sql';
            if (!file_exists($sqlFile)) {
                // Tenta outro caminho
                $sqlFile = __DIR__ . '/database/database.sql';
            }
            if (!file_exists($sqlFile)) {
                throw new Exception("Arquivo database.sql não encontrado em: $sqlFile");
            }
            
            $sql = file_get_contents($sqlFile);
            
            // Executar múltiplos comandos SQL
            if ($conn->multi_query($sql)) {
                $commandCount = 0;
                do {
                    $result = $conn->store_result();
                    if ($result) {
                        $result->free();
                    }
                    $commandCount++;
                } while ($conn->next_result());
                
                echo "   ✓ $commandCount comandos SQL executados\n";
            } else {
                throw new Exception("Erro ao executar SQL: " . $conn->error);
            }
            
            $conn->close();
            
            echo "\n";
            echo "╔════════════════════════════════════════════════════════╗\n";
            echo "║   ✓ BANCO DE DADOS INSTALADO COM SUCESSO!             ║\n";
            echo "╚════════════════════════════════════════════════════════╝\n";
            echo "\n";
            
            echo "INSTALAÇÃO COMPLETA!\n";
            echo "Você pode acessar a aplicação agora.\n";
            echo "\n";
            
            return true;
            
        } catch (Exception $e) {
            echo "\n";
            echo "╔════════════════════════════════════════════════════════╗\n";
            echo "║   ✗ ERRO NA INSTALAÇÃO DO BANCO                       ║\n";
            echo "╚════════════════════════════════════════════════════════╝\n";
            echo "\n";
            echo "Erro: " . $e->getMessage() . "\n";
            echo "\n";
            
            if (php_sapi_name() === 'cli') {
                echo "Pressione ENTER para sair...\n";
                fgets(STDIN);
            }
            
            return false;
        }
    }
}

// Executar se chamado diretamente
if (php_sapi_name() === 'cli' || (isset($_GET['action']) && $_GET['action'] === 'install')) {
    $installer = new InstallDatabase();
    $installer->install();
}
?>
