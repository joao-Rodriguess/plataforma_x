# 🔒 Guia de Segurança - Plataforma X

## Avisos Importantes de Segurança

⚠️ **Este código foi desenvolvido para fins educacionais e demonstração.**
**Antes de usar em produção, implemente as medidas de segurança abaixo.**

---

## 1. Autenticação e Autorização

### ❌ PROBLEMA
Atualmente, qualquer pessoa pode acessar a API e gerenciar usuários.

### ✅ SOLUÇÃO
Implemente autenticação:

```php
// Exemplo: Autenticação básica com token
function verificarToken() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        die(json_encode(['erro' => 'Token não fornecido']));
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    
    // Validar token (usar JWT é recomendado)
    if (!validarToken($token)) {
        http_response_code(401);
        die(json_encode(['erro' => 'Token inválido']));
    }
}
```

### Bibliotecas Recomendadas
- **JWT (JSON Web Tokens)**: firebase/php-jwt
- **OAuth**: league/oauth2-server
- **Autenticação Social**: hybridauth

---

## 2. Senhas Seguras

### ❌ PROBLEMA
Usuários podem usar senhas frágeis.

### ✅ SOLUÇÃO
Enforce senha forte:

```php
function validarSenhaForte($senha) {
    $requisitos = [
        'minimo_caracteres' => strlen($senha) >= 12,
        'letras_maiusculas' => preg_match('/[A-Z]/', $senha),
        'letras_minusculas' => preg_match('/[a-z]/', $senha),
        'numeros' => preg_match('/[0-9]/', $senha),
        'caracteres_especiais' => preg_match('/[!@#$%^&*]/', $senha)
    ];
    
    return count(array_filter($requisitos)) === 5;
}
```

---

## 3. SQL Injection

### ✅ JÁ IMPLEMENTADO
O projeto usa **Prepared Statements**:

```php
// ✓ Correto
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

// ✗ Nunca fazer
$sql = "SELECT * FROM usuarios WHERE id = " . $_GET['id'];
```

### Verifique Regularmente
- Escaneie código com ferramentas como **OWASP ZAP**
- Use **SonarQube** para análise estática

---

## 4. Cross-Site Scripting (XSS)

### ❌ RISCO
JavaScript malicioso pode ser injetado.

### ✅ PREVENÇÃO
- Escape sempre dados no HTML:

```php
echo htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
```

- Use **Content Security Policy**:

```php
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
```

---

## 5. Cross-Site Request Forgery (CSRF)

### ✅ IMPLEMENTAR TOKENS CSRF
```php
// Gerar token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token inválido');
}
```

---

## 6. HTTPS/SSL

### ❌ RISCO CRÍTICO
Dados sendo transmitidos em HTTP puro.

### ✅ SOLUÇÃO
- Implementar SSL/TLS (certificado)
- Force HTTPS:

```php
if ($_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

---

## 7. Proteção de Dados Sensíveis

### ❌ NUNCA
```php
// ✗ Nunca fazer
echo "Senha: " . $senha_banco;
var_dump($dados_sensíveis);
```

### ✅ SEMPRE
- Não armazene senhas em log
- Não exiba senhas no JSON response
- Criptografe dados sensíveis em repouso

```php
// Usar criptografia para dados sensíveis
$chave = random_bytes(32);
$iv = openssl_random_pseudo_bytes(16);
$criptografado = openssl_encrypt(
    $dados_sensíveis,
    'AES-256-CBC',
    $chave,
    false,
    $iv
);
```

---

## 8. Validação de Entrada

### ✅ JÁ IMPLEMENTADO
- Validação de CPF
- Validação de Email
- Campos obrigatórios

### 🔧 MELHORIAS
```php
// Whitelist de valores permitidos
$telefonePadrao = preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $telefone);

// Limitar tamanho
if (strlen($nome_completo) > 150) {
    die('Nome muito longo');
}

