<?php
$pageTitle  = 'Logs de Auditoria';
$activePage = 'logs';
include VIEWS_PATH . '/layouts/header.php';
?>

<div class="ac-page-header">
    <h2><i class="fas fa-clipboard-list"></i> Logs de Auditoria</h2>
    <p>Total: <strong><?= number_format($total) ?></strong> eventos</p>
</div>

<!-- Filtros -->
<div class="ac-card ac-filter-card">
    <div class="ac-card-body">
        <form method="GET" action="<?= APP_URL ?>/logs" class="ac-filter-form">
            <div class="ac-filter-grid">
                <div class="ac-form-group">
                    <label>Ação</label>
                    <select name="acao" class="ac-input">
                        <option value="">Todas</option>
                        <?php foreach ($acoes as $a): ?>
                        <option value="<?= Security::escape($a) ?>" <?= ($_GET['acao'] ?? '') === $a ? 'selected' : '' ?>>
                            <?= Security::escape($a) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ac-form-group">
                    <label>Data início</label>
                    <input type="date" name="data_inicio" class="ac-input" value="<?= Security::escape($_GET['data_inicio'] ?? '') ?>">
                </div>
                <div class="ac-form-group">
                    <label>Data fim</label>
                    <input type="date" name="data_fim" class="ac-input" value="<?= Security::escape($_GET['data_fim'] ?? '') ?>">
                </div>
                <div class="ac-filter-actions">
                    <button type="submit" class="ac-btn ac-btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                    <a href="<?= APP_URL ?>/logs" class="ac-btn ac-btn-outline"><i class="fas fa-times"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabela -->
<div class="ac-card">
    <div class="ac-card-body ac-table-wrap">
        <?php if (empty($logs)): ?>
        <div class="ac-empty"><i class="fas fa-inbox"></i><p>Nenhum log encontrado.</p></div>
        <?php else: ?>
        <table class="ac-table ac-table-sm">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Descrição</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l):
                    $acaoCls = match(true) {
                        str_contains($l['acao'], 'LOGIN')    => 'ac-badge-blue',
                        str_contains($l['acao'], 'LOGOUT')   => 'ac-badge-gray',
                        str_contains($l['acao'], 'CONSULTA') => 'ac-badge-green',
                        str_contains($l['acao'], 'ERRO')     => 'ac-badge-red',
                        str_contains($l['acao'], 'EXCLUIDO') => 'ac-badge-red',
                        str_contains($l['acao'], 'BLOQUEADO')=> 'ac-badge-orange',
                        default                              => 'ac-badge-gray',
                    };
                ?>
                <tr>
                    <td><small><?= date('d/m/Y H:i:s', strtotime($l['criado_em'])) ?></small></td>
                    <td>
                        <?php if ($l['usuario_nome']): ?>
                        <small><?= Security::escape($l['usuario_nome']) ?></small>
                        <?php else: ?>
                        <span class="ac-muted">–</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="ac-badge <?= $acaoCls ?>"><?= Security::escape($l['acao']) ?></span></td>
                    <td><small><?= Security::escape($l['descricao'] ?? '–') ?></small></td>
                    <td><small><?= Security::escape($l['ip_address'] ?? '–') ?></small></td>
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
    for ($p = 1; $p <= min($pages, 20); $p++):
        $q['page'] = $p;
    ?>
    <a href="<?= APP_URL ?>/logs?<?= http_build_query($q) ?>"
       class="ac-page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
