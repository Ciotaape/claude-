<?php
// =============================================
// ALPHA CONEXÃO - Controller: Autenticação
// =============================================

class AuthController {
    private User $userModel;
    private Log  $logModel;

    public function __construct() {
        $this->userModel = new User();
        $this->logModel  = new Log();
    }

    /** Exibe página de login */
    public function showLogin(): void {
        if (Security::isAuthenticated()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        $error   = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        include VIEWS_PATH . '/auth/login.php';
    }

    /** Processa login */
    public function login(): void {
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['auth_error'] = 'Token de segurança inválido.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $_SESSION['auth_error'] = 'Preencha todos os campos.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Rate limit por IP
        if (!Security::checkRateLimit('login_' . Security::getIp(), 10, 900)) {
            $_SESSION['auth_error'] = 'Muitas tentativas. Aguarde 15 minutos.';
            $this->logModel->record('LOGIN_BLOQUEADO', "IP bloqueado por rate limit: $email");
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user['senha'])) {
            $_SESSION['auth_error'] = 'E-mail ou senha incorretos.';
            $this->logModel->record('LOGIN_FALHA', "Tentativa falha para: $email");
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if ($user['status'] === 'pendente') {
            $_SESSION['auth_error'] = 'Sua conta ainda está aguardando aprovação do administrador.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if ($user['status'] === 'bloqueado') {
            $_SESSION['auth_error'] = 'Sua conta foi bloqueada. Entre em contato com o administrador.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Regenera session ID por segurança
        session_regenerate_id(true);

        $_SESSION['usuario_id']    = $user['id'];
        $_SESSION['usuario_nome']  = $user['nome'];
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_role']  = $user['role'];
        $_SESSION['usuario_status']= $user['status'];
        $_SESSION['permissoes']    = [
            'consultar_cnpj'       => (bool)$user['consultar_cnpj'],
            'ver_historico'        => (bool)$user['ver_historico'],
            'ver_logs'             => (bool)$user['ver_logs'],
            'gerenciar_usuarios'   => (bool)$user['gerenciar_usuarios'],
            'acessar_configuracoes'=> (bool)$user['acessar_configuracoes'],
            'exportar_dados'       => (bool)$user['exportar_dados'],
        ];

        // Lembrar acesso
        if (!empty($_POST['lembrar'])) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->setRememberToken($user['id'], $token);
            setcookie('remember_token', $token, time() + 86400 * 30, '/', '', true, true);
        }

        $this->userModel->updateLastLogin($user['id']);
        $this->logModel->record('LOGIN', 'Login realizado com sucesso', $user['id']);

        header('Location: ' . APP_URL . '/dashboard');
        exit;
    }

    /** Exibe página de cadastro */
    public function showRegister(): void {
        if (Security::isAuthenticated()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        $error   = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        include VIEWS_PATH . '/auth/register.php';
    }

    /** Processa cadastro */
    public function register(): void {
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['auth_error'] = 'Token de segurança inválido.';
            header('Location: ' . APP_URL . '/register');
            exit;
        }

        $nome  = trim($_POST['nome']  ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $conf  = $_POST['confirmar_senha'] ?? '';

        if (strlen($nome) < 3) {
            $_SESSION['auth_error'] = 'Nome deve ter pelo menos 3 caracteres.';
            header('Location: ' . APP_URL . '/register');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = 'E-mail inválido.';
            header('Location: ' . APP_URL . '/register');
            exit;
        }
        if (strlen($senha) < 8) {
            $_SESSION['auth_error'] = 'Senha deve ter pelo menos 8 caracteres.';
            header('Location: ' . APP_URL . '/register');
            exit;
        }
        if ($senha !== $conf) {
            $_SESSION['auth_error'] = 'As senhas não coincidem.';
            header('Location: ' . APP_URL . '/register');
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['auth_error'] = 'Este e-mail já está cadastrado.';
            header('Location: ' . APP_URL . '/register');
            exit;
        }

        $this->userModel->create(['nome' => $nome, 'email' => $email, 'senha' => $senha]);
        $this->logModel->record('CADASTRO', "Novo cadastro: $email (aguardando aprovação)", null);

        // Notifica admin (silencioso – SMTP pode não estar configurado)
        $this->notifyAdminNewUser($nome, $email);

        $_SESSION['auth_success'] = 'Cadastro realizado! Aguarde a aprovação do administrador.';
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /** Logout */
    public function logout(): void {
        $userId = $_SESSION['usuario_id'] ?? null;
        $this->logModel->record('LOGOUT', 'Logout realizado', $userId);
        $this->userModel->clearRememberToken($userId ?? 0);
        setcookie('remember_token', '', time() - 3600, '/');
        session_destroy();
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /** Exibe formulário de recuperação de senha */
    public function showForgotPassword(): void {
        $error   = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        include VIEWS_PATH . '/auth/forgot-password.php';
    }

    /** Processa solicitação de recuperação de senha */
    public function forgotPassword(): void {
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['auth_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/forgot-password');
            exit;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $token = $this->userModel->setResetToken($email);

        // Resposta genérica por segurança
        $_SESSION['auth_success'] = 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.';

        if ($token) {
            $this->sendResetEmail($email, $token);
            $this->logModel->record('RESET_SENHA', "Solicitação de reset para: $email");
        }

        header('Location: ' . APP_URL . '/forgot-password');
        exit;
    }

    /** Exibe formulário de nova senha */
    public function showResetPassword(string $token): void {
        $user = $this->userModel->findByResetToken($token);
        if (!$user) {
            $_SESSION['auth_error'] = 'Link inválido ou expirado.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }
        $error   = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        include VIEWS_PATH . '/auth/reset-password.php';
    }

    /** Processa nova senha */
    public function resetPassword(string $token): void {
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['auth_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $user = $this->userModel->findByResetToken($token);
        if (!$user) {
            $_SESSION['auth_error'] = 'Link inválido ou expirado.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $senha = $_POST['senha'] ?? '';
        $conf  = $_POST['confirmar_senha'] ?? '';

        if (strlen($senha) < 8 || $senha !== $conf) {
            $_SESSION['auth_error'] = 'Senha inválida ou senhas não coincidem.';
            header('Location: ' . APP_URL . '/reset-password/' . $token);
            exit;
        }

        $this->userModel->resetPassword($user['id'], $senha);
        $this->logModel->record('SENHA_ALTERADA', 'Senha redefinida via token', $user['id']);
        $_SESSION['auth_success'] = 'Senha redefinida com sucesso. Faça login.';
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /** Verifica cookie "lembrar" e autentica automaticamente */
    public function checkRememberMe(): void {
        if (Security::isAuthenticated()) return;
        $token = $_COOKIE['remember_token'] ?? null;
        if (!$token) return;

        $user = $this->userModel->findByRememberToken($token);
        if (!$user) return;

        session_regenerate_id(true);
        $_SESSION['usuario_id']    = $user['id'];
        $_SESSION['usuario_nome']  = $user['nome'];
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_role']  = $user['role'];
        $_SESSION['usuario_status']= $user['status'];

        $full = $this->userModel->findById($user['id']);
        $_SESSION['permissoes'] = [
            'consultar_cnpj'       => (bool)($full['consultar_cnpj'] ?? false),
            'ver_historico'        => (bool)($full['ver_historico'] ?? false),
            'ver_logs'             => (bool)($full['ver_logs'] ?? false),
            'gerenciar_usuarios'   => (bool)($full['gerenciar_usuarios'] ?? false),
            'acessar_configuracoes'=> (bool)($full['acessar_configuracoes'] ?? false),
            'exportar_dados'       => (bool)($full['exportar_dados'] ?? false),
        ];
    }

    private function notifyAdminNewUser(string $nome, string $email): void {
        // Notificação por e-mail (requer SMTP configurado)
        $to      = ADMIN_EMAIL;
        $subject = 'Alpha Conexão – Novo usuário aguardando aprovação';
        $body    = "Novo cadastro aguardando aprovação:\n\nNome: $nome\nE-mail: $email\n\nAcesse o painel para aprovar.";
        @mail($to, $subject, $body, "From: noreply@alphaconexao.com.br\r\n");
    }

    private function sendResetEmail(string $email, string $token): void {
        $link    = APP_URL . '/reset-password/' . $token;
        $subject = 'Alpha Conexão – Redefinição de senha';
        $body    = "Clique no link para redefinir sua senha (válido por 1 hora):\n\n$link";
        @mail($email, $subject, $body, "From: noreply@alphaconexao.com.br\r\n");
    }
}
