<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';
requireAdmin();
require __DIR__ . '/../config/database.php';

$counts = [
    'total' => 0,
    'Pending' => 0,
    'In Progress' => 0,
    'Resolved' => 0
];

$stmt = $pdo->query(
    "SELECT status, COUNT(*) AS total
     FROM reports GROUP BY status"
);

foreach ($stmt->fetchAll() as $row) {
    $counts[$row['status']] = (int) $row['total'];
    $counts['total'] += (int) $row['total'];
}

$recent = $pdo->query(
    'SELECT report_code, name, fault_type, location,
            report_date, status
     FROM reports
     ORDER BY created_at DESC, id DESC
     LIMIT 5'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | PowerReport - Kankia Iro School of Health Technology</title>
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
        <a class="nav active" href="dashboard.php">Dashboard</a>
        <a class="nav" href="reports.php">All Reports</a>
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
                <h1>Dashboard</h1>
                <p>Electricity Fault Reporting System</p>
            </div>
            <span class="admin">Administrator</span>
        </header>

        <section class="welcome">
            <div>
                <h2>Welcome to PowerReport ⚡</h2>
                <p class="brand-school">Kankia Iro School of Health Technology</p>
                <p>Monitor and manage electricity fault reports.</p>
            </div>
            <a class="primary button-link" href="reports.php">Manage Reports</a>
        </section>

        <section class="stats">
            <div class="stat-card">
                <span>Total Reports</span>
                <h2><?= $counts['total'] ?></h2>
            </div>
            <div class="stat-card">
                <span>Pending</span>
                <h2><?= $counts['Pending'] ?></h2>
            </div>
            <div class="stat-card">
                <span>In Progress</span>
                <h2><?= $counts['In Progress'] ?></h2>
            </div>
            <div class="stat-card">
                <span>Resolved</span>
                <h2><?= $counts['Resolved'] ?></h2>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Recent Fault Reports</h2>
                    <p>Most recently submitted reports</p>
                </div>
                <a href="reports.php">View all</a>
            </div>

            <?php if (!$recent): ?>
                <p class="empty">No reports have been submitted yet.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Name</th>
                            <th>Fault Type</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><?= e($r['report_code']) ?></td>
                                <td><?= e($r['name']) ?></td>
                                <td><?= e($r['fault_type']) ?></td>
                                <td><?= e($r['location']) ?></td>
                                <td><?= e($r['report_date']) ?></td>
                                <td>
                                    <span class="status status-<?= e(strtolower(str_replace(' ', '-', $r['status']))) ?>">
                                        <?= e($r['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <footer>PowerReport | Kankia Iro School of Health Technology © 2026</footer>
    </main>
</div>
</body>
</html>