# Documentação Técnica - Plataforma X

## 1. Arquitetura do Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    Browser/Cliente                          │
│              (HTML + CSS + JavaScript)                      │
└─────────────────────┬───────────────────────────────────────┘
                      │ HTTP/AJAX
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                  Servidor Web (Apache)                      │
│              ┌──────────────────────────────┐              │
│              │   API REST (PHP)             │              │
│              │  - Validações               │              │
│              │  - Autenticação             │              │
│              │  - Operações CRUD           │              │
│              └──────────────────────────────┘              │
└─────────────────────┬───────────────────────────────────────┘
                      │ TCP Port 3306
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    MySQL Database                           │
│              - Tabela: usuarios                             │
│              - Permissões: select, insert, update, delete  │
└─────────────────────────────────────────────────────────────┘
```

## 2. Componentes Principais

### 2.1 Frontend (index.html)
- Estrutura HTML5 semântica
- Interface responsiva com CSS3
- Interatividade com JavaScript vanilla

**Seções principais:**
- Header com branding
- Abas de navegação
- Formulário de criação
- Tabela de listagem
- Modal de edição

### 2.2 Backend (PHP)

#### config.php
Arquivo de configuração centralizada para conexão com banco de dados.

```php
// Credenciais do banco
define('DB_HOST', 'localhost');
define('DB_USER', 'usuario_plataforma');
define('DB_PASSWORD', 'senha_segura_123');
define('DB_NAME', 'plataforma_x');
```

#### usuarios.php
API principal que gerencia todas as operações CRUD.

**Endpoints:**
- `GET /api/usuarios.php?acao=listar` - Retorna todos os usuários
- `GET /api/usuarios.php?acao=obter&id=X` - Retorna usuário específico
- `POST /api/usuarios.php` - Cria novo usuário
- `PUT /api/usuarios.php` - Atualiza usuário
- `DELETE /api/usuarios.php?id=X` - Deleta usuário

### 2.3 Banco de Dados (MySQL)

#### Tabela: usuarios
```sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_completo VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    nome_usuario_banco VARCHAR(50) NOT NULL UNIQUE,
    senha_banco VARCHAR(255) NOT NULL,
    senha_usuario VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);
```

**Campos:**
| Campo | Tipo | Restrição | Descrição |
|-------|------|-----------|-----------|
| id | INT | PRIMARY KEY | Identificador único |
| nome_completo | VARCHAR(150) | NOT NULL | Nome do usuário |
| cpf | VARCHAR(14) | UNIQUE | CPF (validado) |
| email | VARCHAR(120) | UNIQUE | Email (validado) |
| telefone | VARCHAR(20) | NULL | Número de telefone |
| nome_usuario_banco | VARCHAR(50) | UNIQUE | Username do banco |
| senha_banco | VARCHAR(255) | NOT NULL | Senha do banco |
| senha_usuario | VARCHAR(255) | NOT NULL | Senha do usuário (criptografada) |
| data_criacao | TIMESTAMP | DEFAULT | Data/hora de criação |
| ativo | BOOLEAN | DEFAULT TRUE | Status do usuário |

## 3. Fluxos de Dados

### 3.1 Criar Usuário
```
Cliente                        Servidor                    Banco de Dados
  │                              │                              │
  ├─ Preenche Formulário         │                              │
  ├─ Valida dados (JS)           │                              │
  ├─ POST /api/usuarios.php      │                              │
  │ + JSON com dados             ├─ Recebe dados               │
  │                              ├─ Valida CPF                 │
  │                              ├─ Valida Email               │
  │                              ├─ Criptografa senha          │
  │                              ├─ INSERT INTO usuarios       │
  │                              │─────────────────────────►   │
  │                              │                       Insere │
  │                              │  ◄─────────────────────     │
  │  ◄─ JSON com sucesso         │                              │
  │     (id do novo usuário)     │                              │
  ├─ Exibe mensagem de sucesso   │                              │
  └─ Limpa formulário            │                              │
```

### 3.2 Listar Usuários
```
Cliente                        Servidor                    Banco de Dados
  │                              │                              │
  ├─ Clica em "Lista"            │                              │
  ├─ GET /api/usuarios.php       │                              │
  │   ?acao=listar               ├─ SELECT * FROM usuarios     │
  │                              │─────────────────────────►   │
  │                              │                    Retorna  │
  │                              │  ◄─────────────────────     │
  │  ◄─ JSON com array           │                              │
  │     de usuários              │                              │
  ├─ Renderiza tabela            │                              │
  └─ Exibe dados                 │                              │
```

## 4. Segurança

### 4.1 Prevenção de SQL Injection
```php
// ✓ CORRETO - Usar prepared statements
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// ✗ ERRADO - Concatenação direta
$sql = "SELECT * FROM usuarios WHERE id = " . $_GET['id'];
```

### 4.2 Criptografia de Senhas
```php
// Ao criar usuário
$senha_criptografada = password_hash($senha, PASSWORD_BCRYPT);

