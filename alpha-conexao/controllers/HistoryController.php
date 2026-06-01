<?php
// =============================================
// ALPHA CONEXÃO - Controller: Histórico
// =============================================

class HistoryController {
    private Consulta $consultaModel;

    public function __construct() {
        $this->consultaModel = new Consulta();
    }

    public function index(): void {
        Security::requireAuth();
        if (!Security::hasPermission('ver_historico')) {
            $_SESSION['flash_error'] = 'Sem permissão para acessar o histórico.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $filters = [
            'cnpj'        => trim($_GET['cnpj'] ?? ''),
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim'    => $_GET['data_fim'] ?? '',
        ];

        // Usuários comuns só veem o próprio histórico
        if ($_SESSION['usuario_role'] !== 'admin') {
            $filters['usuario_id'] = $_SESSION['usuario_id'];
        } elseif (!empty($_GET['usuario_id'])) {
            $filters['usuario_id'] = (int)$_GET['usuario_id'];
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total   = $this->consultaModel->count($filters);
        $pages   = (int)ceil($total / $perPage);
        $consultas = $this->consultaModel->getAll($filters, $page, $perPage);

        include VIEWS_PATH . '/history/index.php';
    }

    /** Exportação CSV/Excel */
    public function export(): void {
        Security::requireAuth();
        if (!Security::hasPermission('exportar_dados')) {
            header('Location: ' . APP_URL . '/historico');
            exit;
        }

        $filters = [
            'cnpj'        => trim($_GET['cnpj'] ?? ''),
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim'    => $_GET['data_fim'] ?? '',
        ];
        if ($_SESSION['usuario_role'] !== 'admin') {
            $filters['usuario_id'] = $_SESSION['usuario_id'];
        } elseif (!empty($_GET['usuario_id'])) {
            $filters['usuario_id'] = (int)$_GET['usuario_id'];
        }

        $consultas = $this->consultaModel->getAll($filters, 1, 10000);

        $format = $_GET['format'] ?? 'csv';

        if ($format === 'pdf') {
            $this->exportPdf($consultas);
        } else {
            $this->exportCsv($consultas);
        }
    }

    private function exportCsv(array $consultas): void {
        $filename = 'historico_cnpj_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
        fputcsv($out, ['#', 'CNPJ', 'Razão Social', 'Nome Fantasia', 'Situação', 'Usuário', 'IP', 'Data/Hora'], ';');

        foreach ($consultas as $i => $c) {
            fputcsv($out, [
                $i + 1,
                Security::formatCnpj($c['cnpj']),
                $c['razao_social'] ?? '',
                $c['nome_fantasia'] ?? '',
                $c['situacao'] ?? '',
                $c['usuario_nome'],
                $c['ip_address'] ?? '',
                date('d/m/Y H:i:s', strtotime($c['criado_em'])),
            ], ';');
        }

        fclose($out);
        exit;
    }

    private function exportPdf(array $consultas): void {
        // Exportação HTML → PDF simples (sem dependência externa)
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="historico_cnpj_' . date('Ymd_His') . '.html"');

        echo '<!DOCTYPE html><html><head><meta charset="utf-8">
        <title>Histórico CNPJ - Alpha Conexão</title>
        <style>body{font-family:Arial,sans-serif;font-size:12px}
        table{width:100%;border-collapse:collapse}
        th{background:#0F172A;color:#fff;padding:6px}
        td{border:1px solid #ccc;padding:5px}
        tr:nth-child(even){background:#f5f5f5}
        h1{color:#0F172A}</style></head><body>
        <h1>Alpha Conexão – Histórico de Consultas CNPJ</h1>
        <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
        <table><thead><tr>
        <th>#</th><th>CNPJ</th><th>Razão Social</th><th>Situação</th><th>Usuário</th><th>Data/Hora</th>
        </tr></thead><tbody>';

        foreach ($consultas as $i => $c) {
            echo '<tr><td>' . ($i + 1) . '</td>'
               . '<td>' . Security::escape(Security::formatCnpj($c['cnpj'])) . '</td>'
               . '<td>' . Security::escape($c['razao_social'] ?? '') . '</td>'
               . '<td>' . Security::escape($c['situacao'] ?? '') . '</td>'
               . '<td>' . Security::escape($c['usuario_nome']) . '</td>'
               . '<td>' . date('d/m/Y H:i:s', strtotime($c['criado_em'])) . '</td></tr>';
        }

        echo '</tbody></table></body></html>';
        exit;
    }
}
