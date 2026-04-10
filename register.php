
<?php $base = "/blood/"; ?>
<?php


require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('/index.php');

$pageTitle = 'Register as Donor';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    $name = sanitize($db, $_POST['full_name']);
    $email = sanitize($db, $_POST['email']);
    $password = $_POST['password'];
    $phone = sanitize($db, $_POST['phone']);
    $blood_group = sanitize($db, $_POST['blood_group']);
    $age = intval($_POST['age']);
    $gender = sanitize($db, $_POST['gender']);
    $department = sanitize($db, $_POST['department']);
    $student_id = sanitize($db, $_POST['student_id']);
    $address = sanitize($db, $_POST['address']);

    if (!$name) $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if (!$blood_group) $errors[] = 'Blood group is required.';
    if ($age < 18 || $age > 65) $errors[] = 'Age must be between 18 and 65.';

    if (empty($errors)) {
        // Check email exists
        $check = $db->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $errors[] = 'This email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (full_name, email, password, phone, blood_group, age, gender, department, student_id, address) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssissss', $name, $email, $hashed, $phone, $blood_group, $age, $gender, $department, $student_id, $address);
            if ($stmt->execute()) {
                setFlash('success', 'Registration successful! You can now login.');
                //redirect('/login.php');
                redirect('/blood/login.php');

            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Donor Registration</h1>
    <p>Join our campus blood donation network and save lives</p>
</div>

<section class="section">
    <div class="container">
        <div style="max-width:700px;margin:0 auto">
            <div class="card">
                <div class="card-header-strip">
                    <h3 style="font-family:var(--font-display)"><i class="fas fa-user-plus"></i> Create Donor Account</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="flash-message flash-error" style="margin-bottom:1.5rem;border-radius:8px">
                        <ul style="margin:0;padding-left:20px">
                            <?php foreach ($errors as $e): ?><li><?php echo $e; ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Blood Group *</label>
                                <select name="blood_group" class="form-control" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                                    <option value="<?php echo $bg; ?>" <?php echo (($_POST['blood_group'] ?? '') === $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Age *</label>
                                <input type="number" name="age" class="form-control" min="18" max="65" value="<?php echo intval($_POST['age'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>" placeholder="e.g. Computer Science">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Student / Staff ID</label>
                            <input type="text" name="student_id" class="form-control" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                        <p style="font-size:0.85rem;color:var(--gray);margin-bottom:1rem">
                            <i class="fas fa-shield-alt"></i> By registering, you confirm you are eligible to donate blood and are at least 18 years old.
                        </p>
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                            <i class="fas fa-user-plus"></i> Register as Donor
                        </button>
                    </form>

                    <p style="text-align:center;margin-top:1.5rem;font-size:0.9rem;color:var(--gray)">
                        Already have an account? <a href="/login.php" style="color:var(--red);font-weight:600">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
