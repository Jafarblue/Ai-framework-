<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/database.php';

if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username'])) {
    header('Location: dashboard.php');
    exit;
}

$needsSetup = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0;
$canSetup = $needsSetup && in_array(
    $_SERVER['REMOTE_ADDR'] ?? '',
    ['127.0.0.1', '::1'],
    true
);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $usernameInput = $_POST['username'] ?? '';
    $username = is_string($usernameInput) ? trim($usernameInput) : '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
         FROM admins WHERE BINARY username = BINARY ? LIMIT 1'
    );
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (
        $admin &&
        is_string($usernameInput) &&
        is_string($password) &&
        password_verify($password, $admin['password_hash'])
    ) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header('Location: dashboard.php');
        exit;
    }

    $error = 'The username or password is incorrect.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | PowerReport - Kankia Iro School of Health Technology</title>
    <link rel="stylesheet" href="../../CSS/style.css?v=7">
</head>
<body class="login-page">
    <main class="login-card">
        <h1 class="brand-lockup">
            <strong class="brand-product">⚡ PowerReport</strong>
            <span class="brand-school">Kankia Iro School of Health Technology</span>
        </h1>
        <h2>Administrator Login</h2>
        <p>Sign in to manage electricity fault reports.</p>

        <?php if ($error): ?>
            <p class="error">
                <?= e($error) ?>
            </p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <label>Username
                <input name="username" required
                       autocomplete="username">
            </label>

            <label>Password
                <input type="password" name="password" required
                       autocomplete="current-password">
            </label>

            <button class="primary" type="submit">Login</button>
        </form>

        <a class="public-report-link" href="report.php">
            <span>Need to report an electricity fault?</span>
            <strong>Open the public report form</strong>
        </a>
        <?php if ($canSetup): ?>
            <a class="public-report-link" href="setup.php">
                <span>Server computer only</span>
                <strong>Set up the Ahmad123 administrator account</strong>
            </a>
        <?php endif; ?>
    </main>
</body>
</html>