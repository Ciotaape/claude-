<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha | Alpha Conexão</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
</head>
<body class="ac-auth-body">
<div class="ac-auth-container">
    <div class="ac-auth-card">
        <div class="ac-auth-logo">
            <i class="fas fa-building"></i>
            <h1>Alpha Conexão</h1>
            <p>Definir Nova Senha</p>
        </div>

        <?php if ($error): ?>
        <div class="ac-alert ac-alert-error"><i class="fas fa-exclamation-circle"></i> <?= Security::escape($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/reset-password/<?= Security::escape($token ?? '') ?>" class="ac-form">
            <?= Security::csrfField() ?>
            <div class="ac-form-group">
                <label for="senha"><i class="fas fa-lock"></i> Nova Senha</label>
                <div class="ac-input-group">
                    <input type="password" id="senha" name="senha" class="ac-input" placeholder="Mínimo 8 caracteres" required minlength="8">
                    <button type="button" class="ac-input-eye" onclick="togglePassword('senha')"><i class="fas fa-eye" id="eyeSenha"></i></button>
                </div>
            </div>
            <div class="ac-form-group">
                <label for="confirmar_senha"><i class="fas fa-lock"></i> Confirmar Senha</label>
                <div class="ac-input-group">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="ac-input" placeholder="Repita a senha" required>
                    <button type="button" class="ac-input-eye" onclick="togglePassword('confirmar_senha')"><i class="fas fa-eye" id="eyeConfirmar_senha"></i></button>
                </div>
            </div>
            <button type="submit" class="ac-btn ac-btn-primary ac-btn-full">
                <i class="fas fa-save"></i> Salvar nova senha
            </button>
        </form>
    </div>
</div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
