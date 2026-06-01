<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= Security::escape($pageTitle ?? 'Alpha Conexão') ?> | Alpha Conexão</title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/img/favicon.png">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
</head>
<body class="ac-body">

<div class="ac-wrapper">
    <!-- Sidebar -->
    <?php include VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ac-main" id="acMain">
        <!-- Top Bar -->
        <header class="ac-topbar">
            <button class="ac-sidebar-toggle" id="sidebarToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="ac-topbar-right">
                <?php if (!empty($usuarios_pendentes) && $_SESSION['usuario_role'] === 'admin'): ?>
                <a href="<?= APP_URL ?>/usuarios?status=pendente" class="ac-badge-alert" title="Usuários aguardando aprovação">
                    <i class="fas fa-user-clock"></i>
                    <span><?= (int)$usuarios_pendentes ?></span>
                </a>
                <?php endif; ?>

                <div class="ac-user-menu">
                    <button class="ac-user-btn" id="userMenuBtn">
                        <span class="ac-avatar"><?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)) ?></span>
                        <span class="ac-user-name"><?= Security::escape($_SESSION['usuario_nome'] ?? '') ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="ac-user-dropdown" id="userDropdown">
                        <div class="ac-dropdown-header">
                            <strong><?= Security::escape($_SESSION['usuario_nome'] ?? '') ?></strong>
                            <small><?= Security::escape($_SESSION['usuario_email'] ?? '') ?></small>
                        </div>
                        <a href="<?= APP_URL ?>/logout" class="ac-dropdown-item ac-logout">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="ac-alert ac-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= Security::escape($_SESSION['flash_error']) ?>
            <button class="ac-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="ac-alert ac-alert-success">
            <i class="fas fa-check-circle"></i>
            <?= Security::escape($_SESSION['flash_success']) ?>
            <button class="ac-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>

        <!-- Page Content -->
        <main class="ac-content">
