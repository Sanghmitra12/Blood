<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
$pageTitle = 'My Profile';
$db = getDB();

$user_id = $_SESSION['user_id'];
$user = $db->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($db, $_POST['phone']);
    $department = sanitize($db, $_POST['department']);
    $address = sanitize($db, $_POST['address']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $last_donation = sanitize($db, $_POST['last_donation']);

    $db->query("UPDATE users SET phone='$phone', department='$department', address='$address', is_available=$is_available, last_donation=" . ($last_donation ? "'$last_donation'" : "NULL") . " WHERE id=$user_id");
    setFlash('success', 'Profile updated successfully!');
    redirect('/blood/donor/profile.php');
}

// Donation history
$donations = $db->query("SELECT d.*, br.blood_group, br.patient_name FROM donations d LEFT JOIN blood_requests br ON br.id=d.request_id WHERE d.donor_id=$user_id ORDER BY d.donation_date DESC");

include '../includes/header.php';
?>

<div class="page-hero">
    <h1>My Donor Profile</h1>
    <p>Manage your donation details and availability</p>
</div>

<section class="section">
    <div class="container">
        <div class="grid-2" style="align-items:start">
            <!-- Profile Card -->
            <div>
                <div class="card">
                    <div class="card-header-strip" style="display:flex;align-items:center;gap:1rem">
                        <?php
                        $bg_class = 'blood-' . str_replace(['+','-'], ['-pos','-neg'], $user['blood_group']);
                        $initial = strtoupper(substr($user['full_name'], 0, 1));
                        ?>
                        <div class="donor-avatar <?php echo $bg_class; ?>" style="width:56px;height:56px;font-size:1.3rem;margin:0">
                            <?php echo $initial; ?>
                        </div>
                        <div>
                            <h3 style="font-family:var(--font-display)"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
                            <div style="text-align:center;padding:1rem;background:var(--red-pale);border-radius:8px;flex:1">
                                <div style="font-family:var(--font-display);font-size:1.8rem;font-weight:900;color:var(--red)"><?php echo $user['blood_group']; ?></div>
                                <div style="font-size:0.78rem;color:var(--gray)">Blood Group</div>
                            </div>
                            <div style="text-align:center;padding:1rem;background:<?php echo $user['is_available'] ? '#d4edda' : '#f8d7da'; ?>;border-radius:8px;flex:1">
                                <div style="font-size:1.5rem"><?php echo $user['is_available'] ? '✓' : '✗'; ?></div>
                                <div style="font-size:0.78rem;color:var(--gray)"><?php echo $user['is_available'] ? 'Available' : 'Unavailable'; ?></div>
                            </div>
                        </div>

                        <form method="POST">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Last Donation Date</label>
                                <input type="date" name="last_donation" class="form-control" value="<?php echo $user['last_donation'] ?? ''; ?>" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group" style="display:flex;align-items:center;gap:10px">
                                <input type="checkbox" name="is_available" id="is_available" <?php echo $user['is_available'] ? 'checked' : ''; ?> style="width:18px;height:18px">
                                <label for="is_available" style="margin:0">I am available to donate blood</label>
                            </div>
                            <?php if ($user['last_donation']): ?>
                            <?php
                                $lastDate = new DateTime($user['last_donation']);
                                $now = new DateTime();
                                $diff = $now->diff($lastDate);
                                $daysSince = $diff->days;
                                $canDonate = $daysSince >= 90;
                            ?>
                            <div style="padding:10px;border-radius:8px;background:<?php echo $canDonate ? '#d4edda' : '#fff3cd'; ?>;font-size:0.85rem;margin-bottom:1rem">
                                <i class="fas fa-<?php echo $canDonate ? 'check-circle' : 'clock'; ?>"></i>
                                Last donated <?php echo $daysSince; ?> days ago.
                                <?php echo $canDonate ? 'You are eligible to donate again!' : 'You need to wait ' . (90 - $daysSince) . ' more days.'; ?>
                            </div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>

                        <div style="margin-top:1rem;text-align:center">
                            <div style="font-size:0.8rem;color:var(--gray)">Member since <?php echo date('d M Y', strtotime($user['created_at'])); ?></div>
                            <?php if($user['student_id']): ?><div style="font-size:0.8rem;color:var(--gray)">ID: <?php echo htmlspecialchars($user['student_id']); ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation History -->
            <div>
                <h3 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:1.5rem">
                    <i class="fas fa-history" style="color:var(--red)"></i> Donation History
                </h3>
                <?php if ($donations->num_rows === 0): ?>
                <div style="text-align:center;padding:3rem;background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow)">
                    <i class="fas fa-heart" style="font-size:2.5rem;color:var(--red-light);display:block;margin-bottom:0.5rem"></i>
                    <p style="color:var(--gray)">No donation records yet.<br>Make your first donation!</p>
                </div>
                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>Hospital</th><th>Units</th><th>Notes</th></tr>
                        </thead>
                        <tbody>
                            <?php while($don = $donations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($don['donation_date'])); ?></td>
                                <td><?php echo htmlspecialchars($don['hospital'] ?? 'Campus Health Centre'); ?></td>
                                <td><?php echo $don['units']; ?></td>
                                <td style="font-size:0.85rem;color:var(--gray)"><?php echo htmlspecialchars($don['notes'] ?? '—'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
