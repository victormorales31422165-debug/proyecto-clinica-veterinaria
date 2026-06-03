<?php
class TokenAntiCSRF {
    public static function generarToken() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['tokenAntiCSRF'])) {
            $_SESSION['tokenAntiCSRF'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['tokenAntiCSRF'];
    }

    public static function consumirToken($tokenEnviado) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (isset($_SESSION['tokenAntiCSRF']) && $_SESSION['tokenAntiCSRF'] === $tokenEnviado) {
            // Regenerar para la próxima petición (opcional, para mayor seguridad)
            // unset($_SESSION['tokenAntiCSRF']); 
            return true;
        }
        return false;
    }
}
?>