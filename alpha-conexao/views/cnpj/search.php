<?php
$pageTitle  = 'Consultar CNPJ';
$activePage = 'cnpj';
include VIEWS_PATH . '/layouts/header.php';
?>

<div class="ac-page-header">
    <h2><i class="fas fa-search"></i> Consultar CNPJ</h2>
    <p>Pesquise dados empresariais pela API Open CNPJ</p>
</div>

<!-- Formulário de consulta -->
<div class="ac-card ac-search-card">
    <div class="ac-card-body">
        <form method="POST" action="<?= APP_URL ?>/cnpj/buscar" class="ac-search-form" id="cnpjForm">
            <?= Security::csrfField() ?>
            <div class="ac-search-wrap">
                <div class="ac-form-group ac-search-field">
                    <label for="cnpj"><i class="fas fa-barcode"></i> CNPJ</label>
                    <input type="text" id="cnpj" name="cnpj" class="ac-input ac-input-lg"
                           placeholder="00.000.000/0000-00" maxlength="18" autocomplete="off"
                           value="<?= Security::escape($cnpjDigitado ?? '') ?>" required>
                    <span class="ac-input-hint" id="cnpjStatus"></span>
                </div>
                <button type="submit" class="ac-btn ac-btn-primary ac-btn-lg" id="btnConsultar">
                    <i class="fas fa-search" id="btnIcon"></i>
                    <span id="btnText">Consultar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="ac-alert ac-alert-error">
    <i class="fas fa-exclamation-triangle"></i>
    <?= Security::escape($error) ?>
</div>
<?php endif; ?>

