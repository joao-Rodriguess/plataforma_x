# Guia de Testes da API

## Ferramentas Recomendadas
- Postman (https://www.postman.com/)
- Insomnia (https://insomnia.rest/)
- cURL (linha de comando)

## Testes via cURL

### 1. Listar Todos os Usuários
```bash
curl -X GET "http://localhost/plataforma_x/api/usuarios.php?acao=listar"
```

### 2. Obter Usuário Específico
```bash
curl -X GET "http://localhost/plataforma_x/api/usuarios.php?acao=obter&id=1"
```

### 3. Criar Novo Usuário
```bash
curl -X POST "http://localhost/plataforma_x/api/usuarios.php" \
  -H "Content-Type: application/json" \
  -d '{
    "nome_completo": "Teste User",
    "cpf": "111.222.333-44",
    "email": "teste@exemplo.com",
    "telefone": "(11) 99999-9999",
    "nome_usuario_banco": "teste_user",
    "senha_banco": "senha_teste_123",
    "senha_usuario": "user_pass_123"
  }'
```

### 4. Atualizar Usuário
```bash
curl -X PUT "http://localhost/plataforma_x/api/usuarios.php" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "nome_completo": "Novo Nome",
    "email": "novo@exemplo.com",
    "telefone": "(11) 88888-8888"
  }'
```

### 5. Deletar Usuário
```bash
curl -X DELETE "http://localhost/plataforma_x/api/usuarios.php?id=1"
```

## Resposta de Sucesso

```json
{
  "sucesso": true,
  "mensagem": "Usuário criado com sucesso",
  "id": 4
}
```

## Resposta de Erro

```json
{
  "erro": "CPF inválido"
}
```

## Testes de Validação

### CPF Inválido
```bash
curl -X POST "http://localhost/plataforma_x/api/usuarios.php" \
  -H "Content-Type: application/json" \
  -d '{
    "nome_completo": "Teste",
    "cpf": "111.111.111-11",
    "email": "teste@exemplo.com",
    "telefone": "(11) 99999-9999",
    "nome_usuario_banco": "teste",
    "senha_banco": "senha",
    "senha_usuario": "123456"
  }'
```

**Resultado Esperado:**
```json
{
  "erro": "CPF inválido"
}
```

### Email Inválido
```bash
curl -X POST "http://localhost/plataforma_x/api/usuarios.php" \
  -H "Content-Type: application/json" \
  -d '{
    "nome_completo": "Teste",
    "cpf": "123.456.789-00",
    "email": "email_invalido",
    "telefone": "(11) 99999-9999",
    "nome_usuario_banco": "teste",
    "senha_banco": "senha",
    "senha_usuario": "123456"
  }'
```

**Resultado Esperado:**
```json
{
  "erro": "Email inválido"
}
```

### Campos Obrigatórios Faltando
```bash
curl -X POST "http://localhost/plataforma_x/api/usuarios.php" \
  -H "Content-Type: application/json" \
  -d '{
    "nome_completo": "Teste",
    "email": "teste@exemplo.com"
  }'
```

**Resultado Esperado:**
```json
{
  "erro": "Campos obrigatórios faltando"
}
```

## Códigos HTTP Retornados

| Código | Descrição |
|--------|-----------|
| 200    | OK - Requisição bem-sucedida |
| 201    | Created - Usuário criado com sucesso |
| 400    | Bad Request - Erro na requisição |
| 404    | Not Found - Usuário não encontrado |
| 405    | Method Not Allowed - Método HTTP não permitido |
| 500    | Internal Server Error - Erro no servidor |

## Checklist de Testes

- [ ] Listar usuários funciona
- [ ] Obter usuário específico funciona
- [ ] Criar usuário com dados válidos funciona
- [ ] Validação de CPF funciona
- [ ] Validação de email funciona
- [ ] Validação de campos obrigatórios funciona
- [ ] Editar usuário funciona
- [ ] Deletar usuário funciona
- [ ] CPF duplicado é rejeitado
- [ ] Email duplicado é rejeitado
- [ ] Senha é criptografada corretamente

---

**Dica**: Use o Postman para testar facilmente. Importe a collection JSON para automatizar esses testes!
