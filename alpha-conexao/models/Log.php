<?php
// =============================================
// ALPHA CONEXÃO - Model: Logs de Auditoria
// =============================================

class Log {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function record(string $acao, string $descricao = '', ?int $usuarioId = null): void {
        $this->db->prepare(
            'INSERT INTO logs (usuario_id, acao, descricao, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $usuarioId ?? ($_SESSION['usuario_id'] ?? null),
            $acao,
            $descricao,
            Security::getIp(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 30): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $where[] = 'l.usuario_id = ?';
            $params[] = $filters['usuario_id'];
        }
        if (!empty($filters['acao'])) {
            $where[] = 'l.acao = ?';
            $params[] = $filters['acao'];
        }
        if (!empty($filters['data_inicio'])) {
            $where[] = 'DATE(l.criado_em) >= ?';
            $params[] = $filters['data_inicio'];
        }
        if (!empty($filters['data_fim'])) {
            $where[] = 'DATE(l.criado_em) <= ?';
            $params[] = $filters['data_fim'];
        }

        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT l.*, u.nome AS usuario_nome, u.email AS usuario_email
                FROM logs l
                LEFT JOIN usuarios u ON u.id = l.usuario_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY l.criado_em DESC
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

        if (!empty($filters['usuario_id'])) { $where[] = 'l.usuario_id=?'; $params[] = $filters['usuario_id']; }
        if (!empty($filters['acao']))        { $where[] = 'l.acao=?';       $params[] = $filters['acao']; }
        if (!empty($filters['data_inicio'])) { $where[] = 'DATE(l.criado_em)>=?'; $params[] = $filters['data_inicio']; }
        if (!empty($filters['data_fim']))    { $where[] = 'DATE(l.criado_em)<=?'; $params[] = $filters['data_fim']; }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM logs l WHERE ' . implode(' AND ', $where));
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getDistinctAcoes(): array {
        return $this->db->query('SELECT DISTINCT acao FROM logs ORDER BY acao')->fetchAll(PDO::FETCH_COLUMN);
    }
}
