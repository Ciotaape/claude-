<?php
// =============================================
// ALPHA CONEXÃO - Controller: Logs de Auditoria
// =============================================

class LogController {
    private Log $logModel;

    public function __construct() {
        $this->logModel = new Log();
    }

    public function index(): void {
        Security::requireAuth();
        if (!Security::hasPermission('ver_logs')) {
            $_SESSION['flash_error'] = 'Sem permissão para visualizar logs.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $filters = [
            'acao'        => $_GET['acao'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim'    => $_GET['data_fim'] ?? '',
        ];
        if (!empty($_GET['usuario_id'])) {
            $filters['usuario_id'] = (int)$_GET['usuario_id'];
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $total   = $this->logModel->count($filters);
        $pages   = (int)ceil($total / $perPage);
        $logs    = $this->logModel->getAll($filters, $page, $perPage);
        $acoes   = $this->logModel->getDistinctAcoes();

        include VIEWS_PATH . '/logs/index.php';
    }
}
