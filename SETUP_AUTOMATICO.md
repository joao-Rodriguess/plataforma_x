# 🚀 SETUP AUTOMÁTICO - CREDENCIAIS DO BANCO DE DADOS

Este guia explica como o sistema agora gera automaticamente o usuário e a senha do banco de dados MySQL.

## 📋 O que foi implementado

✅ **setup.php** - Gera credenciais aleatórias e seguras  
✅ **create_user.php** - Cria o usuário no MySQL  
✅ **install_database.php** - Instala as tabelas do banco  

## 🔒 Segurança

As credenciais geradas são:
- **Usuário**: Nome único gerado aleatoriamente (ex: `plat_a1b2c3d4`)
- **Senha**: String de 20 caracteres com letras, números e símbolos especiais

## 📝 Passo a Passo de Instalação

### **Passo 1: Gerar Credenciais**

Execute no terminal/PowerShell:

```powershell
php api/setup.php
```

**Resultado esperado:**
```
╔════════════════════════════════════════════════════════╗
║   SETUP AUTOMÁTICO - PLATAFORMA X                     ║
║   Geração de Credenciais do Banco de Dados            ║
╚════════════════════════════════════════════════════════╝

[1/3] Gerando credenciais...
   ✓ Usuário: plat_a1b2c3d4
   ✓ Senha: **********************

[2/3] Criando arquivo de configuração...
   ✓ Arquivo api/config.php criado

[3/3] Salvando credenciais para referência...
   ✓ Credenciais salvas em: .credentials.txt
```

**O que acontece:**
- Arquivo `api/config.php` é criado/atualizado com credenciais geradas
- Arquivo `.credentials.txt` é criado com backup das credenciais (protegido, modo 0600)

### **Passo 2: Criar Usuário no MySQL**

Execute no terminal/PowerShell:

```powershell
php api/create_user.php
```

**O script pedirá:**
```
Usuário ROOT MySQL [padrão: root]: (deixe em branco ou digite 'root')
Senha ROOT MySQL [deixe em branco se não houver]: (se não tiver senha, apenas pressione ENTER)
```

**Resultado esperado:**
```
╔════════════════════════════════════════════════════════╗
║   CRIANDO USUÁRIO DO BANCO DE DADOS                    ║
╚════════════════════════════════════════════════════════╝

[1/3] Lendo credenciais geradas...
   ✓ Usuário: plat_a1b2c3d4
   ✓ Senha: **********************

[2/3] Conectando ao MySQL como root...
   ✓ Conectado com sucesso

[3/3] Criando usuário do banco de dados...
   ✓ Usuário criado
   ✓ Banco de dados criado
   ✓ Privilégios concedidos
   ✓ Privilégios aplicados

╔════════════════════════════════════════════════════════╗
║   ✓ USUÁRIO CRIADO COM SUCESSO!                       ║
╚════════════════════════════════════════════════════════╝
```

### **Passo 3: Instalar Tabelas do Banco**

Execute no terminal/PowerShell:

```powershell
php install_database.php
```

**Resultado esperado:**
```
╔════════════════════════════════════════════════════════╗
║   INSTALANDO BANCO DE DADOS                           ║
╚════════════════════════════════════════════════════════╝

[1/3] Lendo configurações...
   ✓ Host: localhost
   ✓ User: plat_a1b2c3d4
   ✓ Database: plataforma_x

[2/3] Conectando ao banco de dados...
   ✓ Conectado com sucesso

[3/3] Executando scripts SQL...
   ✓ 7 comandos SQL executados

╔════════════════════════════════════════════════════════╗
║   ✓ BANCO DE DADOS INSTALADO COM SUCESSO!             ║
╚════════════════════════════════════════════════════════╝
```

## 📌 Arquivos Criados/Modificados

### **Novos Arquivos:**
- `api/setup.php` - Gera credenciais e cria config.php
- `api/create_user.php` - Cria usuário no MySQL
- `.credentials.txt` - Backup das credenciais (DELETE após guardar!)

### **Arquivo Modificado:**
- `install_database.php` - Agora usa credenciais do config.php

### **Arquivo Original (não modificado, apenas referência):**
- `api/config.php` - Gerado automaticamente pelo setup.php

## 🔑 Onde estão minhas credenciais?

### **Opção 1: Arquivo de Configuração**
Abra `api/config.php`:
```php
define('DB_USER', 'plat_a1b2c3d4');      // ← Seu usuário
define('DB_PASSWORD', 'abc123!@#...');   // ← Sua senha
```

### **Opção 2: Arquivo de Backup (durante instalação)**
Se você salvou, abra `.credentials.txt`:
```
Usuário: plat_a1b2c3d4
Senha: abc123!@#...
```

## ✅ Checklist de Instalação

- [ ] **Passo 1:** Executou `php api/setup.php`?
- [ ] **Passo 2:** Executou `php api/create_user.php` com credenciais root?
- [ ] **Passo 3:** Executou `php install_database.php`?
- [ ] **Opcional:** Deletou arquivo `.credentials.txt` (se guardar as credenciais)?
- [ ] **Teste:** Acessou a aplicação em `http://localhost/plataforma_x`?

## 🐛 Troubleshooting

### Erro: "Arquivo de configuração não encontrado"
→ Execute `php api/setup.php` primeiro

### Erro: "Erro ao conectar como root"
→ Verifique credenciais root do MySQL
→ Se usar XAMPP, geralmente user=`root` e password vazia

### Erro: "Acesso negado para usuário"
→ Execute `php api/create_user.php` novamente
→ Verifique se MySQL está rodando (XAMPP: clique em "Start" no MySQL)

### Erro: "Banco não encontrado"
→ Execute `php api/create_user.php` primeiro (cria o banco)

## 🔐 Boas Práticas de Segurança

1. **Nunca compartilhe `api/config.php`** - Guarde em local seguro
2. **Guarde `.credentials.txt`** - Faça backup em password manager
3. **Delete `.credentials.txt`** - Após guardar as credenciais
4. **Em produção:**
   - Use variáveis de ambiente ao invés de arquivo
   - Implemente HTTPS
   - Use senhas mais complexas
   - Restrinja privilégios do usuário MySQL

## 🚀 Próximos Passos

Após a instalação completa:

1. Acesse: `http://localhost/plataforma_x`
2. Crie sua primeira conta
3. Comece a usar a plataforma!

## 📞 Suporte

Se tiver dúvidas:
- Verifique este arquivo: `SETUP_AUTOMATICO.md`
- Consulte: `DOCUMENTACAO_TECNICA.md`
- Leia: `FAQ.md`

---

**Data de criação:** Novembro 2025  
**Versão:** 1.0
