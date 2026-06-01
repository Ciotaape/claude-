<?php
// =============================================
// ALPHA CONEXÃO – Front Controller
// =============================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/lib/Security.php';
require_once __DIR__ . '/lib/Router.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Consulta.php';
require_once __DIR__ . '/models/Log.php';
require_once __DIR__ . '/models/Configuracao.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/CnpjController.php';
require_once __DIR__ . '/controllers/HistoryController.php';
require_once __DIR__ . '/controllers/LogController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/SettingsController.php';

// Verifica cookie "lembrar acesso"
(new AuthController())->checkRememberMe();

$router = new Router();

// ── Autenticação ──────────────────────────────
$router->get( '/login',           fn() => (new AuthController())->showLogin());
$router->post('/login',           fn() => (new AuthController())->login());
$router->get( '/register',        fn() => (new AuthController())->showRegister());
$router->post('/register',        fn() => (new AuthController())->register());
$router->get( '/logout',          fn() => (new AuthController())->logout());
$router->get( '/forgot-password', fn() => (new AuthController())->showForgotPassword());
$router->post('/forgot-password', fn() => (new AuthController())->forgotPassword());
$router->get( '/reset-password/{token}', fn($t) => (new AuthController())->showResetPassword($t));
$router->post('/reset-password/{token}', fn($t) => (new AuthController())->resetPassword($t));

// ── Dashboard ─────────────────────────────────
$router->get('/',          fn() => (new DashboardController())->index());
$router->get('/dashboard', fn() => (new DashboardController())->index());

// ── CNPJ ──────────────────────────────────────
$router->get( '/cnpj',         fn() => (new CnpjController())->index());
$router->post('/cnpj/buscar',  fn() => (new CnpjController())->search());
$router->get( '/cnpj/api',     fn() => (new CnpjController())->apiSearch());

// ── Histórico ─────────────────────────────────
$router->get('/historico',          fn() => (new HistoryController())->index());
$router->get('/historico/exportar', fn() => (new HistoryController())->export());

// ── Logs ──────────────────────────────────────
$router->get('/logs', fn() => (new LogController())->index());

// ── Usuários ──────────────────────────────────
$router->get( '/usuarios',                       fn() => (new UserController())->index());
$router->post('/usuarios/aprovar/{id}',          fn($id) => (new UserController())->approve((int)$id));
$router->post('/usuarios/bloquear/{id}',         fn($id) => (new UserController())->block((int)$id));
$router->post('/usuarios/excluir/{id}',          fn($id) => (new UserController())->delete((int)$id));
$router->post('/usuarios/permissoes/{id}',       fn($id) => (new UserController())->updatePermissions((int)$id));

// ── Configurações ─────────────────────────────
$router->get( '/configuracoes',         fn() => (new SettingsController())->index());
$router->post('/configuracoes/salvar',  fn() => (new SettingsController())->save());
$router->get( '/configuracoes/backup',  fn() => (new SettingsController())->backup());

$router->dispatch();
