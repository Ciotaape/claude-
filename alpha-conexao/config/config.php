<?php
// =============================================
// ALPHA CONEXÃO - Configurações Gerais
// =============================================

define('APP_NAME', 'Alpha Conexão');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/alpha-conexao'); // Alterar para URL de produção
define('APP_ROOT', dirname(__DIR__));
define('VIEWS_PATH', APP_ROOT . '/views');

// Segurança
define('SESSION_LIFETIME', 3600); // 1 hora
define('CSRF_TOKEN_LIFETIME', 3600);
define('BCRYPT_COST', 12);
define('RATE_LIMIT_WINDOW', 3600); // 1 hora
define('RATE_LIMIT_MAX', 60); // máx. consultas por janela

// API Open CNPJ
define('API_CNPJ_BASE', 'https://publica.cnpj.ws/cnpj/');
define('API_TIMEOUT', 15);

// Upload
define('UPLOAD_MAX_SIZE', 2097152); // 2MB
define('UPLOAD_PATH', APP_ROOT . '/assets/img/uploads');
define('ALLOWED_IMG_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Admin principal
define('ADMIN_EMAIL', 'tacio.cleiner19@gmail.com');

// Fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Modo de debug (false em produção)
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', APP_ROOT . '/logs/php_errors.log');
}

// Inicialização de sessão segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}
