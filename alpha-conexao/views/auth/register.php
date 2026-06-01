<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Acesso | Alpha Conexão</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
</head>
<body class="ac-auth-body">

<div class="ac-auth-container">
    <div class="ac-auth-card">
        <div class="ac-auth-logo">
            <i class="fas fa-building"></i>
            <h1>Alpha Conexão</h1>
            <p>Solicitar Acesso ao Sistema</p>
        </div>

        <?php if ($error): ?>
        <div class="ac-alert ac-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= Security::escape($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="ac-alert ac-alert-success">
            <i class="fas fa-check-circle"></i>
            <?= Security::escape($success) ?>
        </div>
        <?php endif; ?>

        <div class="ac-info-box">
            <i class="fas fa-info-circle"></i>
            Seu cadastro será analisado pelo administrador. Você receberá uma notificação quando aprovado.
        </div>

        <form method="POST" action="<?= APP_URL ?>/register" class="ac-form" novalidate>
            <?= Security::csrfField() ?>

            <div class="ac-form-group">
                <label for="nome"><i class="fas fa-user"></i> Nome Completo</label>
                <input type="text" id="nome" name="nome" class="ac-input"
                       placeholder="Seu nome completo" required minlength="3"
                       value="<?= Security::escape($_POST['nome'] ?? '') ?>">
            </div>

            <div class="ac-form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                <input type="email" id="email" name="email" class="ac-input"
                       placeholder="seu@email.com" required autocomplete="email"
                       value="<?= Security::escape($_POST['email'] ?? '') ?>">
            </div>

            <div class="ac-form-group">
                <label for="senha"><i class="fas fa-lock"></i> Senha</label>
                <div class="ac-input-group">
                    <input type="password" id="senha" name="senha" class="ac-input"
                           placeholder="Mínimo 8 caracteres" required minlength="8">
                    <button type="button" class="ac-input-eye" onclick="togglePassword('senha')">
                        <i class="fas fa-eye" id="eyeSenha"></i>
                    </button>
                </div>
                <div class="ac-password-strength" id="strengthBar"></div>
            </div>

            <div class="ac-form-group">
                <label for="confirmar_senha"><i class="fas fa-lock"></i> Confirmar Senha</label>
                <div class="ac-input-group">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="ac-input"
                           placeholder="Repita a senha" required>
                    <button type="button" class="ac-input-eye" onclick="togglePassword('confirmar_senha')">
                        <i class="fas fa-eye" id="eyeConfirmar_senha"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="ac-btn ac-btn-primary ac-btn-full">
                <i class="fas fa-user-plus"></i> Solicitar Acesso
            </button>
        </form>

        <div class="ac-auth-footer">
            <p>Já tem conta? <a href="<?= APP_URL ?>/login" class="ac-link">Fazer login</a></p>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
