<?php
$pageTitle  = 'Histórico de Consultas';
$activePage = 'historico';
include VIEWS_PATH . '/layouts/header.php';
?>

<div class="ac-page-header">
    <h2><i class="fas fa-history"></i> Histórico de Consultas</h2>
    <p>Total: <strong><?= number_format($total) ?></strong> registros</p>
</div>

<!-- Filtros -->
<div class="ac-card ac-filter-card">
    <div class="ac-card-header"><h3><i class="fas fa-filter"></i> Filtros</h3></div>
    <div class="ac-card-body">
        <form method="GET" action="<?= APP_URL ?>/historico" class="ac-filter-form">
            <div class="ac-filter-grid">
                <div class="ac-form-group">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" class="ac-input" placeholder="Pesquisar CNPJ..."
                           value="<?= Security::escape($_GET['cnpj'] ?? '') ?>">
                </div>
                <div class="ac-form-group">
                    <label>Data início</label>
                    <input type="date" name="data_inicio" class="ac-input"
                           value="<?= Security::escape($_GET['data_inicio'] ?? '') ?>">
                </div>
                <div class="ac-form-group">
                    <label>Data fim</label>
                    <input type="date" name="data_fim" class="ac-input"
                           value="<?= Security::escape($_GET['data_fim'] ?? '') ?>">
                </div>
                <div class="ac-filter-actions">
                    <button type="submit" class="ac-btn ac-btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                    <a href="<?= APP_URL ?>/historico" class="ac-btn ac-btn-outline"><i class="fas fa-times"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Ações de Exportação -->
<?php if (Security::hasPermission('exportar_dados')): ?>
<div class="ac-export-bar">
    <span><i class="fas fa-download"></i> Exportar:</span>
    <a href="<?= APP_URL ?>/historico/exportar?<?= http_build_query(array_filter($_GET)) ?>&format=csv"
       class="ac-btn ac-btn-sm ac-btn-success">
        <i class="fas fa-file-csv"></i> CSV / Excel
    </a>
    <a href="<?= APP_URL ?>/historico/exportar?<?= http_build_query(array_filter($_GET)) ?>&format=pdf"
       class="ac-btn ac-btn-sm ac-btn-danger">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
</div>
<?php endif; ?>

<!-- Tabela -->
<div class="ac-card">
    <div class="ac-card-body ac-table-wrap">
        <?php if (empty($consultas)): ?>
        <div class="ac-empty">
            <i class="fas fa-inbox"></i>
            <p>Nenhuma consulta encontrada com os filtros informados.</p>
        </div>
        <?php else: ?>
        <table class="ac-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>CNPJ</th>
                    <th>Razão Social</th>
                    <th>Situação</th>
                    <?php if ($_SESSION['usuario_role'] === 'admin'): ?>
                    <th>Usuário</th>
                    <?php endif; ?>
                    <th>Data/Hora</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultas as $i => $c): ?>
                <tr>
                    <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                    <td><code><?= Security::escape(Security::formatCnpj($c['cnpj'])) ?></code></td>
                    <td><?= Security::escape($c['razao_social'] ?? '–') ?></td>
                    <td>
                        <span class="ac-badge <?= strtolower($c['situacao'] ?? '') === 'ativa' ? 'ac-badge-green' : 'ac-badge-gray' ?>">
                            <?= Security::escape($c['situacao'] ?? '–') ?>
                        </span>
                    </td>
                    <?php if ($_SESSION['usuario_role'] === 'admin'): ?>
                    <td><?= Security::escape($c['usuario_nome']) ?></td>
                    <?php endif; ?>
                    <td><?= date('d/m/Y H:i:s', strtotime($c['criado_em'])) ?></td>
                    <td><small><?= Security::escape($c['ip_address'] ?? '–') ?></small></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Paginação -->
<?php if ($pages > 1): ?>
<div class="ac-pagination">
    <?php
    $q = $_GET;
    for ($p = 1; $p <= $pages; $p++):
        $q['page'] = $p;
        $active    = $p === $page ? 'active' : '';
    ?>
    <a href="<?= APP_URL ?>/historico?<?= http_build_query($q) ?>" class="ac-page-btn <?= $active ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
