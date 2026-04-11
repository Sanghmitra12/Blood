<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('/index.php');

$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $email = sanitize($db, $_POST['email']);
    $password = $_POST['password'];

    $result = $db->query("SELECT * FROM users WHERE email='$email' LIMIT 1");
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['blood_group'] = $user['blood_group'];
            setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($user['role'] === 'admin' ? '/blood/admin/dashboard.php' : '/donor/profile.php');
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'No account found with this email.';
    }
}

include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Welcome Back</h1>
    <p>Login to your BloodLink Campus account</p>
</div>

<section class="section">
    <div class="container">
        <div class="form-card">
            <div class="form-title">Login</div>
            <div class="form-subtitle">Access your donor dashboard</div>

            <?php if ($error): ?>
            <div class="flash-message flash-error" style="margin-bottom:1.5rem;border-radius:8px">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div style="text-align:center;margin-top:1.5rem;color:var(--gray);font-size:0.88rem">
                <p>Don't have an account? <a href="/register.php" style="color:var(--red);font-weight:600">Register here</a></p>
                <hr style="margin:1.2rem 0;border-color:var(--gray-light)">
                <p><strong>Demo Accounts:</strong></p>
                <p>Admin: admin@university.edu / password</p>
                <p>Donor: rahul@student.edu / password</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
