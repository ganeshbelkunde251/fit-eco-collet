<?php
// login.php - User Sign In
$pageTitle = "Sign In — EcoCollect";
require_once __DIR__ . '/config/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . (is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        $error = "Security check failed. Please try again.";
    } elseif (empty($email) || empty($password)) {
        $error = "Please enter both email address and password.";
    } else {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                set_flash('success', "Welcome back, " . htmlspecialchars($user['name']) . "!");

                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: user/dashboard.php');
                }
                exit;
            } else {
                $error = "Invalid email or password. Please check your credentials.";
            }
        } catch (Exception $e) {
            $error = "System error during sign in: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="brand-icon bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-box-arrow-in-right fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
                        <p class="text-muted small">Sign in to manage waste pickup requests</p>
                    </div>

                    <?php require_once __DIR__ . '/includes/alerts.php'; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Demo Quick Credentials Box -->
                    <div class="bg-light p-3 rounded-3 mb-4 border border-dashed">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary text-white fs-8">HACKATHON DEMO ACCOUNTS</span>
                            <a href="seed_admin.php" class="text-success small fw-semibold text-decoration-none">Run Seeder</a>
                        </div>
                        <small class="text-muted d-block mb-1">
                            <strong>User:</strong> <code>rahul@ecocollect.com</code> | <code>User@123</code>
                        </small>
                        <small class="text-muted d-block">
                            <strong>Admin:</strong> <code>admin@ecocollect.com</code> | <code>Admin@123</code>
                        </small>
                    </div>

                    <form method="POST" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold small">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="user@ecocollect.local" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-semibold small mb-1">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold mb-3 shadow-sm">
                            Sign In to EcoCollect <i class="bi bi-arrow-right ms-1"></i>
                        </button>

                        <div class="text-center">
                            <p class="small text-muted mb-0">Don't have an account yet? <a href="signup.php" class="text-success fw-bold text-decoration-none">Create Account</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
