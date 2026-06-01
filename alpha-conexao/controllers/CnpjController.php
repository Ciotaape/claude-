<?php
// =============================================
// ALPHA CONEXÃO - Controller: Consulta CNPJ
// =============================================

class CnpjController {
    private Consulta $consultaModel;
    private Log      $logModel;

    public function __construct() {
        $this->consultaModel = new Consulta();
        $this->logModel      = new Log();
    }

    /** Exibe formulário de consulta */
    public function index(): void {
        Security::requireAuth();
        if (!Security::hasPermission('consultar_cnpj')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para consultar CNPJs.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        $resultado   = null;
        $error       = $_SESSION['flash_error'] ?? null;
        $cnpjDigitado= '';
        unset($_SESSION['flash_error']);
        include VIEWS_PATH . '/cnpj/search.php';
    }

    /** Processa consulta via formulário */
    public function search(): void {
        Security::requireAuth();

        if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . APP_URL . '/cnpj');
            exit;
        }

        if (!Security::hasPermission('consultar_cnpj')) {
            $_SESSION['flash_error'] = 'Sem permissão para consultar.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $cnpjRaw = $_POST['cnpj'] ?? '';
        $cnpj    = Security::onlyDigits($cnpjRaw);

        if (!Security::validateCnpj($cnpj)) {
            $_SESSION['flash_error'] = 'CNPJ inválido. Verifique e tente novamente.';
            header('Location: ' . APP_URL . '/cnpj');
            exit;
        }

        // Rate limit: max 60 consultas por hora por usuário
        $key = 'cnpj_' . ($_SESSION['usuario_id'] ?? 0);
        if (!Security::checkRateLimit($key, RATE_LIMIT_MAX, RATE_LIMIT_WINDOW)) {
            $_SESSION['flash_error'] = 'Limite de consultas atingido. Tente novamente em 1 hora.';
            header('Location: ' . APP_URL . '/cnpj');
            exit;
        }

        $resultado    = $this->fetchFromApi($cnpj);
        $cnpjDigitado = Security::formatCnpj($cnpj);
        $error        = null;

        if (isset($resultado['error'])) {
            $error     = $resultado['error'];
            $resultado = null;
            $this->logModel->record('CONSULTA_ERRO', "CNPJ: $cnpj | Erro: $error");
        } else {
            // Salva no histórico
            $this->consultaModel->save([
                'usuario_id'   => $_SESSION['usuario_id'],
                'cnpj'         => $cnpj,
                'razao_social' => $resultado['razao_social'] ?? null,
                'nome_fantasia'=> $resultado['nome_fantasia'] ?? null,
                'situacao'     => $resultado['descricao_situacao_cadastral'] ?? null,
                'dados_json'   => $resultado,
                'ip_address'   => Security::getIp(),
            ]);
            $this->logModel->record('CONSULTA_CNPJ', "CNPJ consultado: $cnpj");
        }

        include VIEWS_PATH . '/cnpj/search.php';
    }

    /** API AJAX – retorna JSON */
    public function apiSearch(): void {
        Security::requireAuth();
        header('Content-Type: application/json; charset=utf-8');

        if (!Security::hasPermission('consultar_cnpj')) {
            echo json_encode(['error' => 'Sem permissão.']);
            exit;
        }

        $cnpj = Security::onlyDigits($_GET['cnpj'] ?? '');

        if (!Security::validateCnpj($cnpj)) {
            echo json_encode(['error' => 'CNPJ inválido.']);
            exit;
        }

        $key = 'cnpj_' . ($_SESSION['usuario_id'] ?? 0);
        if (!Security::checkRateLimit($key, RATE_LIMIT_MAX, RATE_LIMIT_WINDOW)) {
            echo json_encode(['error' => 'Rate limit atingido.']);
            exit;
        }

        $data = $this->fetchFromApi($cnpj);

        if (!isset($data['error'])) {
            $this->consultaModel->save([
                'usuario_id'   => $_SESSION['usuario_id'],
                'cnpj'         => $cnpj,
                'razao_social' => $data['razao_social'] ?? null,
                'nome_fantasia'=> $data['nome_fantasia'] ?? null,
                'situacao'     => $data['descricao_situacao_cadastral'] ?? null,
                'dados_json'   => $data,
                'ip_address'   => Security::getIp(),
            ]);
            $this->logModel->record('CONSULTA_CNPJ', "CNPJ: $cnpj (API)");
        }

        echo json_encode($data);
        exit;
    }

    /** Consulta a API Open CNPJ */
    private function fetchFromApi(string $cnpj): array {
        $url = rtrim(API_CNPJ_BASE, '/') . '/' . $cnpj;

        $ctx = stream_context_create([
            'http' => [
                'timeout'        => API_TIMEOUT,
                'ignore_errors'  => true,
                'header'         => "User-Agent: AlphaConexao/1.0\r\nAccept: application/json\r\n",
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);

        if ($response === false) {
            return ['error' => 'Não foi possível conectar à API. Verifique sua conexão.'];
        }

        // Verifica código HTTP
        $httpCode = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('/^HTTP\/\d+\.?\d*\s+(\d+)/', $h, $m)) {
                    $httpCode = (int)$m[1];
                }
            }
        }

        if ($httpCode === 429) {
            return ['error' => 'API temporariamente indisponível (rate limit externo). Tente em instantes.'];
        }
        if ($httpCode === 404) {
            return ['error' => 'CNPJ não encontrado na base da Receita Federal.'];
        }
        if ($httpCode !== 200) {
            return ['error' => "Erro na API (HTTP $httpCode). Tente novamente."];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Resposta inválida da API.'];
        }

        return $data;
    }
}
