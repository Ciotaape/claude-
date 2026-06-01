<?php
// =============================================
// ALPHA CONEXÃO - Model: Usuário
// =============================================

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            'SELECT u.*, p.consultar_cnpj, p.ver_historico, p.ver_logs,
                    p.gerenciar_usuarios, p.acessar_configuracoes, p.exportar_dados
             FROM usuarios u
             LEFT JOIN permissoes p ON p.usuario_id = u.id
             WHERE u.email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT u.*, p.consultar_cnpj, p.ver_historico, p.ver_logs,
                    p.gerenciar_usuarios, p.acessar_configuracoes, p.exportar_dados
             FROM usuarios u
             LEFT JOIN permissoes p ON p.usuario_id = u.id
             WHERE u.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, email, senha, status, role) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['nome'],
            $data['email'],
            password_hash($data['senha'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            $data['status'] ?? 'pendente',
            $data['role']   ?? 'usuario',
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->createPermissions($id);
        return $id;
    }

    private function createPermissions(int $userId): void {
        $this->db->prepare(
            'INSERT INTO permissoes (usuario_id) VALUES (?)'
        )->execute([$userId]);
    }

    public function updatePermissions(int $userId, array $perms): bool {
        $stmt = $this->db->prepare(
            'UPDATE permissoes SET consultar_cnpj=?, ver_historico=?, ver_logs=?,
             gerenciar_usuarios=?, acessar_configuracoes=?, exportar_dados=?
             WHERE usuario_id=?'
        );
        return $stmt->execute([
            (int)($perms['consultar_cnpj'] ?? 0),
            (int)($perms['ver_historico'] ?? 0),
            (int)($perms['ver_logs'] ?? 0),
            (int)($perms['gerenciar_usuarios'] ?? 0),
            (int)($perms['acessar_configuracoes'] ?? 0),
            (int)($perms['exportar_dados'] ?? 0),
            $userId,
        ]);
    }

    public function updateStatus(int $id, string $status): bool {
        return $this->db->prepare('UPDATE usuarios SET status=? WHERE id=?')
            ->execute([$status, $id]);
    }

    public function updateLastLogin(int $id): void {
        $this->db->prepare('UPDATE usuarios SET ultimo_login=NOW() WHERE id=?')
            ->execute([$id]);
    }

    public function setRememberToken(int $id, string $token): void {
        $this->db->prepare('UPDATE usuarios SET remember_token=? WHERE id=?')
            ->execute([hash('sha256', $token), $id]);
    }

    public function findByRememberToken(string $token): ?array {
        $stmt = $this->db->prepare(
            'SELECT u.* FROM usuarios u WHERE u.remember_token=? AND u.status="ativo" LIMIT 1'
        );
        $stmt->execute([hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    }

    public function clearRememberToken(int $id): void {
        $this->db->prepare('UPDATE usuarios SET remember_token=NULL WHERE id=?')
            ->execute([$id]);
    }

    public function setResetToken(string $email): ?string {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET reset_token=?, reset_token_expiry=? WHERE email=? AND status="ativo"'
        );
        $stmt->execute([$token, $expiry, $email]);
        return $stmt->rowCount() > 0 ? $token : null;
    }

    public function findByResetToken(string $token): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE reset_token=? AND reset_token_expiry > NOW() LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function resetPassword(int $id, string $newPassword): void {
        $this->db->prepare(
            'UPDATE usuarios SET senha=?, reset_token=NULL, reset_token_expiry=NULL WHERE id=?'
        )->execute([password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]), $id]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare('DELETE FROM usuarios WHERE id=?')->execute([$id]);
    }

    public function getAll(array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'u.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(u.nome LIKE ? OR u.email LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql = 'SELECT u.id, u.nome, u.email, u.status, u.role, u.ultimo_login, u.criado_em
                FROM usuarios u
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY u.criado_em DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countActive(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM usuarios WHERE status="ativo"')->fetchColumn();
    }

    public function countPending(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM usuarios WHERE status="pendente"')->fetchColumn();
    }
}
