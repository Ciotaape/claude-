<?php
// =============================================
// ALPHA CONEXÃO - Controller: Configurações
// =============================================

class SettingsController {
    private Configuracao $configModel;
    private Log          $logModel;

    public function __construct() {
        $this->configModel = new Configuracao();
        $this->logModel    = new Log();
    }

    public function index(): void {
        Security::requireAuth();
        if (!Security::hasPermission('acessar_configuracoes')) {
            $_SESSION['flash_error'] = 'Sem permissão para acessar configurações.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $config  = $this->configModel->getAll();
        $error   = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        include VIEWS_PATH . '/settings/index.php';
    }

    public function save(): void {
        Security::requireAuth();
        if (!Security::hasPermission('acessar_configuracoes')) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/configuracoes');
            exit;
        }

        $allowed = [
            'site_nome', 'cor_primaria', 'cor_secundaria',
            'smtp_host', 'smtp_porta', 'smtp_usuario', 'smtp_senha',
            'smtp_from', 'smtp_from_nome',
            'api_cnpj_url', 'rate_limit_consultas', 'sessao_tempo',
            'notif_email_admin',
        ];

        $data = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $data[$key] = trim($_POST[$key]);
            }
        }

        // Upload de logo
        if (!empty($_FILES['site_logo']['tmp_name'])) {
            $logoPath = $this->handleLogoUpload($_FILES['site_logo']);
            if ($logoPath) {
                $data['site_logo'] = $logoPath;
            }
        }

        $this->configModel->saveMany($data);
        $this->logModel->record('CONFIG_ATUALIZADA', 'Configurações do sistema atualizadas');

        $_SESSION['flash_success'] = 'Configurações salvas com sucesso.';
        header('Location: ' . APP_URL . '/configuracoes');
        exit;
    }

    public function backup(): void {
        Security::requireAdmin();

        $db   = Database::getInstance();
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sql  = "-- Alpha Conexão - Backup " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $create = $db->query("SHOW CREATE TABLE `$table`")->fetch();
            $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $create['Create Table'] . ";\n\n";

            $rows = $db->query("SELECT * FROM `$table`")->fetchAll();
            foreach ($rows as $row) {
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $db->quote($v), $row);
                $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $vals) . ");\n";
            }
            $sql .= "\n";
        }

        $filename = 'backup_alpha_conexao_' . date('Ymd_His') . '.sql';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $sql;

        $this->logModel->record('BACKUP_REALIZADO', 'Backup do banco gerado');
        exit;
    }

    private function handleLogoUpload(array $file): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > UPLOAD_MAX_SIZE) return null;

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ALLOWED_IMG_TYPES)) return null;

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $ext;
        $dest     = UPLOAD_PATH . '/' . $filename;

        if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return APP_URL . '/assets/img/uploads/' . $filename;
        }
        return null;
    }
}
