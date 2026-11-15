<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/database.php';

$adminUsername = 'Ahmad123';
$adminPasswordHash = '$2y$10$.FU0yWpUXRjQSMEuBmC4ku78kvmPIDoJHex5cY9Jm8BMmp4Btlrum';

$needsSetup = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0;
$isLocalRequest = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

if (!$needsSetup) {
    header('Location: login.php');
    exit;
}

if (!$isLocalRequest) {
    http_response_code(403);
    exit('Initial administrator setup is only available from this computer.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO admins (username, password_hash) VALUES (?, ?)'
        );
        $stmt->execute([$adminUsername, $adminPasswordHash]);

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $pdo->lastInsertId();
        $_SESSION['admin_username'] = $adminUsername;

        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'Initial administrator setup could not be completed. Sign in or try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set Up Administrator | PowerReport - Kankia Iro School of Health Technology</title>
    <link rel="stylesheet" href="../../CSS/style.css?v=7">
</head>
<body class="login-page">
    <main class="login-card">
        <h1 class="brand-lockup">
            <strong class="brand-product">⚡ PowerReport</strong>
            <span class="brand-school">Kankia Iro School of Health Technology</span>
        </h1>
        <h2>Set Up Administrator</h2>
        <p>Create the first administrator account. After setup, sign in to create additional administrators.</p>

        <?php if ($error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <button class="primary" type="submit">Create Ahmad123 Administrator</button>
        </form>

        <p><a href="login.php">Back to Login</a></p>
    </main>
</body>
</html>