<!-- Resultado -->
<?php if (!empty($resultado)): ?>
<div class="ac-result" id="resultado">
    <?php
    $r         = $resultado;
    $razao     = $r['razao_social'] ?? '–';
    $fantasia  = $r['nome_fantasia'] ?? '';
    $situacao  = $r['descricao_situacao_cadastral'] ?? '–';
    $abertura  = !empty($r['data_inicio_atividade']) ? date('d/m/Y', strtotime($r['data_inicio_atividade'])) : '–';
    $natureza  = $r['natureza_juridica']['descricao'] ?? '–';
    $porte     = $r['porte']['descricao'] ?? '–';
    $capital   = isset($r['capital_social']) ? 'R$ ' . number_format((float)$r['capital_social'], 2, ',', '.') : '–';
    $cnaeP     = $r['cnae_fiscal_descricao'] ?? ($r['cnae_fiscal_principal']['descricao'] ?? '–');
    $cnaePCod  = $r['cnae_fiscal'] ?? ($r['cnae_fiscal_principal']['codigo'] ?? '');
    $cnaes     = $r['cnaes_secundarios'] ?? [];
    $socios    = $r['qsa'] ?? [];
    $tel1      = $r['ddd_telefone_1'] ?? '';
    $tel2      = $r['ddd_telefone_2'] ?? '';
    $email_emp = $r['email'] ?? '';
    $end       = $r['logradouro'] ?? '';
    $endNum    = $r['numero'] ?? '';
    $endComp   = $r['complemento'] ?? '';
    $bairro    = $r['bairro'] ?? '';
    $mun       = $r['municipio'] ?? '';
    $uf        = $r['uf'] ?? '';
    $cep       = $r['cep'] ?? '';
    $ativaSit  = strtolower($situacao) === 'ativa';
    ?>

    <div class="ac-result-header">
        <div>
            <h2><?= Security::escape($razao) ?></h2>
            <?php if ($fantasia): ?>
            <p class="ac-result-fantasia"><?= Security::escape($fantasia) ?></p>
            <?php endif; ?>
            <code class="ac-cnpj-display"><?= Security::escape($cnpjDigitado) ?></code>
        </div>
        <span class="ac-badge-lg <?= $ativaSit ? 'ac-badge-green' : 'ac-badge-red' ?>">
            <i class="fas <?= $ativaSit ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
            <?= Security::escape($situacao) ?>
        </span>
    </div>

    <div class="ac-result-grid">
        <!-- Dados Gerais -->
        <div class="ac-result-section">
            <h4><i class="fas fa-info-circle"></i> Dados Gerais</h4>
            <table class="ac-result-table">
                <tr><th>Razão Social</th><td><?= Security::escape($razao) ?></td></tr>
                <?php if ($fantasia): ?>
                <tr><th>Nome Fantasia</th><td><?= Security::escape($fantasia) ?></td></tr>
                <?php endif; ?>
                <tr><th>Data de Abertura</th><td><?= $abertura ?></td></tr>
                <tr><th>Natureza Jurídica</th><td><?= Security::escape($natureza) ?></td></tr>
                <tr><th>Porte</th><td><?= Security::escape($porte) ?></td></tr>
                <tr><th>Capital Social</th><td><?= $capital ?></td></tr>
                <tr><th>Situação Cadastral</th>
                    <td><span class="ac-badge <?= $ativaSit ? 'ac-badge-green' : 'ac-badge-red' ?>"><?= Security::escape($situacao) ?></span></td></tr>
            </table>
        </div>

        <!-- CNAE -->
        <div class="ac-result-section">
            <h4><i class="fas fa-industry"></i> Atividades Econômicas</h4>
            <p><strong>CNAE Principal:</strong><br>
               <?php if ($cnaePCod): ?><code><?= Security::escape($cnaePCod) ?></code> – <?php endif; ?>
               <?= Security::escape($cnaeP) ?></p>
            <?php if (!empty($cnaes)): ?>
            <p><strong>CNAEs Secundários:</strong></p>
            <ul class="ac-cnaes-list">
                <?php foreach ($cnaes as $c): ?>
                <li><code><?= Security::escape($c['codigo'] ?? '') ?></code> <?= Security::escape($c['descricao'] ?? '') ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <!-- Contato -->
        <div class="ac-result-section">
            <h4><i class="fas fa-phone"></i> Contato</h4>
            <table class="ac-result-table">
                <?php if ($tel1): ?><tr><th>Telefone 1</th><td><?= Security::escape($tel1) ?></td></tr><?php endif; ?>
                <?php if ($tel2): ?><tr><th>Telefone 2</th><td><?= Security::escape($tel2) ?></td></tr><?php endif; ?>
                <?php if ($email_emp): ?><tr><th>E-mail</th><td><?= Security::escape($email_emp) ?></td></tr><?php endif; ?>
            </table>
        </div>

        <!-- Endereço -->
        <div class="ac-result-section">
            <h4><i class="fas fa-map-marker-alt"></i> Endereço</h4>
            <table class="ac-result-table">
                <tr><th>Logradouro</th><td><?= Security::escape("$end, $endNum" . ($endComp ? ", $endComp" : '')) ?></td></tr>
                <tr><th>Bairro</th><td><?= Security::escape($bairro) ?></td></tr>
                <tr><th>Município</th><td><?= Security::escape($mun) ?></td></tr>
                <tr><th>Estado</th><td><?= Security::escape($uf) ?></td></tr>
                <tr><th>CEP</th><td><?= Security::escape($cep) ?></td></tr>
            </table>
        </div>

        <!-- Sócios -->
        <?php if (!empty($socios)): ?>
        <div class="ac-result-section ac-result-section-full">
            <h4><i class="fas fa-user-tie"></i> Quadro Societário (QSA)</h4>
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Qualificação</th>
                            <th>Desde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($socios as $s): ?>
                        <tr>
                            <td><?= Security::escape($s['nome_socio'] ?? $s['nome'] ?? '–') ?></td>
                            <td><?= Security::escape($s['qualificacao_socio']['descricao'] ?? $s['qualificacao'] ?? '–') ?></td>
                            <td><?= !empty($s['data_entrada_sociedade']) ? date('d/m/Y', strtotime($s['data_entrada_sociedade'])) : '–' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="ac-result-actions">
        <button onclick="window.print()" class="ac-btn ac-btn-outline">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <a href="<?= APP_URL ?>/cnpj" class="ac-btn ac-btn-outline">
            <i class="fas fa-plus"></i> Nova Consulta
        </a>
    </div>
</div>
<?php endif; ?>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
