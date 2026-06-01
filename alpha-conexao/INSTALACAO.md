# Alpha Conexão – Manual de Instalação

## Requisitos

| Item | Versão mínima |
|------|--------------|
| PHP  | 8.1 |
| MySQL / MariaDB | 5.7 / 10.3 |
| Apache | 2.4 com mod_rewrite |
| Extensões PHP | PDO, PDO_MySQL, json, mbstring, openssl |

---

## 1. Upload dos Arquivos (cPanel / FTP)

1. Compacte a pasta `alpha-conexao/` em um arquivo `.zip`.
2. No cPanel → **Gerenciador de Arquivos**, navegue até `public_html/`.
3. Faça upload do `.zip` e extraia.
4. O caminho final deve ser: `public_html/alpha-conexao/`

---

## 2. Banco de Dados

1. No cPanel → **MySQL Databases**:
   - Crie o banco: `alpha_conexao`
   - Crie o usuário: `alpha_user` com senha forte
   - Atribua **todos os privilégios** ao usuário no banco

2. No cPanel → **phpMyAdmin**:
   - Selecione o banco `alpha_conexao`
   - Clique em **Importar**
   - Selecione o arquivo `database/schema.sql`
   - Clique **Executar**

---

## 3. Configuração do Sistema

### 3.1 Conexão com o Banco

Edite o arquivo `config/database.php`:

```php
private static array $config = [
    'host'   => 'localhost',
    'dbname' => 'alpha_conexao',  // nome do banco criado
    'user'   => 'alpha_user',     // usuário MySQL
    'pass'   => 'SUA_SENHA_AQUI', // senha do usuário
    'charset'=> 'utf8mb4',
];
```

### 3.2 URL do Sistema

Edite o arquivo `config/config.php`:

```php
define('APP_URL', 'https://seudominio.com.br/alpha-conexao');
```

> Se instalar na raiz: `define('APP_URL', 'https://seudominio.com.br');`

### 3.3 .htaccess

Se instalar em subpasta diferente, ajuste a linha:
```
RewriteBase /alpha-conexao/
```
Para o caminho correto, ex: `RewriteBase /sistema/`

---

## 4. Primeiro Acesso

1. Acesse: `https://seudominio.com.br/alpha-conexao/login`
2. **E-mail:** `tacio.cleiner19@gmail.com`
3. **Senha padrão:** `password`
4. **⚠️ IMPORTANTE:** Troque a senha imediatamente após o primeiro login.

### Trocar a senha padrão:

A senha padrão está no arquivo `database/schema.sql`:
```sql
password_hash('password', PASSWORD_BCRYPT)
```
Após fazer login, execute no MySQL para atualizar:
```sql
UPDATE usuarios SET senha = '$2y$12$HASH_DA_NOVA_SENHA' WHERE email = 'tacio.cleiner19@gmail.com';
```
Ou use a funcionalidade "Esqueci minha senha" na tela de login.

---

## 5. Configurações via Painel

Após fazer login como administrador, acesse **Configurações** para:

- Alterar nome e logotipo da empresa
- Configurar servidor SMTP para envio de e-mails
- Definir limite de consultas por hora
- Fazer backup do banco de dados

---

## 6. Permissões de Diretórios

Execute no terminal (SSH) ou via cPanel → Terminal:

```bash
chmod 755 public_html/alpha-conexao
chmod 644 public_html/alpha-conexao/config/database.php
chmod 644 public_html/alpha-conexao/config/config.php
chmod 755 public_html/alpha-conexao/assets/img/uploads
```

---

## 7. HTTPS (Recomendado)

1. No cPanel → **Let's Encrypt SSL**, emita o certificado para seu domínio.
2. No `config/config.php`, altere a URL para `https://`.
3. O sistema configurará automaticamente cookies seguros via HTTPS.

---

## 8. Fluxo de Usuários

```
Usuário solicita cadastro → Conta fica "pendente"
        ↓
Administrador recebe notificação por e-mail
        ↓
Admin acessa "Usuários" → Clica em ✓ Aprovar
        ↓
Usuário recebe e-mail de aprovação e pode fazer login
```

---

## 9. API Open CNPJ

O sistema usa a API pública: `https://publica.cnpj.ws/cnpj/`

- Sem necessidade de chave de API
- Limite: 3 requisições/segundo (gerenciado pelo rate limit interno)
- Documentação: https://publica.cnpj.ws

---

## 10. Estrutura de Arquivos

```
alpha-conexao/
├── index.php              ← Front controller (roteamento)
├── .htaccess              ← Rewrite rules + segurança
├── config/
│   ├── config.php         ← Configurações globais
│   └── database.php       ← Conexão PDO
├── controllers/           ← Lógica de negócio (MVC)
├── models/                ← Acesso ao banco
├── views/                 ← Templates HTML/PHP
│   ├── layouts/           ← Header, sidebar, footer
│   ├── auth/              ← Login, cadastro, reset
│   ├── dashboard/
│   ├── cnpj/
│   ├── history/
│   ├── users/
│   ├── logs/
│   ├── settings/
│   └── errors/
├── assets/
│   ├── css/main.css       ← Design system completo
│   ├── js/main.js         ← Interatividade
│   └── img/               ← Imagens e uploads
├── lib/
│   ├── Router.php         ← Roteador
│   └── Security.php       ← CSRF, sanitização, rate limit
└── database/
    └── schema.sql         ← Script SQL completo
```

---

## 11. Segurança Implementada

| Recurso | Implementação |
|---------|--------------|
| Senhas | bcrypt (custo 12) |
| CSRF | Token por sessão (validade 1h) |
| SQL Injection | PDO + prepared statements |
| XSS | `htmlspecialchars` em todos os outputs |
| Rate Limit | 60 consultas/hora por usuário |
| Rate Limit Login | 10 tentativas / 15min por IP |
| Sessões | HttpOnly, SameSite=Strict, regeneração no login |
| Headers HTTP | X-Frame-Options, X-XSS-Protection, CSP |
| Uploads | Validação de MIME type e tamanho |

---

## Suporte

Sistema desenvolvido para uso exclusivo da **Alpha Conexão**.
Administrador: tacio.cleiner19@gmail.com
