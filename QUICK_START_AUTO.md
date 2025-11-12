# 🎯 SETUP RÁPIDO - Credenciais Automáticas

## 2️⃣ Formas de Configurar o Banco de Dados

### **Opção 1: Pela Interface Web (Recomendado para iniciantes)**

1. Abra no navegador:
   ```
   http://localhost/plataforma_x/setup.html
   ```

2. Clique nos botões para executar cada passo
3. Pronto! Seu banco está configurado

---

### **Opção 2: Pelo Terminal/PowerShell (Recomendado para desenvolvedores)**

#### **Passo 1:** Gerar Credenciais
```powershell
php api/setup.php
```

#### **Passo 2:** Criar Usuário no MySQL
```powershell
php api/create_user.php
```

Quando solicitado:
- Usuário ROOT: `root` (ou deixe em branco)
- Senha ROOT: (deixe em branco se não houver)

#### **Passo 3:** Instalar Banco de Dados
```powershell
php install_database.php
```

---

## ✅ Verificar Instalação

Após os 3 passos, você verá:
```
╔════════════════════════════════════════════════════════╗
║   ✓ BANCO DE DADOS INSTALADO COM SUCESSO!             ║
╚════════════════════════════════════════════════════════╝
```

---

## 📌 O Que Muda

### **Antes:**
```php
define('DB_USER', 'usuario_plataforma');
define('DB_PASSWORD', 'senha_segura_123');
```

### **Depois:**
```php
define('DB_USER', 'plat_a1b2c3d4');          // ← Gerado aleatoriamente
define('DB_PASSWORD', 'XyZ9#$kL...abc...');  // ← Gerado aleatoriamente
```

---

## 🔒 Segurança

✅ Credenciais **únicas** geradas para cada instalação  
✅ Senhas **fortes** com 20 caracteres  
✅ Arquivo de backup `.credentials.txt` com permissões restritas  

---

## 📞 Precisa de Ajuda?

Leia:
- `SETUP_AUTOMATICO.md` - Guia completo passo a passo
- `DOCUMENTACAO_TECNICA.md` - Documentação técnica
- `FAQ.md` - Perguntas frequentes

---

**Tudo pronto! Acesse:** `http://localhost/plataforma_x` 🚀
