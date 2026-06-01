<?php
// =============================================
// ALPHA CONEXÃO - Model: Consulta CNPJ
// =============================================

class Consulta {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO consultas (usuario_id, cnpj, razao_social, nome_fantasia, situacao, dados_json, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['usuario_id'],
            $data['cnpj'],
            $data['razao_social'] ?? null,
            $data['nome_fantasia'] ?? null,
            $data['situacao']     ?? null,
            isset($data['dados_json']) ? json_encode($data['dados_json'], JSON_UNESCAPED_UNICODE) : null,
            $data['ip_address']   ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $where[] = 'c.usuario_id = ?';
            $params[] = $filters['usuario_id'];
        }
        if (!empty($filters['cnpj'])) {
            $where[] = 'c.cnpj LIKE ?';
            $params[] = '%' . preg_replace('/\D/', '', $filters['cnpj']) . '%';
        }
        if (!empty($filters['data_inicio'])) {
            $where[] = 'DATE(c.criado_em) >= ?';
            $params[] = $filters['data_inicio'];
        }
        if (!empty($filters['data_fim'])) {
            $where[] = 'DATE(c.criado_em) <= ?';
            $params[] = $filters['data_fim'];
        }

        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT c.id, c.cnpj, c.razao_social, c.nome_fantasia, c.situacao,
                       c.ip_address, c.criado_em, u.nome AS usuario_nome, u.email AS usuario_email
                FROM consultas c
                JOIN usuarios u ON u.id = c.usuario_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY c.criado_em DESC
                LIMIT ? OFFSET ?';

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $where[] = 'c.usuario_id = ?';
            $params[] = $filters['usuario_id'];
        }
        if (!empty($filters['cnpj'])) {
            $where[] = 'c.cnpj LIKE ?';
            $params[] = '%' . preg_replace('/\D/', '', $filters['cnpj']) . '%';
        }
        if (!empty($filters['data_inicio'])) {
            $where[] = 'DATE(c.criado_em) >= ?';
            $params[] = $filters['data_inicio'];
        }
        if (!empty($filters['data_fim'])) {
            $where[] = 'DATE(c.criado_em) <= ?';
            $params[] = $filters['data_fim'];
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM consultas c WHERE ' . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function countTotal(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM consultas')->fetchColumn();
    }

    public function countMonth(): int {
        return (int)$this->db->query(
            'SELECT COUNT(*) FROM consultas WHERE MONTH(criado_em)=MONTH(NOW()) AND YEAR(criado_em)=YEAR(NOW())'
        )->fetchColumn();
    }

    public function getRecentByUser(int $userId, int $limit = 10): array {
        $stmt = $this->db->prepare(
            'SELECT cnpj, razao_social, situacao, criado_em
             FROM consultas WHERE usuario_id=? ORDER BY criado_em DESC LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getDailyStats(int $days = 30): array {
        $stmt = $this->db->prepare(
            'SELECT DATE(criado_em) AS dia, COUNT(*) AS total
             FROM consultas
             WHERE criado_em >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(criado_em) ORDER BY dia ASC'
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getMonthlyStats(int $months = 12): array {
        $stmt = $this->db->prepare(
            'SELECT YEAR(criado_em) AS ano, MONTH(criado_em) AS mes, COUNT(*) AS total
             FROM consultas
             WHERE criado_em >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY YEAR(criado_em), MONTH(criado_em) ORDER BY ano, mes'
        );
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 10): array {
        $stmt = $this->db->prepare(
            'SELECT c.cnpj, c.razao_social, c.situacao, c.criado_em, u.nome AS usuario_nome
             FROM consultas c JOIN usuarios u ON u.id=c.usuario_id
             ORDER BY c.criado_em DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
