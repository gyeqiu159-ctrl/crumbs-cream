<?php
/**
 * Shared auth bootstrap for every page under /admin.
 * Include this at the very top of any protected admin page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

function admin_is_logged_in(): bool
{
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function admin_attempt_login(string $username, string $password): bool
{
    if (ADMIN_PASSWORD_HASH === '') {
        return false; // no password set up yet
    }

    if (!hash_equals(ADMIN_USERNAME, $username)) {
        return false;
    }

    if (!password_verify($password, ADMIN_PASSWORD_HASH)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION[ADMIN_SESSION_KEY] = true;
    return true;
}

function admin_logout(): void
{
    $_SESSION = [];
    session_destroy();
}
