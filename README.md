# Plataforma X - Sistema de Gerenciamento de Usuários

## 📋 Descrição

Aplicação web completa para cadastro, alteração, exclusão e consulta de usuários. Desenvolvida com PHP, MySQL, HTML5, CSS3 e JavaScript.

## 🚀 Funcionalidades

- ✅ Criar novo usuário
- ✅ Listar todos os usuários
- ✅ Editar dados do usuário
- ✅ Deletar usuário
- ✅ Validação de CPF
- ✅ Validação de Email
- ✅ Senhas criptografadas com bcrypt
- ✅ Interface responsiva e moderna
- ✅ API RESTful

## 📁 Estrutura do Projeto

```
plataforma_x/
├── index.html              # Página principal
├── css/
│   └── style.css          # Estilos da aplicação
├── js/
│   └── script.js          # Lógica do frontend
├── api/
│   ├── config.php         # Configuração do banco de dados
│   └── usuarios.php       # API para gerenciar usuários
└── database/
    └── database.sql       # Script para criar banco de dados
```

## 🔧 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor Apache (XAMPP, WAMP, etc.)
- Navegador web moderno

## 📦 Instalação

### 1. Copiar arquivos para o servidor web

Copie toda a pasta `plataforma_x` para o diretório raiz do seu servidor web:
- **XAMPP**: `C:\xampp\htdocs\plataforma_x`
- **WAMP**: `C:\wamp\www\plataforma_x`
- **MAMP**: `/Applications/MAMP/htdocs/plataforma_x`

### 2. Criar banco de dados

1. Abra o phpMyAdmin (geralmente em `http://localhost/phpmyadmin`)
2. Selecione a aba **SQL**
3. Copie todo o conteúdo do arquivo `database/database.sql`
4. Cole no editor SQL do phpMyAdmin
5. Clique em **Executar**

**Ou via linha de comando:**

```bash
mysql -u root -p < database/database.sql
```

### 3. Configurar conexão com banco de dados (OPCIONAL)

Se você alterar as credenciais do banco, atualize o arquivo `api/config.php`:

```php
define('DB_HOST', 'localhost');      // Host do MySQL
define('DB_USER', 'usuario_plataforma'); // Usuário do MySQL
define('DB_PASSWORD', 'senha_segura_123'); // Senha do MySQL
define('DB_NAME', 'plataforma_x');   // Nome do banco
```

### 4. Acessar a aplicação

Abra seu navegador e acesse:
```
http://localhost/plataforma_x/
```

## 🔐 Campos do Usuário

- **Nome Completo**: Nome completo do usuário (obrigatório)
- **CPF**: Cadastro de Pessoa Física (obrigatório, validação automática)
- **Email**: Endereço de email (obrigatório, validação automática)
- **Telefone**: Número de telefone (opcional)
- **Nome de Usuário do Banco**: Username para acesso ao banco (obrigatório)
- **Senha do Banco**: Senha de acesso ao banco (obrigatório)
- **Senha do Usuário**: Senha do usuário (obrigatório, mínimo 6 caracteres)

## 📡 API Endpoints

### Listar Usuários
```
GET /api/usuarios.php?acao=listar
```

### Obter Usuário Específico
```
GET /api/usuarios.php?acao=obter&id=1
```

### Criar Usuário
```
POST /api/usuarios.php
Content-Type: application/json

{
  "nome_completo": "João Silva",
  "cpf": "123.456.789-00",
  "email": "joao@exemplo.com",
  "telefone": "(11) 99999-9999",
  "nome_usuario_banco": "joao_silva",
  "senha_banco": "senha123",
  "senha_usuario": "usuario123"
}
```

### Atualizar Usuário
```
PUT /api/usuarios.php
Content-Type: application/json

{
  "id": 1,
  "nome_completo": "João Silva Updated",
  "email": "joao.novo@exemplo.com",
  "telefone": "(11) 98888-8888"
}
```

### Deletar Usuário
```
DELETE /api/usuarios.php?id=1
```

## ✅ Validações

- **CPF**: Validação de dígitos verificadores
- **Email**: Formato válido de email
- **Senha do Usuário**: Mínimo 6 caracteres
- **Campos Obrigatórios**: Validação no frontend e backend

## 🔒 Segurança

- Senhas do usuário criptografadas com bcrypt
- Prepared statements para prevenir SQL Injection
- Sanitização de inputs
- CORS habilitado para requisições cross-origin
- Validação de entrada no frontend e backend

## 🎨 Interface

- Design responsivo (desktop, tablet, mobile)
- Tema moderno com gradientes
- Modal para edição de usuários
- Abas para navegação
- Alertas visuais de sucesso/erro

## 🐛 Troubleshooting

### Erro: "Falha na conexão com o banco de dados"
- Verifique se o MySQL está rodando
- Confira as credenciais em `api/config.php`
- Certifique-se de que o banco de dados foi criado

### Erro: "CORS policy"
- O arquivo `usuarios.php` já configura os headers de CORS
- Se persistir, verifique as configurações do seu servidor Apache

### Página em branco
- Verifique se os arquivos `css/style.css` e `js/script.js` estão acessíveis
- Abra o console do navegador (F12) para ver mensagens de erro

## 📝 Licença

Este projeto é fornecido como está para fins educacionais.

## 👨‍💻 Autor

Desenvolvido conforme os requisitos especificados.

---

**Versão**: 1.0  
**Última atualização**: 12 de Novembro de 2025
