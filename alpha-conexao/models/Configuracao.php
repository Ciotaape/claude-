<?php
// =============================================
// ALPHA CONEXÃO - Model: Configurações
// =============================================

class Configuracao {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function get(string $chave, mixed $default = null): mixed {
        $stmt = $this->db->prepare('SELECT valor FROM configuracoes WHERE chave=? LIMIT 1');
        $stmt->execute([$chave]);
        $row = $stmt->fetch();
        return $row ? $row['valor'] : $default;
    }

    public function set(string $chave, mixed $valor): void {
        $this->db->prepare(
            'INSERT INTO configuracoes (chave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor=VALUES(valor)'
        )->execute([$chave, $valor]);
    }

    public function getAll(): array {
        $rows = $this->db->query('SELECT chave, valor FROM configuracoes')->fetchAll();
        $config = [];
        foreach ($rows as $row) $config[$row['chave']] = $row['valor'];
        return $config;
    }

    public function saveMany(array $data): void {
        foreach ($data as $chave => $valor) {
            $this->set($chave, $valor);
        }
    }
}
