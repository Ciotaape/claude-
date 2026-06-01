<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | Alpha Conexão</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
</head>
<body class="ac-auth-body">
<div class="ac-auth-container">
    <div class="ac-auth-card">
        <div class="ac-auth-logo">
            <i class="fas fa-building"></i>
            <h1>Alpha Conexão</h1>
            <p>Recuperação de Senha</p>
        </div>

        <?php if ($error): ?>
        <div class="ac-alert ac-alert-error"><i class="fas fa-exclamation-circle"></i> <?= Security::escape($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="ac-alert ac-alert-success"><i class="fas fa-check-circle"></i> <?= Security::escape($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/forgot-password" class="ac-form">
            <?= Security::csrfField() ?>
            <div class="ac-form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-mail cadastrado</label>
                <input type="email" id="email" name="email" class="ac-input" placeholder="seu@email.com" required>
            </div>
            <button type="submit" class="ac-btn ac-btn-primary ac-btn-full">
                <i class="fas fa-paper-plane"></i> Enviar instruções
            </button>
        </form>

        <div class="ac-auth-footer">
            <p><a href="<?= APP_URL ?>/login" class="ac-link"><i class="fas fa-arrow-left"></i> Voltar ao login</a></p>
        </div>
    </div>
</div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
