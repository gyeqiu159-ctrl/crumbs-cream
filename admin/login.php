<?php
require_once __DIR__ . '/auth.php';

if (admin_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$notSetUp = (ADMIN_PASSWORD_HASH === '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$notSetUp) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (admin_attempt_login($username, $password)) {
        header('Location: index.php');
        exit;
    }

    $error = 'Incorrect username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Crumb & Cream</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-login-body">

    <div class="login-card reveal-in">
        <div class="login-brand">
            <span class="logo-mark" aria-hidden="true"></span>
            <span>Crumb &amp; Cream</span>
        </div>
        <h1>Admin Login</h1>
        <p class="login-sub">Sign in to view orders and stats.</p>

        <?php if ($notSetUp): ?>
            <div class="alert alert-error">
                No admin password has been set up yet. Open
                <code>admin/generate-password.php</code> in your browser to create one,
                then paste the resulting hash into <code>admin/config.php</code>.
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" <?php echo $notSetUp ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required <?php echo $notSetUp ? 'disabled' : ''; ?>>
            </div>
            <button type="submit" class="btn btn-primary btn-block" <?php echo $notSetUp ? 'disabled' : ''; ?>>Log In</button>
        </form>

        <a href="../index.php" class="back-link">&larr; Back to site</a>
    </div>

</body>
</html>