// Ao verificar (se necessário)
if (password_verify($senha_plain, $senha_criptografada)) {
    // Senha correta
}
```

### 4.3 Validação de Entrada
```javascript
// Frontend (JavaScript)
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Backend (PHP)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Email inválido
}
```

### 4.4 CORS Headers
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
```

## 5. Validações

### 5.1 Validação de CPF
**Algoritmo:**
1. Remove caracteres especiais (., -)
2. Verifica se tem 11 dígitos
3. Verifica se não é uma sequência repetida (111.111.111-11)
4. Calcula primeiro dígito verificador
5. Calcula segundo dígito verificador
6. Compara com os dígitos informados

```
CPF válido: 123.456.789-00
CPF inválido: 111.111.111-11
CPF inválido: 123.456.789-99
```

### 5.2 Validação de Email
```regex
^[^\s@]+@[^\s@]+\.[^\s@]+$
```

Exemplos:
- ✓ usuario@exemplo.com
- ✗ usuario@exemplo
- ✗ usuario.exemplo.com
- ✗ usuario @exemplo.com

### 5.3 Validação de Senha
- Mínimo 6 caracteres
- Obrigatória para criação
- Criptografada com bcrypt (custo 10)

## 6. Tratamento de Erros

### Códigos HTTP Utilizados
```
200 OK              - Requisição bem-sucedida
201 Created         - Usuário criado
400 Bad Request     - Erro na validação
404 Not Found       - Usuário não encontrado
405 Method Allowed  - Método HTTP inválido
500 Server Error    - Erro no servidor
```

### Exemplo de Resposta de Erro
```json
{
  "erro": "CPF inválido"
}
```

## 7. Performance

### 7.1 Otimizações Implementadas
- Índice PRIMARY KEY em `id`
- Índices UNIQUE em `cpf` e `email`
- Uso de prepared statements
- Paginação opcional (futura)

### 7.2 Sugestões de Melhoria
```sql
-- Adicionar índices
CREATE INDEX idx_cpf ON usuarios(cpf);
CREATE INDEX idx_email ON usuarios(email);
CREATE INDEX idx_data_criacao ON usuarios(data_criacao);
```

## 8. Guia de Desenvolvimento

### 8.1 Adicionar Novo Campo

**Banco de dados (SQL):**
```sql
ALTER TABLE usuarios ADD COLUMN novo_campo VARCHAR(100);
```

**Backend (PHP usuarios.php):**
```php
// Em criarUsuario()
'novo_campo' => $conexao->real_escape_string($dados['novo_campo']),

// Em atualizarUsuario()
if (isset($dados['novo_campo'])) {
    $campos[] = "novo_campo = ?";
    $valores[] = $dados['novo_campo'];
}
```

**Frontend (HTML index.html):**
```html
<div class="form-group">
    <label for="novo_campo">Novo Campo</label>
    <input type="text" id="novo_campo" name="novo_campo">
</div>
```

**Frontend (JavaScript script.js):**
```javascript
// Em criarUsuario()
novo_campo: document.getElementById('novo_campo').value,
```

### 8.2 Estrutura de uma Requisição Completa

**Request:**
```http
POST /api/usuarios.php HTTP/1.1
Host: localhost/plataforma_x
Content-Type: application/json

{
  "nome_completo": "João Silva",
  "cpf": "123.456.789-00",
  "email": "joao@exemplo.com",
  "telefone": "(11) 99999-9999",
  "nome_usuario_banco": "joao_silva",
  "senha_banco": "senha_db",
  "senha_usuario": "senha_user"
}
```

**Response:**
```http
HTTP/1.1 201 Created
Content-Type: application/json

{
  "sucesso": true,
  "mensagem": "Usuário criado com sucesso",
  "id": 1
}
```

## 9. Debugging

### Ativar Log de Erros PHP
```php
// No arquivo config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### Verificar Conexão com Banco
```php
if ($conexao->connect_error) {
    die(json_encode([
        'erro' => 'Conexão falhou: ' . $conexao->connect_error
    ]));
}
```

### Console do Navegador
- Abrir com F12 ou Ctrl+Shift+I
- Aba "Console" para erros JavaScript
- Aba "Network" para ver requisições HTTP
- Aba "Application" > "Storage" para localStorage

## 10. Checklist de Deployment

- [ ] Banco de dados criado
- [ ] Permissões do usuário MySQL configuradas
- [ ] PHP 7.4+ instalado
- [ ] Apache com mod_rewrite ativado
- [ ] SSL/HTTPS configurado (recomendado)
- [ ] Backup do banco de dados
- [ ] Logs configurados
- [ ] Senhas padrão alteradas
- [ ] Testes funcionais realizados
- [ ] Documentação atualizada

---

**Versão**: 1.0  
**Última atualização**: 12 de Novembro de 2025
