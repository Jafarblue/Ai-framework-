<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';
requireAdmin();
require __DIR__ . '/../config/database.php';

$allowedStatuses = ['Pending', 'In Progress', 'Resolved'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        $error = 'The report ID is invalid.';
    } elseif ($action === 'update') {
        $status = $_POST['status'] ?? '';

        if (!in_array($status, $allowedStatuses, true)) {
            $error = 'The selected status is invalid.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE reports SET status = ? WHERE id = ?'
            );
            $stmt->execute([$status, $id]);
            $message = $stmt->rowCount()
                ? 'The report status was updated.'
                : 'There was no change, or the report does not exist.';
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM reports WHERE id = ?');
        $stmt->execute([$id]);
        $message = $stmt->rowCount()
            ? 'The report was deleted.'
            : 'The report could not be found.';
    } else {
        $error = 'The requested action is invalid.';
    }
}

$q = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$sql = 'SELECT id, report_code, name, phone, fault_type,
               location, description, report_date, status, created_at
        FROM reports WHERE 1=1';
$params = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $sql .= ' AND (report_code LIKE ? OR name LIKE ?
              OR phone LIKE ? OR location LIKE ? OR fault_type LIKE ?)';
    array_push($params, $like, $like, $like, $like, $like);
}

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= ' AND status = ?';
    $params[] = $statusFilter;
}

$sql .= ' ORDER BY created_at DESC, id DESC LIMIT 500';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Reports | PowerReport - Kankia Iro School of Health Technology</title>
    <link rel="stylesheet" href="../../CSS/style.css?v=7">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h2 class="brand-lockup">
            <strong class="brand-product">⚡ PowerReport</strong>
            <span class="brand-school">Kankia Iro School of Health Technology</span>
        </h2>
        <p class="side-label">ADMINISTRATION</p>
        <a class="nav" href="dashboard.php">Dashboard</a>
        <a class="nav active" href="reports.php">All Reports</a>
        <a class="nav" href="create_admin.php">Add Administrator</a>
        <a class="nav" href="report.php" target="_blank">Public Report Form</a>

        <div class="side-footer">
            <span><?= e($_SESSION['admin_username'] ?? 'Administrator') ?></span>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token"
                       value="<?= e(csrfToken()) ?>">
                <button class="secondary" type="submit">Logout</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h1>All Fault Reports</h1>
                <p>Search reports and update their status.</p>
            </div>
        </header>

        <?php if ($message): ?>
            <p class="success"><?= e($message) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <section class="panel">
            <form method="get" class="filters">
                <input name="q" value="<?= e($q) ?>"
                       placeholder="Search ID, name, phone, location...">

                <select name="status">
                    <option value="">All statuses</option>
                    <?php foreach ($allowedStatuses as $s): ?>
                        <option value="<?= e($s) ?>"
                            <?= $statusFilter === $s ? 'selected' : '' ?>>
                            <?= e($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="primary" type="submit">Search</button>
                <a class="secondary button-link" href="reports.php">Clear</a>
            </form>

            <?php if (!$reports): ?>
                <p class="empty">No matching reports were found.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reporter</th>
                            <th>Phone</th>
                            <th>Fault</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($reports as $r): ?>
                            <tr>
                                <td><?= e($r['report_code']) ?></td>
                                <td><?= e($r['name']) ?></td>
                                <td><?= e($r['phone']) ?></td>
                                <td><?= e($r['fault_type']) ?></td>
                                <td><?= e($r['location']) ?></td>
                                <td><?= e($r['report_date']) ?></td>
                                <td class="description-cell">
                                    <?= e($r['description']) ?>
                                </td>
                                <td>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="csrf_token"
                                            value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="id"
                                            value="<?= (int) $r['id'] ?>">
                                        <input type="hidden" name="action"
                                            value="update">

                                        <select name="status"
                                                aria-label="Report status">
                                            <?php foreach ($allowedStatuses as $s): ?>
                                                <option value="<?= e($s) ?>"
                                                    <?= $r['status'] === $s ? 'selected' : '' ?>>
                                                    <?= e($s) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button class="primary small-btn"
                                                type="submit">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" class="inline-form delete-form">
                                        <input type="hidden" name="csrf_token"
                                            value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="id"
                                            value="<?= (int) $r['id'] ?>">
                                        <input type="hidden" name="action"
                                            value="delete">

                                        <button class="danger small-btn"
                                                type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="muted">Showing up to 500 reports on this page.</p>
            <?php endif; ?>
        </section>

        <footer>PowerReport | Kankia Iro School of Health Technology © 2026</footer>
    </main>
</div>
</body>
</html>