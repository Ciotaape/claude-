<aside class="ac-sidebar" id="acSidebar">
    <div class="ac-sidebar-header">
        <?php
        $config = $config ?? [];
        $logo   = $config['site_logo'] ?? '';
        $nome   = $config['site_nome'] ?? APP_NAME;
        ?>
        <?php if ($logo): ?>
            <img src="<?= Security::escape($logo) ?>" alt="Logo" class="ac-logo-img">
        <?php else: ?>
            <div class="ac-logo-text">
                <i class="fas fa-building"></i>
                <span><?= Security::escape($nome) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <nav class="ac-nav">
        <ul>
            <li class="ac-nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <a href="<?= APP_URL ?>/dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if (Security::hasPermission('consultar_cnpj')): ?>
            <li class="ac-nav-item <?= ($activePage ?? '') === 'cnpj' ? 'active' : '' ?>">
                <a href="<?= APP_URL ?>/cnpj">
                    <i class="fas fa-search"></i>
                    <span>Consultar CNPJ</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (Security::hasPermission('ver_historico')): ?>
            <li class="ac-nav-item <?= ($activePage ?? '') === 'historico' ? 'active' : '' ?>">
                <a href="<?= APP_URL ?>/historico">
                    <i class="fas fa-history"></i>
                    <span>Histórico</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($_SESSION['usuario_role'] === 'admin'): ?>
            <li class="ac-nav-section">Administração</li>

            <li class="ac-nav-item <?= ($activePage ?? '') === 'usuarios' ? 'active' : '' ?>">
                <a href="<?= APP_URL ?>/usuarios">
                    <i class="fas fa-users"></i>
                    <span>Usuários</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (Security::hasPermission('ver_logs')): ?>
            <li class="ac-nav-item <?= ($activePage ?? '') === 'logs' ? 'active' : '' ?>">
                <a href="<?= APP_URL ?>/logs">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Logs</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (Security::hasPermission('acessar_configuracoes')): ?>
            <li class="ac-nav-item <?= ($activePage ?? '') === 'configuracoes' ? 'active' : '' ?>">
                <a href="<?= APP_URL ?>/configuracoes">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="ac-sidebar-footer">
        <small>v<?= APP_VERSION ?></small>
    </div>
</aside>
