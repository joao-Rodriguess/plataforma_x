# 🤔 Perguntas Frequentes (FAQ)

## Instalação

### P: Onde coloco os arquivos?
**R:** Na pasta raiz do seu servidor web:
- Windows (XAMPP): `C:\xampp\htdocs\plataforma_x`
- Windows (WAMP): `C:\wamp\www\plataforma_x`
- Mac (MAMP): `/Applications/MAMP/htdocs/plataforma_x`
- Linux: `/var/www/html/plataforma_x`

### P: Como faço para criar o banco de dados?
**R:** Existem 3 formas:

1. **phpMyAdmin (Fácil):**
   - Abra http://localhost/phpmyadmin
   - Clique em SQL
   - Copie e cole o conteúdo de `database/database.sql`
   - Clique em Executar

2. **Linha de comando:**
   ```bash
   mysql -u root -p < database/database.sql
   ```

3. **Script Windows:**
   - Execute `install_database.bat` na pasta do projeto

### P: Recebi erro "MySQL não encontrado no PATH"
**R:** O MySQL não está no seu PATH. Soluções:
- Adicione MySQL ao PATH (Windows)
- Ou use phpMyAdmin em vez do script
- Ou use o MySQL Workbench

### P: Qual é a senha padrão do MySQL?
**R:** Depende da sua instalação:
- XAMPP: Sem senha (deixe em branco)
- WAMP: Geralmente sem senha
- Instalação padrão: Verifique sua configuração

### P: Como mudo as credenciais do banco?
**R:** Edite `api/config.php`:
```php
define('DB_USER', 'novo_usuario');
define('DB_PASSWORD', 'nova_senha');
```

---

## Funcionalidades

### P: Como crio um novo usuário?
**R:** 
1. Clique em "Novo Usuário"
2. Preencha todos os campos
3. Clique em "Cadastrar Usuário"

### P: O CPF é validado?
**R:** Sim! Usa o algoritmo correto com dígitos verificadores. Exemplos:
- ✓ 123.456.789-00 (válido)
- ✗ 111.111.111-11 (inválido - sequência repetida)

### P: Posso usar CPF fictício para teste?
**R:** Sim, use: **123.456.789-00** (algoritmo o aceita)

### P: Posso editar apenas alguns campos?
**R:** Sim! Na modal de edição, você pode editar:
- Nome completo
- Email
- Telefone

**OBS:** CPF e nome de usuário não podem ser editados (para segurança)

### P: Posso deletar um usuário?
**R:** Sim, clique no botão "Deletar" na linha do usuário. Será solicitada confirmação.

### P: Os dados são salvos no banco?
**R:** Sim! Todos os dados vão direto para MySQL. Você pode verificar em phpMyAdmin.

---

## Segurança

### P: As senhas dos usuários são seguras?
**R:** Sim! Usamos **bcrypt com custo 10**, que é o padrão atual.

### P: Como verificar se a senha está criptografada?
**R:** No phpMyAdmin:
1. Vá para tabela `usuarios`
2. Veja a coluna `senha_usuario`
3. Você verá algo como: `$2y$10$...` (bcrypt hash)

### P: Posso ver a senha do usuário?
**R:** Não! É criptografada de forma irreversível. Nem nós conseguimos ver.

### P: A API é segura?
**R:** A versão básica é segura contra SQL Injection e XSS. Para produção:
- Implemente autenticação (JWT)
- Ative HTTPS/SSL
- Revise `SEGURANCA.md`

### P: O banco está protegido?
**R:** Sim! Usa `prepared statements`. Para mais proteção:
- Altere a senha padrão
- Configure firewall
- Faça backups regulares

---

## Erros Comuns

### P: "Falha na conexão com o banco de dados"
**R:** Verifique:
1. MySQL está rodando? (Apache + MySQL no XAMPP)
2. Credenciais em `api/config.php` estão corretas?
3. O banco foi criado? (Verifique em phpMyAdmin)

### P: "Página em branco"
**R:** 
1. Abra F12 (Console do navegador)
2. Procure por erros em vermelho
3. Confira se `css/style.css` e `js/script.js` existem
4. Verifique logs do PHP

### P: "404 Not Found"
**R:**
1. A URL está correta? `http://localhost/plataforma_x/`
2. A pasta está no local certo?
3. Reinicie o Apache

### P: "CPF já existe"
**R:** Esse CPF já foi cadastrado. Use outro CPF.

### P: "Email já existe"
**R:** Esse email já foi cadastrado. Use outro email.

### P: "Campos obrigatórios faltando"
**R:** Preencha todos os campos com * (asterisco).

---

## API/Backend

### P: Como uso a API diretamente?
**R:** Com ferramentas como Postman ou cURL:

```bash
# Listar usuários
curl http://localhost/plataforma_x/api/usuarios.php?acao=listar

# Criar usuário
curl -X POST http://localhost/plataforma_x/api/usuarios.php \
  -H "Content-Type: application/json" \
  -d '{"nome_completo":"João","cpf":"123.456.789-00",...}'
```

Veja `API_TESTING.md` para detalhes.

### P: Qual é a URL base da API?
**R:** `http://localhost/plataforma_x/api/usuarios.php`

### P: Como adiciono um novo campo?
**R:** 3 passos:
1. Adicione coluna no banco: `ALTER TABLE usuarios ADD ...`
2. Adicione validação em `api/usuarios.php`
3. Adicione campo no HTML e JavaScript

