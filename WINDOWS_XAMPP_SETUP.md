🪟 GUIA WINDOWS/XAMPP - SETUP AUTOMÁTICO DE CREDENCIAIS
═════════════════════════════════════════════════════════════════════

Este arquivo contém instruções específicas para Windows com XAMPP.

═════════════════════════════════════════════════════════════════════
✅ PASSO 1: VERIFICAR XAMPP
═════════════════════════════════════════════════════════════════════

1. Abra XAMPP Control Panel
   └─ Procure no Menu Iniciar por "XAMPP Control Panel"

2. Clique em "Start" para:
   ├─ Apache
   └─ MySQL

3. Aguarde os serviços ficarem em estado "Running" (verde)

Exemplo:
┌─────────────────────────────────┐
│ Apache        [Start] Running   │
│ MySQL         [Start] Running   │
│ FileZilla FTP [  ]   Stopped    │
│ Mercury Mail  [  ]   Stopped    │
└─────────────────────────────────┘

═════════════════════════════════════════════════════════════════════
✅ PASSO 2: ACESSAR A PASTA DO PROJETO
═════════════════════════════════════════════════════════════════════

Abre o Windows Explorer e navegue até:
  C:\xampp\htdocs\plataforma_x

Você deve ver os arquivos:
  ├─ api/
  ├─ css/
  ├─ database/
  ├─ js/
  ├─ setup.html          ← NOVO
  ├─ setup.php           ← NOVO
  ├─ install_database.php
  └─ ... outros arquivos

═════════════════════════════════════════════════════════════════════
✅ PASSO 3: EXECUTAR O SETUP (OPÇÃO A - WEB - RECOMENDADO)
═════════════════════════════════════════════════════════════════════

É a FORMA MAIS FÁCIL! Não precisa de terminal.

1. Abra seu navegador (Chrome, Firefox, Edge, etc)

2. Digite na barra de endereços:
   http://localhost/plataforma_x/setup.html

3. Você verá uma página bonita com 3 passos

4. Clique em "Executar Passo 1"
   └─ Aguarde até ver "✓ Credenciais geradas!"

5. Clique em "Executar Passo 2"
   └─ Digite credenciais root (padrão: root, sem senha)
   └─ Aguarde até ver "✓ Usuário criado com sucesso!"

6. Clique em "Executar Passo 3"
   └─ Aguarde até ver "✓ Banco de dados instalado com sucesso!"

7. Pronto! Acesse: http://localhost/plataforma_x

═════════════════════════════════════════════════════════════════════
✅ PASSO 3: EXECUTAR O SETUP (OPÇÃO B - LINHA DE COMANDO)
═════════════════════════════════════════════════════════════════════

Se preferir usar terminal/PowerShell:

1. Abra PowerShell como ADMINISTRADOR
   └─ Clique com botão direito no ícone do PowerShell → "Executar como administrador"

2. Navegue até a pasta:
   cd C:\xampp\htdocs\plataforma_x

3. Execute o Passo 1:
   c:\xampp\php\php.exe api/setup.php

   Resultado:
   ╔════════════════════════════════════════════════════════╗
   ║   SETUP AUTOMÁTICO - PLATAFORMA X                     ║
   ║   Geração de Credenciais do Banco de Dados            ║
   ╚════════════════════════════════════════════════════════╝
   
   [1/3] Gerando credenciais...
      ✓ Usuário: plat_a1b2c3d4
      ✓ Senha: ********************
   
   [2/3] Criando arquivo de configuração...
      ✓ Arquivo api/config.php criado
   
   [3/3] Salvando credenciais para referência...
      ✓ Credenciais salvas em: .credentials.txt

4. Execute o Passo 2:
   c:\xampp\php\php.exe api/create_user.php

   O sistema vai pedir:
   ├─ Usuário ROOT MySQL [padrão: root]: 
   │  └─ Deixe em branco e pressione ENTER
   └─ Senha ROOT MySQL [deixe em branco se não houver]: 
      └─ Deixe em branco e pressione ENTER

   Resultado:
   ╔════════════════════════════════════════════════════════╗
   ║   CRIANDO USUÁRIO DO BANCO DE DADOS                    ║
   ╚════════════════════════════════════════════════════════╝
   
   [1/3] Lendo credenciais geradas...
   [2/3] Conectando ao MySQL como root...
   [3/3] Criando usuário do banco de dados...
      ✓ Usuário criado
      ✓ Banco de dados criado
      ✓ Privilégios concedidos

5. Execute o Passo 3:
   c:\xampp\php\php.exe install_database.php

   Resultado:
   ╔════════════════════════════════════════════════════════╗
   ║   INSTALANDO BANCO DE DADOS                           ║
   ╚════════════════════════════════════════════════════════╝
   
   [1/3] Lendo configurações...
   [2/3] Conectando ao banco de dados...
   [3/3] Executando scripts SQL...
      ✓ 7 comandos SQL executados

═════════════════════════════════════════════════════════════════════
🔑 ONDE ESTÃO MINHAS CREDENCIAIS?
═════════════════════════════════════════════════════════════════════

Dois lugares:

