<?php
/**
 * security_headers.php
 * Incluir al inicio de todas las páginas HTML (antes de cualquier output).
 * No incluir en endpoints JSON (ya devuelven Content-Type: application/json).
 */

// Evitar clickjacking
header('X-Frame-Options: DENY');

// Evitar MIME sniffing
header('X-Content-Type-Options: nosniff');

// Política de referrer
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content Security Policy básica
// Ajustar si se agregan más CDNs o dominios de scripts/estilos
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "style-src 'self' https://cdn.jsdelivr.net; " .
    "script-src 'self' https://cdn.jsdelivr.net; " .
    "font-src 'self' data:; " .
    "img-src 'self' data:; " .
    "connect-src 'self'"
);

// Generar token CSRF si no existe en la sesión (requiere session_start() previo)
if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