Veja `DOCUMENTACAO_TECNICA.md` para exemplo completo.

---

## Dados e Testes

### P: Tem dados de exemplo?
**R:** Sim! Execute `database/dados_exemplo.sql`:
```bash
mysql -u root -p plataforma_x < database/dados_exemplo.sql
```

Ou pelo phpMyAdmin.

### P: Como limpo todos os dados?
**R:** No phpMyAdmin:
1. Vá para tabela `usuarios`
2. Clique em "Esvaziar"

**OBS:** Isso não pode ser desfeito!

### P: Quantos usuários posso cadastrar?
**R:** Teoricamente ilimitado. Depende do espaço em disco.

---

## Performance

### P: A aplicação é lenta?
**R:** Não. Mas se estiver:
1. Verifique quantos usuários estão na tabela
2. Adicione índices (ver `DOCUMENTACAO_TECNICA.md`)
3. Habilite cache

### P: Há suporte a paginação?
**R:** Não na versão atual. Pode ser adicionado. Veja futuras melhorias.

---

## Backup e Recuperação

### P: Como faço backup do banco?
**R:** Via phpMyAdmin:
1. Selecione banco `plataforma_x`
2. Clique em "Exportar"
3. Clique em "Go"

**Ou via comando:**
```bash
mysqldump -u usuario -p senha plataforma_x > backup.sql
```

### P: Como restauro um backup?
**R:** Via phpMyAdmin:
1. Crie novo banco (ou use existente)
2. Clique em "Importar"
3. Selecione arquivo `.sql`
4. Clique em "Go"

**Ou via comando:**
```bash
mysql -u usuario -p novo_banco < backup.sql
```

---

## Customização

### P: Posso mudar as cores?
**R:** Sim! Edite `css/style.css`. Procure por:
```css
/* Cor principal */
#667eea, #764ba2
```

### P: Posso adicionar novos campos?
**R:** Sim, veja passo-a-passo em `DOCUMENTACAO_TECNICA.md` seção 8.1.

### P: Posso mudar o nome da tabela?
**R:** Tecnicamente sim, mas exigiria mudanças em vários arquivos. Não recomendado.

### P: Posso integrar com outra aplicação?
**R:** Sim! A API é REST pública. Faça requisições para `api/usuarios.php`.

---

## Produção

### P: Posso usar em produção?
**R:** Sim, mas implemente:
1. Autenticação (JWT)
2. HTTPS/SSL
3. Rate limiting
4. Backups automáticos

Veja `SEGURANCA.md` para checklist completo.

### P: Preciso de hospedagem especial?
**R:** Não. Qualquer hospedagem que suporte:
- PHP 7.4+
- MySQL 5.7+
- Apache com mod_rewrite

### P: Qual é a licença?
**R:** Fornecido como está para fins educacionais e comerciais.

---

## Suporte

### P: Onde posso tirar dúvidas?
**R:** 
1. Consulte os arquivos de documentação:
   - `README.md` - Guia geral
   - `DOCUMENTACAO_TECNICA.md` - Detalhes técnicos
   - `API_TESTING.md` - Testes da API
   - `SEGURANCA.md` - Questões de segurança

2. Procure por erros no console (F12)

3. Revise os logs do PHP

### P: Encontrei um bug. O que faço?
**R:**
1. Reproduza o erro consistentemente
2. Anotie os passos para reproduzir
3. Verifique se não há erros no console (F12)
4. Revise a documentação
5. Contacte suporte técnico

### P: Posso pedir novas funcionalidades?
**R:** Sim! Futuras melhorias planejadas:
- [ ] Autenticação (Login/Logout)
- [ ] Paginação
- [ ] Busca/Filtro
- [ ] Exportar para CSV/PDF
- [ ] Dashboard
- [ ] Múltiplos idiomas

---

## Solução de Problemas

### "Erro ao conectar" apareça frequentemente?
```php
// Verifique em api/config.php
// As credenciais estão corretas?
define('DB_HOST', 'localhost');
define('DB_USER', 'usuario_plataforma');
define('DB_PASSWORD', 'senha_segura_123');
```

### Página carrega mas não funciona?
```javascript
// Abra Console (F12)
// Procure por erros em vermelho
// Verifique se a API está respondendo
// Vá para Network tab e verifique requisições
```

### Banco recusa a conexão?
```bash
# Verifique se MySQL está rodando
# Windows: XAMPP Control Panel
# Mac: MAMP
# Linux: sudo service mysql status
```

---

## Dúvidas Gerais

### P: Por quanto tempo esse código será mantido?
**R:** Fornecido como está. Funciona com PHP 7.4+ e MySQL 5.7+.

### P: Posso vender uma aplicação baseada nisso?
**R:** Sim! Desde que respeite os requisitos de segurança e qualidade.

### P: Qual é o tamanho do arquivo?
**R:** ~500KB (sem banco de dados).

### P: Quanto tempo leva para instalar?
**R:** ~5 minutos seguindo `INICIO_RAPIDO.txt`.

---

## Não encontrou sua pergunta?

Consulte:
- **INDICE.txt** - Navegação rápida
- **README.md** - Guia completo
- **DOCUMENTACAO_TECNICA.md** - Detalhes técnicos
- **Abra o Console** (F12) - Para ver erros

---

**Última atualização:** 12 de Novembro de 2025  
**Versão:** 1.0  
**Status:** 100% funcional
