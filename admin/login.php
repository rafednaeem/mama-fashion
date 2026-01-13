<?php
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    redirect('/admin/dashboard.php');
}

$pageTitle = 'Admin Login';
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
        $stmt = $pdo->prepare('SELECT id, name, password FROM admin WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        $valid = false;
        if ($admin) {
            if (password_verify($password, $admin['password'])) {
                $valid = true;
            } elseif ($admin['password'] === $password) {
                $valid = true;
            }
        }

        if (!$valid) {
            $errors[] = 'Invalid admin credentials.';
        } else {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            redirect('/admin/dashboard.php');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($pageTitle); ?> | MAMA Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/style.css">
</head>
<body>
    <main class="page-wrapper">
        <div class="container">
            <div class="form-card" style="margin-top:40px;">
                <h1 class="form-title">Admin login</h1>
                <p class="form-subtitle">
                    Restricted area for managing products and orders.
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
                        <label for="email">Admin email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo e($email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-small">Login</button>
                    <p class="form-help" style="margin-top:8px;">
                        Default (change later): admin@mama.local / admin123
                    </p>
                </form>
            </div>
        </div>
    </main>
</body>
</html>

