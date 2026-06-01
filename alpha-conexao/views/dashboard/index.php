<?php
$pageTitle    = 'Dashboard';
$activePage   = 'dashboard';
$usuarios_pendentes = $stats['usuarios_pendentes'] ?? 0;
include VIEWS_PATH . '/layouts/header.php';
?>

<div class="ac-page-header">
    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
    <p>Bem-vindo, <?= Security::escape($_SESSION['usuario_nome'] ?? '') ?>!</p>
</div>

<!-- Cards de Estatísticas -->
<div class="ac-stats-grid">
    <div class="ac-stat-card ac-stat-blue">
        <div class="ac-stat-icon"><i class="fas fa-search"></i></div>
        <div class="ac-stat-info">
            <h3><?= number_format($stats['total_consultas']) ?></h3>
            <p>Total de Consultas</p>
        </div>
    </div>

    <div class="ac-stat-card ac-stat-green">
        <div class="ac-stat-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="ac-stat-info">
            <h3><?= number_format($stats['consultas_mes']) ?></h3>
            <p>Consultas este mês</p>
        </div>
    </div>

    <div class="ac-stat-card ac-stat-purple">
        <div class="ac-stat-icon"><i class="fas fa-users"></i></div>
        <div class="ac-stat-info">
            <h3><?= number_format($stats['usuarios_ativos']) ?></h3>
            <p>Usuários Ativos</p>
        </div>
    </div>

    <?php if ($_SESSION['usuario_role'] === 'admin'): ?>
    <div class="ac-stat-card ac-stat-orange">
        <div class="ac-stat-icon"><i class="fas fa-user-clock"></i></div>
        <div class="ac-stat-info">
            <h3><?= number_format($stats['usuarios_pendentes']) ?></h3>
            <p>Aguardando Aprovação</p>
        </div>
        <?php if ($stats['usuarios_pendentes'] > 0): ?>
        <a href="<?= APP_URL ?>/usuarios?status=pendente" class="ac-stat-action">Ver pendentes</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Ação rápida -->
<?php if (Security::hasPermission('consultar_cnpj')): ?>
<div class="ac-quick-search">
    <h3><i class="fas fa-bolt"></i> Consulta Rápida</h3>
    <form method="POST" action="<?= APP_URL ?>/cnpj/buscar" class="ac-quick-form">
        <?= Security::csrfField() ?>
        <div class="ac-input-group">
            <input type="text" name="cnpj" class="ac-input" placeholder="Digite o CNPJ (somente números)" maxlength="18" id="quickCnpj">
            <button type="submit" class="ac-btn ac-btn-primary"><i class="fas fa-search"></i> Consultar</button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Gráficos -->
<div class="ac-charts-grid">
    <div class="ac-card">
        <div class="ac-card-header">
            <h3><i class="fas fa-chart-line"></i> Consultas por dia (últimos 30 dias)</h3>
        </div>
        <div class="ac-card-body">
            <canvas id="dailyChart" height="120"></canvas>
        </div>
    </div>

    <div class="ac-card">
        <div class="ac-card-header">
            <h3><i class="fas fa-chart-bar"></i> Consultas por mês</h3>
        </div>
        <div class="ac-card-body">
            <canvas id="monthlyChart" height="120"></canvas>
        </div>
    </div>
</div>

<!-- Tabela de consultas recentes -->
<div class="ac-card">
    <div class="ac-card-header">
        <h3><i class="fas fa-clock"></i> Últimas Consultas</h3>
        <?php if (Security::hasPermission('ver_historico')): ?>
        <a href="<?= APP_URL ?>/historico" class="ac-btn ac-btn-sm ac-btn-outline">Ver todas</a>
        <?php endif; ?>
    </div>
    <div class="ac-card-body ac-table-wrap">
        <?php if (empty($recentConsultas)): ?>
        <div class="ac-empty">
            <i class="fas fa-inbox"></i>
            <p>Nenhuma consulta realizada ainda.</p>
        </div>
        <?php else: ?>
        <table class="ac-table">
            <thead>
                <tr>
                    <th>CNPJ</th>
                    <th>Razão Social</th>
                    <th>Situação</th>
                    <?php if ($_SESSION['usuario_role'] === 'admin'): ?>
                    <th>Usuário</th>
                    <?php endif; ?>
                    <th>Data/Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentConsultas as $c): ?>
                <tr>
                    <td><code><?= Security::escape(Security::formatCnpj($c['cnpj'])) ?></code></td>
                    <td><?= Security::escape($c['razao_social'] ?? '–') ?></td>
                    <td>
                        <span class="ac-badge <?= strtolower($c['situacao'] ?? '') === 'ativa' ? 'ac-badge-green' : 'ac-badge-red' ?>">
                            <?= Security::escape($c['situacao'] ?? '–') ?>
                        </span>
                    </td>
                    <?php if ($_SESSION['usuario_role'] === 'admin'): ?>
                    <td><?= Security::escape($c['usuario_nome'] ?? '–') ?></td>
                    <?php endif; ?>
                    <td><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php
$meses  = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
$dLabels = json_encode(array_column($dailyStats, 'dia'));
$dData   = json_encode(array_column($dailyStats, 'total'));
$mLabels = json_encode(array_map(fn($r) => $meses[(int)$r['mes']-1] . '/' . substr($r['ano'], 2), $monthlyStats));
$mData   = json_encode(array_column($monthlyStats, 'total'));

$extraJs = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#2563EB';
    const chartDefaults = {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    };
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: $dLabels,
            datasets: [{ label:'Consultas', data: $dData, borderColor: primary,
                backgroundColor: primary + '22', fill: true, tension: 0.3, pointRadius: 3 }]
        },
        options: chartDefaults
    });
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: $mLabels,
            datasets: [{ label:'Consultas', data: $mData, backgroundColor: primary }]
        },
        options: chartDefaults
    });
    // Máscara CNPJ campo rápido
    const q = document.getElementById('quickCnpj');
    if (q) q.addEventListener('input', () => { q.value = maskCnpj(q.value); });
})();
</script>
JS;
?>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