// Validar apenas caracteres permitidos
if (!preg_match('/^[a-zA-Z0-9@._-]+$/', $email)) {
    die('Email contém caracteres inválidos');
}
```

---

## 9. Rate Limiting

### ❌ RISCO
Ataque de força bruta e DDoS.

### ✅ SOLUÇÃO
Implementar rate limiting:

```php
// Ver arquivo: api/config-advanced.php
if (!verificarRateLimit($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    die(json_encode(['erro' => 'Muitas requisições. Tente novamente mais tarde.']));
}
```

---

## 10. Logging e Auditoria

### ✅ IMPORTANTE
Registre todas as operações sensíveis:

```php
// Registrar auditoria
$sql = "INSERT INTO auditoria (acao, usuario_id, ip, timestamp)
        VALUES (?, ?, ?, NOW())";

// Registrar erros
error_log("Tentativa de acesso não autorizado de " . $_SERVER['REMOTE_ADDR']);
```

---

## 11. Atualização de Dependências

### 🔄 MANTER ATUALIZADO
- PHP: Manter na versão mais recente LTS (8.1+)
- MySQL: Versão 8.0+
- Bibliotecas: Executar `composer update` regularmente

### Verificar Vulnerabilidades
```bash
composer audit
```

---

## 12. Configuração do Servidor

### 📄 .htaccess
```apache
# Bloquear acesso a arquivos sensíveis
<FilesMatch "\.env|config\.php|\.sql$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Desativar listagem de diretórios
Options -Indexes

# Adicionar headers de segurança
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "DENY"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
```

### php.ini
```ini
# Desativar exposição de versão
expose_php = Off

# Limitar upload
upload_max_filesize = 10M
post_max_size = 10M

# Sessão segura
session.cookie_httponly = On
session.cookie_secure = On
session.cookie_samesite = "Strict"
```

---

## 13. Backup e Recuperação

### 🔄 BACKUP AUTOMÁTICO
```bash
#!/bin/bash
# backup.sh
mysqldump -u usuario -p senha plataforma_x > backup_$(date +%Y%m%d_%H%M%S).sql

# Agendar com cron
# 0 2 * * * /path/to/backup.sh
```

### 🔐 CRIPTOGRAFAR BACKUP
```bash
tar czf backup.tar.gz backup_*.sql
openssl enc -aes-256-cbc -in backup.tar.gz -out backup.tar.gz.enc
```

---

## 14. Monitoramento

### 📊 FERRAMENTAS RECOMENDADAS
- **Sentry**: Rastreamento de erros
- **New Relic**: Monitoramento de performance
- **CloudFlare**: DDoS protection
- **Wazuh**: Segurança e compliance

### Alertas
Configurar alertas para:
- ❌ Múltiplas tentativas de login falhadas
- ⚠️ Alterações em dados críticos
- 🚨 Acessos de países suspeitos

---

## 15. Testes de Segurança

### 🧪 CHECKLIST
- [ ] Teste de SQL Injection
- [ ] Teste de XSS
- [ ] Teste de CSRF
- [ ] Teste de autenticação bypass
- [ ] Teste de autorização
- [ ] Teste de força bruta
- [ ] Teste de leak de dados sensíveis
- [ ] Penetration test

### Ferramentas
- **OWASP ZAP**: Teste de segurança de aplicações web
- **Burp Suite**: Teste de penetração
- **SQLMap**: Teste de SQL Injection
- **w3af**: Web attack and audit framework

---

## 16. Conformidade Legal

### 📋 VERIFICAR
- **LGPD** (Lei Geral de Proteção de Dados - Brasil)
- **GDPR** (EU - General Data Protection Regulation)
- **CCPA** (California Consumer Privacy Act)

### Requisitos
- Consentimento para coleta de dados
- Direito ao esquecimento (right to be forgotten)
- Notificação de breach
- Criptografia de dados pessoais

---

## 17. Checklist de Deploy para Produção

```
ANTES DE COLOCAR EM PRODUÇÃO:

Segurança:
[ ] Alterar todas as senhas padrão
[ ] Ativar HTTPS/SSL
[ ] Configurar firewall
[ ] Implementar autenticação
[ ] Rate limiting ativado
[ ] Logs configurados
[ ] CORS restritivo

Performance:
[ ] Minificar CSS/JS
[ ] Otimizar imagens
[ ] Cache configurado
[ ] CDN ativado
[ ] Compressão gzip ativada

Testes:
[ ] Testes unitários
[ ] Testes de integração
[ ] Teste de carga
[ ] Teste de segurança
[ ] Teste em produção (staging)

Backup:
[ ] Backup automático configurado
[ ] Backup criptografado
[ ] Teste de restauração
[ ] Plano de recuperação

Monitoring:
[ ] Alertas configurados
[ ] Logs centralizados
[ ] Monitoramento de performance
[ ] Monitoramento de segurança

Documentação:
[ ] Documentação atualizada
[ ] Runbook de incidentes
[ ] Contatos de suporte
[ ] Plano de resposta a crises
```

---

## 18. Recursos e Referências

### 📚 Leitura Recomendada
- **OWASP Top 10**: https://owasp.org/www-project-top-ten/
- **NIST Cybersecurity Framework**: https://www.nist.gov/cyberframework
- **CWE Top 25**: https://cwe.mitre.org/top25/

### 🛠️ Ferramentas
- **Composer Audit**: Verificar vulnerabilidades
- **PHPStan**: Análise estática
- **Psalm**: Type checking e análise de segurança

---

## Contato de Segurança

Se encontrar uma vulnerabilidade:
1. ❌ NÃO publique em redes sociais
2. ✉️ Envie email para: security@seusite.com
3. ⏱️ Aguarde resposta em 48 horas
4. 🤝 Trabalhe conosco para fix

---

**Lembre-se:** Segurança é um processo contínuo, não um destino!

Mantenha-se atualizado com as últimas ameaças e melhores práticas.

---

**Versão**: 1.0
**Data**: 12 de Novembro de 2025
