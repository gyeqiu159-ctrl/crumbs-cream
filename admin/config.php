<?php
/**
 * Admin dashboard credentials
 * -----------------------------------------------------------------
 * ADMIN_USERNAME       — the login username
 * ADMIN_PASSWORD_HASH  — a bcrypt hash, NOT a plain password
 *
 * FIRST-TIME SETUP:
 *   1. Open admin/generate-password.php in your browser
 *   2. Type the password you want, it will show you a hash
 *   3. Copy that hash and paste it below, replacing the empty string
 *   4. Delete admin/generate-password.php (don't leave it on a live site)
 *
 * Until ADMIN_PASSWORD_HASH is filled in, the login page will show
 * a reminder instead of letting anyone in.
 */

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$UeW/PobmRkEMnK2aNqr95u.AvaRjNSUvKUY4mXbh076OcUbF1Tmfi'); // paste your generated hash here
define('ADMIN_SESSION_KEY', 'crumbcream_admin_logged_in');
