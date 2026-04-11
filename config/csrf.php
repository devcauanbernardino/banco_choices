<?php

declare(strict_types=1);

require_once __DIR__ . '/app_bootstrap.php';

/**
 * Token CSRF por sessão (sincronizer token).
 */
function csrf_token(): string
{
    app_session_start();
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_validate(?string $token): bool
{
    if (!is_string($token) || $token === '') {
        return false;
    }
    $sess = $_SESSION['_csrf_token'] ?? '';

    return is_string($sess) && $sess !== '' && hash_equals($sess, $token);
}

/** HTML de campo hidden para formulários POST. */
function csrf_field(): string
{
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

    return '<input type="hidden" name="_csrf" value="' . $t . '">';
}
