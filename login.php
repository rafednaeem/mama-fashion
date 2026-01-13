<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Login';

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        $valid = false;
        if ($user) {
            // Support both hashed (new) and plain (older sample) passwords.
            if (password_verify($password, $user['password'])) {
                $valid = true;
            } elseif ($user['password'] === $password) {
                $valid = true;
            }
        }

        if (!$valid) {
            $errors[] = 'Invalid email or password.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            redirect('/'); // back to home
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1 class="form-title">Login</h1>
            <p class="form-subtitle">
                Access your saved details and track your orders.
            </p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo e($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-small">Login</button>
                <p class="form-help" style="margin-top:8px;">
                    New here? <a href="<?php echo $baseUrl; ?>/register.php">Create an account</a>.
                </p>
            </form>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

