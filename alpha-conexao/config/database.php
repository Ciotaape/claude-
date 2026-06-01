<?php
// =============================================
// ALPHA CONEXÃO - Conexão com Banco de Dados
// =============================================

class Database {
    private static ?PDO $instance = null;

    private static array $config = [
        'host'    => 'localhost',
        'dbname'  => 'alpha_conexao',
        'user'    => 'root',       // Alterar para usuário de produção
        'pass'    => '',           // Alterar para senha de produção
        'charset' => 'utf8mb4',
    ];

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$config['host'],
                self::$config['dbname'],
                self::$config['charset']
            );
            self::$instance = new PDO($dsn, self::$config['user'], self::$config['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
