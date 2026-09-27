<?php
// signup.php - User Registration
$pageTitle = "Sign Up — EcoCollect";
require_once __DIR__ . '/config/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . (is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF Token
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = "Security check failed. Please refresh and try again.";
    }

    // Server-side Validations
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check for duplicate email
    if (empty($errors)) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = "An account with this email address already exists.";
            }
        } catch (Exception $e) {
            $errors[] = "Database error during registration. Please try again.";
        }
    }

    // Insert user into MySQL
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (:name, :email, :phone, :password, 'user')");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashedPassword
            ]);

            set_flash('success', 'Account created successfully! Please sign in with your email and password.');
            header('Location: login.php');
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to create account: " . $e->getMessage();
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
                            <i class="bi bi-person-plus-fill fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Create Account</h3>
                        <p class="text-muted small">Join EcoCollect to schedule & track waste pickups</p>
                    </div>

                    <?php require_once __DIR__ . '/includes/alerts.php'; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0 ps-3 small">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="signup.php">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold small">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-label-input form-control bg-light border-start-0 ps-0" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Jane Doe" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold small">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="jane@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold small">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control bg-light border-start-0 ps-0" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="+1 (555) 000-0000" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold small">Password (Min 8 chars)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" minlength="8" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold small">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" class="form-control bg-light border-start-0 ps-0" id="confirm_password" name="confirm_password" minlength="8" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold mb-3 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Register Account
                        </button>

                        <div class="text-center">
                            <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="text-success fw-bold text-decoration-none">Sign In</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
