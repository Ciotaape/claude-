<?php
// =============================================
// ALPHA CONEXÃO - Controller: Dashboard
// =============================================

class DashboardController {
    private Consulta    $consultaModel;
    private User        $userModel;
    private Configuracao$configModel;

    public function __construct() {
        $this->consultaModel = new Consulta();
        $this->userModel     = new User();
        $this->configModel   = new Configuracao();
    }

    public function index(): void {
        Security::requireAuth();

        $stats = [
            'total_consultas'   => $this->consultaModel->countTotal(),
            'consultas_mes'     => $this->consultaModel->countMonth(),
            'usuarios_ativos'   => $this->userModel->countActive(),
            'usuarios_pendentes'=> $this->userModel->countPending(),
        ];

        $recentConsultas = $this->consultaModel->getRecent(10);
        $dailyStats      = $this->consultaModel->getDailyStats(30);
        $monthlyStats    = $this->consultaModel->getMonthlyStats(12);
        $config          = $this->configModel->getAll();

        include VIEWS_PATH . '/dashboard/index.php';
    }
}
