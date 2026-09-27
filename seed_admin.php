<?php
/**
 * Database Initializer & Admin Seeder
 * EcoCollect Management Platform
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$message = '';
$error = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST' || isset($_GET['auto']) || (php_sapi_name() === 'cli')) {
    try {
        $sqlPath = __DIR__ . '/database/database.sql';
        $demoSqlPath = __DIR__ . '/database/demo-data.sql';

        if (!file_exists($sqlPath)) {
            throw new Exception("Database SQL file not found at database/database.sql");
        }

        $pdo = get_db_connection();

        // 1. Execute Schema SQL
        $sql = file_get_contents($sqlPath);
        $pdo->exec($sql);

        // 2. Execute Demo Data SQL if available
        if (file_exists($demoSqlPath)) {
            $demoSql = file_get_contents($demoSqlPath);
            $pdo->exec($demoSql);
        }

        $message = "Database tables and 16 realistic demo pickup requests initialized successfully!";
    } catch (Exception $e) {
        $error = "Failed to initialize database: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect Setup & Database Seeder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f7faf8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .setup-card { max-width: 650px; margin: 60px auto; border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .btn-primary-custom { background-color: #2E7D32; border-color: #2E7D32; color: white; }
        .btn-primary-custom:hover { background-color: #1b5e20; }
    </style>
</head>
<body>

<div class="container">
    <div class="card setup-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-success"><i class="bi bi-recycle me-2"></i>EcoCollect Setup</h2>
            <p class="text-muted">Initialize MySQL database and seed demo accounts & realistic pickup requests.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Success!</strong> <?= htmlspecialchars($message) ?>
                    <hr class="my-2">
                    <p class="mb-1"><strong>Demo Credentials Available:</strong></p>
                    <ul class="mb-2 ps-3 small">
                        <li><strong>Admin:</strong> <code>admin@ecocollect.com</code> | Password: <code>Admin@123</code></li>
                        <li><strong>User 1:</strong> <code>rahul@ecocollect.com</code> | Password: <code>User@123</code></li>
                        <li><strong>User 2:</strong> <code>priya@ecocollect.com</code> | Password: <code>User@123</code></li>
                    </ul>
                    <a href="login.php" class="btn btn-sm btn-success fw-semibold mt-2">Go to Sign In Page</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="p-3 bg-light rounded-3 mb-4 border">
                <h6 class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i> Pre-requisites & Credentials</h6>
                <small class="text-muted d-block mb-1">1. XAMPP Apache & MySQL services must be running.</small>
                <small class="text-muted d-block mb-1">2. Target Database: <code>ecocollect</code> on <code>127.0.0.1:3306</code>.</small>
                <small class="text-muted d-block">3. Seeds 16 realistic pickup requests across Pune localities (Kothrud, Baner, Wakad, etc.).</small>
            </div>

            <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-3 rounded-3 fw-bold">
                <i class="bi bi-database-fill-gear me-2"></i> Initialize Database & Seed Demo Data
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Back to EcoCollect Home</a>
        </div>
    </div>
</div>

</body>
</html>
