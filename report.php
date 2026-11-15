<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/database.php';

$success = '';
$error = '';

$types = [
    'Power Outage',
    'Low Voltage',
    'Broken Electric Pole',
    'Damaged Transformer',
    'Exposed Wire',
    'Other'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $type = $_POST['type'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? '';

    $validDate = false;
    if (is_string($date)) {
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $validDate = $parsedDate !== false &&
            $parsedDate->format('Y-m-d') === $date;
    }

    if (
        $name === '' || mb_strlen($name) > 120 ||
        $phone === '' || strlen($phone) > 30 ||
        !in_array($type, $types, true) ||
        $location === '' || mb_strlen($location) > 255 ||
        $description === '' || mb_strlen($description) > 10000 ||
        !$validDate
    ) {
        $error = 'Please complete all fields with valid information.';
    } else {
        try {
            $code = 'FR-' . strtoupper(bin2hex(random_bytes(6)));

            $stmt = $pdo->prepare(
                'INSERT INTO reports
                (report_code, name, phone, fault_type,
                 location, description, report_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $code, $name, $phone, $type,
                $location, $description, $date
            ]);

            $success = 'Your report was submitted successfully. Report ID: ' . $code;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'There was a problem saving your report. Please try again.';
        }
    }
}

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Fault | PowerReport - Kankia Iro School of Health Technology</title>
    <link rel="stylesheet" href="../../CSS/style.css?v=7">
</head>
<body class="public-page">
    <main class="public-card">
        <h1 class="brand-lockup">
            <strong class="brand-product">⚡ PowerReport</strong>
            <span class="brand-school">Kankia Iro School of Health Technology</span>
        </h1>
        <h2>Report an Electricity Fault</h2>
        <p>Submit a report about an electricity fault in your area.</p>

        <?php if ($success): ?>
            <p class="success"><?= e($success) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Full Name
                <input name="name" maxlength="120" required>
            </label>

            <label>Phone Number
                <input name="phone" type="tel" maxlength="30" required>
            </label>

            <label>Fault Type
                <select name="type" required>
                    <option value="">Select fault type</option>
                    <?php foreach ($types as $item): ?>
                        <option value="<?= e($item) ?>">
                            <?= e($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Location / Address
                <input name="location" maxlength="255" required>
            </label>

            <label>Fault Description
                <textarea name="description" rows="4"
                          maxlength="10000" required></textarea>
            </label>

            <label>Date of Report
                <input type="date" name="date"
                       value="<?= e($today) ?>" required>
            </label>

            <button type="submit" class="primary">Submit Report</button>
        </form>

        <a class="public-report-link" href="login.php">
            <span>Are you an administrator?</span>
            <strong>Sign in to the admin dashboard</strong>
        </a>
    </main>
</body>
</html>