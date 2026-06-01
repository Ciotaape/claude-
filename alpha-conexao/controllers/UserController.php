<?php
// =============================================
// ALPHA CONEXÃO - Controller: Usuários
// =============================================

class UserController {
    private User $userModel;
    private Log  $logModel;

    public function __construct() {
        $this->userModel = new User();
        $this->logModel  = new Log();
    }

    public function index(): void {
        Security::requireAdmin();
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => trim($_GET['search'] ?? ''),
        ];
        $usuarios = $this->userModel->getAll($filters);
        $error    = $_SESSION['flash_error'] ?? null;
        $success  = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        include VIEWS_PATH . '/users/index.php';
    }

    public function approve(int $id): void {
        Security::requireAdmin();
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['flash_error'] = 'Usuário não encontrado.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $this->userModel->updateStatus($id, 'ativo');
        $this->logModel->record('USUARIO_APROVADO', "Usuário #{$id} ({$user['email']}) aprovado");
        $this->notifyUserApproved($user['email'], $user['nome']);

        $_SESSION['flash_success'] = "Usuário {$user['nome']} aprovado com sucesso.";
        header('Location: ' . APP_URL . '/usuarios');
        exit;
    }

    public function block(int $id): void {
        Security::requireAdmin();
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $user = $this->userModel->findById($id);
        if (!$user || $user['role'] === 'admin') {
            $_SESSION['flash_error'] = 'Operação não permitida.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $this->userModel->updateStatus($id, 'bloqueado');
        $this->logModel->record('USUARIO_BLOQUEADO', "Usuário #{$id} ({$user['email']}) bloqueado");

        $_SESSION['flash_success'] = "Usuário {$user['nome']} bloqueado.";
        header('Location: ' . APP_URL . '/usuarios');
        exit;
    }

    public function delete(int $id): void {
        Security::requireAdmin();
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $user = $this->userModel->findById($id);
        if (!$user || $user['role'] === 'admin') {
            $_SESSION['flash_error'] = 'Não é possível excluir este usuário.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $this->userModel->delete($id);
        $this->logModel->record('USUARIO_EXCLUIDO', "Usuário #{$id} ({$user['email']}) excluído");

        $_SESSION['flash_success'] = "Usuário {$user['nome']} excluído.";
        header('Location: ' . APP_URL . '/usuarios');
        exit;
    }

    public function updatePermissions(int $id): void {
        Security::requireAdmin();
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $perms = [
            'consultar_cnpj'       => isset($_POST['consultar_cnpj']) ? 1 : 0,
            'ver_historico'        => isset($_POST['ver_historico']) ? 1 : 0,
            'ver_logs'             => isset($_POST['ver_logs']) ? 1 : 0,
            'gerenciar_usuarios'   => isset($_POST['gerenciar_usuarios']) ? 1 : 0,
            'acessar_configuracoes'=> isset($_POST['acessar_configuracoes']) ? 1 : 0,
            'exportar_dados'       => isset($_POST['exportar_dados']) ? 1 : 0,
        ];

        $user = $this->userModel->findById($id);
        $this->userModel->updatePermissions($id, $perms);
        $this->logModel->record('PERMISSOES_ALTERADAS', "Permissões do usuário #{$id} atualizadas");

        $_SESSION['flash_success'] = "Permissões de {$user['nome']} atualizadas.";
        header('Location: ' . APP_URL . '/usuarios');
        exit;
    }

    private function notifyUserApproved(string $email, string $nome): void {
        $subject = 'Alpha Conexão – Conta aprovada!';
        $body    = "Olá $nome,\n\nSua conta foi aprovada. Acesse o sistema em: " . APP_URL . "/login";
        @mail($email, $subject, $body, "From: noreply@alphaconexao.com.br\r\n");
    }
}