1. ARQUIVO PERMANENTE (usado pela aplicação):
   C:\xampp\htdocs\plataforma_x\api\config.php

   Abra com Notepad e veja:
   ┌──────────────────────────────────────┐
   │ define('DB_USER', 'plat_xxxxx');    │
   │ define('DB_PASSWORD', 'xxxxx');     │
   └──────────────────────────────────────┘

2. ARQUIVO TEMPORÁRIO (backup para você):
   C:\xampp\htdocs\plataforma_x\.credentials.txt

   Abra com Notepad para ver:
   ┌──────────────────────────────────────┐
   │ Usuário: plat_xxxxx                 │
   │ Senha: xxxxx                        │
   └──────────────────────────────────────┘

IMPORTANTE: Delete .credentials.txt após guardar as credenciais!

═════════════════════════════════════════════════════════════════════
✅ VERIFICAR SE FUNCIONOU
═════════════════════════════════════════════════════════════════════

1. Abra MySQL via XAMPP:
   ├─ XAMPP Control Panel
   └─ Clique em "Admin" ao lado de "MySQL"

2. Uma página web vai abrir (phpMyAdmin)

3. À esquerda, você deve ver:
   ├─ plataforma_x  ← Seu banco foi criado!
   │  ├─ usuarios   ← Tabela foi criada!
   │  └─ ...

4. Se viu isso, PARABÉNS! Tudo funcionou! 🎉

═════════════════════════════════════════════════════════════════════
🚀 ACESSAR A PLATAFORMA
═════════════════════════════════════════════════════════════════════

Abra o navegador e acesse:
  http://localhost/plataforma_x

Você verá a página inicial da plataforma.

═════════════════════════════════════════════════════════════════════
🐛 TROUBLESHOOTING - ERROS COMUNS
═════════════════════════════════════════════════════════════════════

ERRO 1: "php: O termo 'php' não é reconhecido"
─────────────────────────────────────────────────
Solução: Use o caminho completo:
  c:\xampp\php\php.exe setup.php


ERRO 2: "Erro ao conectar como root"
─────────────────────────────────────
Solução 1: Certifique-se que MySQL está rodando no XAMPP
Solução 2: Verifique se XAMPP está com privilégios de admin


ERRO 3: "Arquivo de configuração não encontrado"
────────────────────────────────────────────────
Solução: Execute o Passo 1 (setup.php) primeiro


ERRO 4: "Erro: Database plataforma_x não existe"
────────────────────────────────────────────────
Solução: Execute o Passo 2 (create_user.php) que cria o banco


ERRO 5: "Acesso negado para usuário plat_xxxxx"
───────────────────────────────────────────────
Solução: Execute o Passo 2 (create_user.php) novamente


ERRO 6: "Página em branco ao acessar setup.html"
───────────────────────────────────────────────
Solução 1: Verifique se Apache está rodando no XAMPP
Solução 2: Verifique a URL: http://localhost/plataforma_x/setup.html


═════════════════════════════════════════════════════════════════════
💡 DICAS
═════════════════════════════════════════════════════════════════════

1. Inicie o XAMPP primeiro
   └─ Apache + MySQL rodando

2. Use a interface web (setup.html)
   └─ Mais fácil que linha de comando

3. Guarde as credenciais de forma segura
   └─ Use um password manager (1Password, LastPass, etc)

4. Delete .credentials.txt após guardar
   └─ Por segurança

5. Nunca compartilhe api/config.php
   └─ Contém credenciais do banco

═════════════════════════════════════════════════════════════════════
📚 DOCUMENTAÇÃO
═════════════════════════════════════════════════════════════════════

Leia os outros arquivos para mais informações:

• COMECO_AQUI_SETUP.txt      ← Visão geral (COMECE AQUI!)
• QUICK_START_AUTO.md         ← Guia rápido (2 minutos)
• SETUP_AUTOMATICO.md         ← Guia completo (passo a passo)
• WINDOWS_XAMPP_SETUP.md      ← Este arquivo (instruções Windows)

═════════════════════════════════════════════════════════════════════
🎯 CHECKLIST DE CONCLUSÃO
═════════════════════════════════════════════════════════════════════

✓ Iniciei XAMPP (Apache + MySQL rodando)
✓ Abri http://localhost/plataforma_x/setup.html (ou usei CLI)
✓ Executei Passo 1 (setup.php) com sucesso
✓ Executei Passo 2 (create_user.php) com sucesso
✓ Executei Passo 3 (install_database.php) com sucesso
✓ Guardei as credenciais de forma segura
✓ Deletei .credentials.txt (ou planejou deletar)
✓ Verifiquei no phpMyAdmin que o banco foi criado
✓ Acessei http://localhost/plataforma_x com sucesso

Se marcou todos os itens, PARABÉNS! 🎉

═════════════════════════════════════════════════════════════════════
📞 SUPORTE
═════════════════════════════════════════════════════════════════════

Se tiver dúvidas ou problemas:

1. Leia os arquivos de documentação
2. Verifique a seção Troubleshooting acima
3. Consulte FAQ.md para perguntas frequentes
4. Verifique DOCUMENTACAO_TECNICA.md para detalhes

═════════════════════════════════════════════════════════════════════
Criado: Novembro 2025 | Plataforma X v1.0
═════════════════════════════════════════════════════════════════════
