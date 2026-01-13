<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Register';

$name = $email = $password = $phone = $address = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($password === '' || strlen($password) < 4) {
        $errors[] = 'Password should be at least 4 characters (for demo).';
    }

    if (empty($errors)) {
        $pdo = getPDO();

        // Check if email exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, phone, address) 
                                   VALUES (:name, :email, :password, :phone, :address)');
            // Store a hashed password for better security.
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([
                ':name'     => $name,
                ':email'    => $email,
                ':password' => $hashed,
                ':phone'    => $phone,
                ':address'  => $address,
            ]);
            $success = 'Account created. You can now log in.';
            $name = $email = $password = $phone = $address = '';
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1 class="form-title">Create an account</h1>
            <p class="form-subtitle">
                Save your delivery details and track your orders for future purchases.
            </p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo e($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo e($success); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                <div class="form-group">
                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone (Pakistan)</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo e($phone); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Default delivery address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo e($address); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-small">Sign up</button>
                <p class="form-help" style="margin-top:8px;">
                    Already have an account? <a href="<?php echo $baseUrl; ?>/login.php">Log in</a>.
                </p>
            </form>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

