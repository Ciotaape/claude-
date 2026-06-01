<?php
$pageTitle  = 'Configurações';
$activePage = 'configuracoes';
include VIEWS_PATH . '/layouts/header.php';
?>

<div class="ac-page-header">
    <h2><i class="fas fa-cog"></i> Configurações do Sistema</h2>
</div>

<?php if ($error): ?>
<div class="ac-alert ac-alert-error"><i class="fas fa-exclamation-circle"></i> <?= Security::escape($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="ac-alert ac-alert-success"><i class="fas fa-check-circle"></i> <?= Security::escape($success) ?></div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/configuracoes/salvar" enctype="multipart/form-data">
    <?= Security::csrfField() ?>

    <!-- Identidade Visual -->
    <div class="ac-card">
        <div class="ac-card-header"><h3><i class="fas fa-palette"></i> Identidade Visual</h3></div>
        <div class="ac-card-body ac-settings-grid">
            <div class="ac-form-group">
                <label>Nome do Sistema</label>
                <input type="text" name="site_nome" class="ac-input"
                       value="<?= Security::escape($config['site_nome'] ?? 'Alpha Conexão') ?>">
            </div>
            <div class="ac-form-group">
                <label>Logotipo (PNG/JPG até 2MB)</label>
                <?php if (!empty($config['site_logo'])): ?>
                <img src="<?= Security::escape($config['site_logo']) ?>" alt="Logo atual" class="ac-current-logo">
                <?php endif; ?>
                <input type="file" name="site_logo" class="ac-input" accept="image/*">
            </div>
            <div class="ac-form-group">
                <label>Cor Primária</label>
                <div class="ac-color-wrap">
                    <input type="color" name="cor_primaria" class="ac-color-input"
                           value="<?= Security::escape($config['cor_primaria'] ?? '#0F172A') ?>">
                    <input type="text"  name="cor_primaria_hex" class="ac-input ac-color-text"
                           value="<?= Security::escape($config['cor_primaria'] ?? '#0F172A') ?>" pattern="#[0-9a-fA-F]{6}" placeholder="#0F172A">
                </div>
            </div>
            <div class="ac-form-group">
                <label>Cor Secundária (Destaque)</label>
                <div class="ac-color-wrap">
                    <input type="color" name="cor_secundaria" class="ac-color-input"
                           value="<?= Security::escape($config['cor_secundaria'] ?? '#2563EB') ?>">
                    <input type="text"  name="cor_secundaria_hex" class="ac-input ac-color-text"
                           value="<?= Security::escape($config['cor_secundaria'] ?? '#2563EB') ?>" pattern="#[0-9a-fA-F]{6}" placeholder="#2563EB">
                </div>
            </div>
        </div>
    </div>

    <!-- Configurações de API -->
    <div class="ac-card">
        <div class="ac-card-header"><h3><i class="fas fa-plug"></i> API Open CNPJ</h3></div>
        <div class="ac-card-body ac-settings-grid">
            <div class="ac-form-group">
                <label>URL Base da API</label>
                <input type="url" name="api_cnpj_url" class="ac-input"
                       value="<?= Security::escape($config['api_cnpj_url'] ?? 'https://publica.cnpj.ws/cnpj/') ?>">
                <small class="ac-hint">Padrão: https://publica.cnpj.ws/cnpj/</small>
            </div>
            <div class="ac-form-group">
                <label>Limite de consultas por hora (por usuário)</label>
                <input type="number" name="rate_limit_consultas" class="ac-input" min="1" max="500"
                       value="<?= Security::escape($config['rate_limit_consultas'] ?? '60') ?>">
            </div>
        </div>
    </div>

    <!-- SMTP -->
    <div class="ac-card">
        <div class="ac-card-header"><h3><i class="fas fa-envelope"></i> Configurações de E-mail (SMTP)</h3></div>
        <div class="ac-card-body ac-settings-grid">
            <div class="ac-form-group">
                <label>Servidor SMTP</label>
                <input type="text" name="smtp_host" class="ac-input" placeholder="smtp.gmail.com"
                       value="<?= Security::escape($config['smtp_host'] ?? '') ?>">
            </div>
            <div class="ac-form-group">
                <label>Porta</label>
                <input type="number" name="smtp_porta" class="ac-input" placeholder="587"
                       value="<?= Security::escape($config['smtp_porta'] ?? '587') ?>">
            </div>
            <div class="ac-form-group">
                <label>Usuário SMTP</label>
                <input type="text" name="smtp_usuario" class="ac-input" placeholder="usuario@email.com"
                       value="<?= Security::escape($config['smtp_usuario'] ?? '') ?>">
            </div>
            <div class="ac-form-group">
                <label>Senha SMTP</label>
                <input type="password" name="smtp_senha" class="ac-input" placeholder="••••••••"
                       value="<?= Security::escape($config['smtp_senha'] ?? '') ?>">
            </div>
            <div class="ac-form-group">
                <label>E-mail Remetente</label>
                <input type="email" name="smtp_from" class="ac-input" placeholder="noreply@empresa.com"
                       value="<?= Security::escape($config['smtp_from'] ?? '') ?>">
            </div>
            <div class="ac-form-group">
                <label>Nome do Remetente</label>
                <input type="text" name="smtp_from_nome" class="ac-input" placeholder="Alpha Conexão"
                       value="<?= Security::escape($config['smtp_from_nome'] ?? 'Alpha Conexão') ?>">
            </div>
            <div class="ac-form-group">
                <label>E-mail do Administrador (notificações)</label>
                <input type="email" name="notif_email_admin" class="ac-input"
                       value="<?= Security::escape($config['notif_email_admin'] ?? ADMIN_EMAIL) ?>">
            </div>
        </div>
    </div>

    <div class="ac-form-actions">
        <button type="submit" class="ac-btn ac-btn-primary ac-btn-lg">
            <i class="fas fa-save"></i> Salvar Configurações
        </button>
        <?php if ($_SESSION['usuario_role'] === 'admin'): ?>
        <a href="<?= APP_URL ?>/configuracoes/backup" class="ac-btn ac-btn-outline ac-btn-lg">
            <i class="fas fa-database"></i> Backup do Banco
        </a>
        <?php endif; ?>
    </div>
</form>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
