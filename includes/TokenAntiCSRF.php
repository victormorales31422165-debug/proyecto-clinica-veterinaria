<?php
class TokenAntiCSRF {
    
    public static function generarToken() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if (!isset($_SESSION['tokenAntiCSRF'])) {
            // CORRECCIÓN: La función correcta es bin2hex (con el número 2)
            $_SESSION['tokenAntiCSRF'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['tokenAntiCSRF'];
    }

    public static function consumirToken($tokenEnviado) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if (isset($_SESSION['tokenAntiCSRF']) && $_SESSION['tokenAntiCSRF'] === $tokenEnviado) {
            // MEJORA DE SEGURIDAD: 
            // Una vez que el token se usa con éxito, lo eliminamos (lo "consumimos").
            // Esto evita que el mismo token sea usado dos veces (ataque de repetición).
            unset($_SESSION['tokenAntiCSRF']);
            return true;
        }
        return false;
    }
}
?>