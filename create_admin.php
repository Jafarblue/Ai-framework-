<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';
requireAdmin();
require __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $usernameInput = $_POST['username'] ?? '';
    $username = is_string($usernameInput) ? trim($usernameInput) : '';
    $password = $_POST['password'] ?? '';
    $confirmation = $_POST['password_confirmation'] ?? '';

    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $error = 'Choose a username with 3 to 50 letters, numbers, dots, underscores, or hyphens.';
    } elseif (!is_string($password) || strlen($password) < 12) {
        $error = 'Choose a password with at least 12 characters.';
    } elseif (!is_string($confirmation) || !hash_equals($password, $confirmation)) {
        $error = 'The password confirmation does not match.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO admins (username, password_hash) VALUES (?, ?)'
            );
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $success = 'Administrator account created successfully.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'That username is already in use or the account could not be created.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Administrator | PowerReport - Kankia Iro School of Health Technology</title>
    <link rel="stylesheet" href="../../CSS/style.css?v=7">
</head>
<body class="login-page">
    <main class="login-card">
        <h1 class="brand-lockup">
            <strong class="brand-product">⚡ PowerReport</strong>
            <span class="brand-school">Kankia Iro School of Health Technology</span>
        </h1>
        <h2>Add Administrator</h2>
        <p>Create another account for a trusted administrator.</p>

        <?php if ($success): ?>
            <p class="success"><?= e($success) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <label>Administrator Username
                <input name="username" minlength="3" maxlength="50"
                       autocomplete="username" required>
            </label>

            <label>Password
                <input type="password" name="password" minlength="12"
                       autocomplete="new-password" required>
            </label>

            <label>Confirm Password
                <input type="password" name="password_confirmation" minlength="12"
                       autocomplete="new-password" required>
            </label>

            <button class="primary" type="submit">Create Administrator</button>
        </form>

        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </main>
</body>
</html>
