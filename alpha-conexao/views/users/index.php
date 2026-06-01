<?php
$pageTitle  = 'Gerenciar Usuários';
$activePage = 'usuarios';
include VIEWS_PATH . '/layouts/header.php';
?>

<div class="ac-page-header">
    <h2><i class="fas fa-users"></i> Gerenciar Usuários</h2>
    <p>Controle de acesso e permissões</p>
</div>

<?php if ($error): ?>
<div class="ac-alert ac-alert-error"><i class="fas fa-exclamation-circle"></i> <?= Security::escape($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="ac-alert ac-alert-success"><i class="fas fa-check-circle"></i> <?= Security::escape($success) ?></div>
<?php endif; ?>

<!-- Filtros -->
<div class="ac-card ac-filter-card">
    <div class="ac-card-body">
        <form method="GET" action="<?= APP_URL ?>/usuarios" class="ac-filter-form">
            <div class="ac-filter-grid">
                <div class="ac-form-group">
                    <label>Buscar</label>
                    <input type="text" name="search" class="ac-input" placeholder="Nome ou e-mail..."
                           value="<?= Security::escape($_GET['search'] ?? '') ?>">
                </div>
                <div class="ac-form-group">
                    <label>Status</label>
                    <select name="status" class="ac-input">
                        <option value="">Todos</option>
                        <option value="ativo"      <?= ($_GET['status'] ?? '') === 'ativo'      ? 'selected' : '' ?>>Ativo</option>
                        <option value="pendente"   <?= ($_GET['status'] ?? '') === 'pendente'   ? 'selected' : '' ?>>Pendente</option>
                        <option value="bloqueado"  <?= ($_GET['status'] ?? '') === 'bloqueado'  ? 'selected' : '' ?>>Bloqueado</option>
                    </select>
                </div>
                <div class="ac-filter-actions">
                    <button type="submit" class="ac-btn ac-btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                    <a href="<?= APP_URL ?>/usuarios" class="ac-btn ac-btn-outline"><i class="fas fa-times"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de usuários -->
<div class="ac-card">
    <div class="ac-card-body ac-table-wrap">
        <?php if (empty($usuarios)): ?>
        <div class="ac-empty"><i class="fas fa-users-slash"></i><p>Nenhum usuário encontrado.</p></div>
        <?php else: ?>
        <table class="ac-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Último acesso</th>
                    <th>Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td>
                        <span class="ac-avatar-sm"><?= strtoupper(substr($u['nome'], 0, 1)) ?></span>
                        <?= Security::escape($u['nome']) ?>
                    </td>
                    <td><?= Security::escape($u['email']) ?></td>
                    <td>
                        <span class="ac-badge <?= $u['role'] === 'admin' ? 'ac-badge-blue' : 'ac-badge-gray' ?>">
                            <?= $u['role'] === 'admin' ? 'Admin' : 'Usuário' ?>
                        </span>
                    </td>
                    <td>
                        <span class="ac-badge
                            <?= $u['status'] === 'ativo' ? 'ac-badge-green' :
                               ($u['status'] === 'pendente' ? 'ac-badge-orange' : 'ac-badge-red') ?>">
                            <?= ucfirst($u['status']) ?>
                        </span>
                    </td>
                    <td><?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : '–' ?></td>
                    <td><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
                    <td class="ac-actions">
                        <?php if ($u['role'] !== 'admin'): ?>

                        <?php if ($u['status'] === 'pendente'): ?>
                        <form method="POST" action="<?= APP_URL ?>/usuarios/aprovar/<?= $u['id'] ?>" class="ac-inline-form">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="ac-btn ac-btn-xs ac-btn-success" title="Aprovar">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($u['status'] !== 'bloqueado'): ?>
                        <form method="POST" action="<?= APP_URL ?>/usuarios/bloquear/<?= $u['id'] ?>" class="ac-inline-form">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="ac-btn ac-btn-xs ac-btn-warning" title="Bloquear"
                                    onclick="return confirm('Bloquear <?= Security::escape($u['nome']) ?>?')">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        <?php endif; ?>

                        <button class="ac-btn ac-btn-xs ac-btn-info" title="Permissões"
                                onclick="openPermModal(<?= $u['id'] ?>, '<?= Security::escape($u['nome']) ?>')">
                            <i class="fas fa-shield-alt"></i>
                        </button>

                        <form method="POST" action="<?= APP_URL ?>/usuarios/excluir/<?= $u['id'] ?>" class="ac-inline-form">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="ac-btn ac-btn-xs ac-btn-danger" title="Excluir"
                                    onclick="return confirm('Excluir permanentemente <?= Security::escape($u['nome']) ?>?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                        <?php else: ?>
                        <span class="ac-muted">Admin principal</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Permissões -->
<div class="ac-modal" id="permModal">
    <div class="ac-modal-overlay" onclick="closePermModal()"></div>
    <div class="ac-modal-box">
        <div class="ac-modal-header">
            <h3><i class="fas fa-shield-alt"></i> Permissões – <span id="permUserName"></span></h3>
            <button class="ac-modal-close" onclick="closePermModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="permForm" class="ac-modal-body">
            <?= Security::csrfField() ?>
            <div class="ac-perm-list">
                <label class="ac-switch-label">
                    <span>Consultar CNPJ</span>
                    <label class="ac-switch"><input type="checkbox" name="consultar_cnpj" id="p_consultar_cnpj"><span class="ac-slider"></span></label>
                </label>
                <label class="ac-switch-label">
                    <span>Ver Histórico</span>
                    <label class="ac-switch"><input type="checkbox" name="ver_historico" id="p_ver_historico"><span class="ac-slider"></span></label>
                </label>
                <label class="ac-switch-label">
                    <span>Ver Logs</span>
                    <label class="ac-switch"><input type="checkbox" name="ver_logs" id="p_ver_logs"><span class="ac-slider"></span></label>
                </label>
                <label class="ac-switch-label">
                    <span>Gerenciar Usuários</span>
                    <label class="ac-switch"><input type="checkbox" name="gerenciar_usuarios" id="p_gerenciar_usuarios"><span class="ac-slider"></span></label>
                </label>
                <label class="ac-switch-label">
                    <span>Acessar Configurações</span>
                    <label class="ac-switch"><input type="checkbox" name="acessar_configuracoes" id="p_acessar_configuracoes"><span class="ac-slider"></span></label>
                </label>
                <label class="ac-switch-label">
                    <span>Exportar Dados</span>
                    <label class="ac-switch"><input type="checkbox" name="exportar_dados" id="p_exportar_dados"><span class="ac-slider"></span></label>
                </label>
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-outline" onclick="closePermModal()">Cancelar</button>
                <button type="submit" class="ac-btn ac-btn-primary"><i class="fas fa-save"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php
// Preparar dados de permissões para JS
$permsData = [];
foreach ($usuarios as $u) {
    $full = (new User())->findById($u['id']);
    $permsData[$u['id']] = [
        'consultar_cnpj'       => (bool)($full['consultar_cnpj'] ?? false),
        'ver_historico'        => (bool)($full['ver_historico'] ?? false),
        'ver_logs'             => (bool)($full['ver_logs'] ?? false),
        'gerenciar_usuarios'   => (bool)($full['gerenciar_usuarios'] ?? false),
        'acessar_configuracoes'=> (bool)($full['acessar_configuracoes'] ?? false),
        'exportar_dados'       => (bool)($full['exportar_dados'] ?? false),
    ];
}
?>

<script>
const permsData = <?= json_encode($permsData) ?>;
const appUrl    = '<?= APP_URL ?>';

function openPermModal(userId, userName) {
    document.getElementById('permUserName').textContent = userName;
    document.getElementById('permForm').action = appUrl + '/usuarios/permissoes/' + userId;
    const p = permsData[userId] || {};
    ['consultar_cnpj','ver_historico','ver_logs','gerenciar_usuarios','acessar_configuracoes','exportar_dados']
        .forEach(k => { const el = document.getElementById('p_'+k); if (el) el.checked = !!p[k]; });
    document.getElementById('permModal').classList.add('active');
}
function closePermModal() {
    document.getElementById('permModal').classList.remove('active');
}
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
