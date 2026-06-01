<?php
// =============================================
// ALPHA CONEXÃO - Utilitários de Segurança
// =============================================

class Security {

    /** Gera token CSRF e armazena na sessão */
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token']) || time() > ($_SESSION['csrf_token_expiry'] ?? 0)) {
            $_SESSION['csrf_token']        = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_expiry'] = time() + CSRF_TOKEN_LIFETIME;
        }
        return $_SESSION['csrf_token'];
    }

    /** Valida token CSRF vindo do formulário */
    public static function verifyCsrf(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) return false;
        return hash_equals($_SESSION['csrf_token'], $token)
            && time() <= ($_SESSION['csrf_token_expiry'] ?? 0);
    }

    /** Retorna campo hidden CSRF pronto para HTML */
    public static function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    /** Sanitiza string para output HTML */
    public static function escape(mixed $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Remove tudo exceto dígitos */
    public static function onlyDigits(string $value): string {
        return preg_replace('/\D/', '', $value);
    }

    /** Formata CNPJ para exibição */
    public static function formatCnpj(string $cnpj): string {
        $cnpj = self::onlyDigits($cnpj);
        if (strlen($cnpj) === 14) {
            return sprintf('%s.%s.%s/%s-%s',
                substr($cnpj, 0, 2), substr($cnpj, 2, 3),
                substr($cnpj, 5, 3), substr($cnpj, 8, 4), substr($cnpj, 12, 2)
            );
        }
        return $cnpj;
    }

    /** Valida CNPJ com dígito verificador */
    public static function validateCnpj(string $cnpj): bool {
        $cnpj = self::onlyDigits($cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) return false;

        $calcDigit = function(string $cnpj, int $length): int {
            $sum = 0;
            $pos = $length - 7;
            for ($i = $length; $i >= 1; $i--) {
                $sum += (int)$cnpj[$length - $i] * $pos--;
                if ($pos < 2) $pos = 9;
            }
            $result = $sum % 11;
            return $result < 2 ? 0 : 11 - $result;
        };

        return $calcDigit($cnpj, 12) === (int)$cnpj[12]
            && $calcDigit($cnpj, 13) === (int)$cnpj[13];
    }

    /** Verifica rate limit baseado em sessão/IP */
    public static function checkRateLimit(string $key, int $max, int $window): bool {
        $sessionKey = 'rate_' . md5($key);
        $now = time();

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset' => $now + $window];
        }

        if ($now > $_SESSION[$sessionKey]['reset']) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset' => $now + $window];
        }

        if ($_SESSION[$sessionKey]['count'] >= $max) return false;

        $_SESSION[$sessionKey]['count']++;
        return true;
    }

    /** Retorna IP real do visitante */
    public static function getIp(): string {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = trim(explode(',', $_SERVER[$k])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    /** Verifica se usuário está autenticado */
    public static function isAuthenticated(): bool {
        return !empty($_SESSION['usuario_id']) && !empty($_SESSION['usuario_status'])
            && $_SESSION['usuario_status'] === 'ativo';
    }

    /** Redireciona se não autenticado */
    public static function requireAuth(): void {
        if (!self::isAuthenticated()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /** Redireciona se não for admin */
    public static function requireAdmin(): void {
        self::requireAuth();
        if (($_SESSION['usuario_role'] ?? '') !== 'admin') {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }

    /** Verifica permissão específica */
    public static function hasPermission(string $perm): bool {
        return !empty($_SESSION['permissoes'][$perm]);
    }
}
