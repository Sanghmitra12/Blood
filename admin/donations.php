<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
requireAdmin();
$pageTitle = 'Donations Log';
$db = getDB();

$action = $_GET['action'] ?? '';
$errors = [];

// Add donation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_donation'])) {
    $donor_id = intval($_POST['donor_id']);
    $donation_date = sanitize($db, $_POST['donation_date']);
    $units = intval($_POST['units']);
    $hospital = sanitize($db, $_POST['hospital']);
    $notes = sanitize($db, $_POST['notes']);
    $blood_group = sanitize($db, $_POST['blood_group']);

    if (!$donation_date) $errors[] = 'Date is required.';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO donations (donor_id, donation_date, units, hospital, notes) VALUES (?,?,?,?,?)");
        $stmt->bind_param('isiss', $donor_id, $donation_date, $units, $hospital, $notes);
        $stmt->execute();

        // Update last donation date for donor
        if ($donor_id) {
            $db->query("UPDATE users SET last_donation='$donation_date' WHERE id=$donor_id");
        }

        // Update inventory
        if ($blood_group) {
            $db->query("UPDATE blood_inventory SET units_available = units_available + $units WHERE blood_group='$blood_group'");
        }

        setFlash('success', 'Donation logged successfully and inventory updated!');
        redirect('/blood/admin/donations.php');
    }
}

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM donations WHERE id=$id");
    setFlash('success', 'Donation record deleted.');
    redirect('/blood/admin/donations.php');
}

$donations = $db->query("SELECT d.*, u.full_name, u.blood_group as donor_blood FROM donations d LEFT JOIN users u ON u.id=d.donor_id ORDER BY d.donation_date DESC");
$donors = $db->query("SELECT id, full_name, blood_group FROM users WHERE role='donor' ORDER BY full_name");

include '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <h4>Main</h4>
            <a href="/blood/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/blood/admin/donors.php"><i class="fas fa-users"></i> Manage Donors</a>
            <a href="/blood/admin/requests.php"><i class="fas fa-tint"></i> Blood Requests</a>
            <a href="/blood/admin/donations.php" class="active"><i class="fas fa-hand-holding-heart"></i> Donations Log</a>
        </div>
        <div class="sidebar-section">
            <h4>Manage</h4>
            <a href="/blood/admin/inventory.php"><i class="fas fa-warehouse"></i> Blood Inventory</a>
            <a href="/blood/admin/events.php"><i class="fas fa-calendar-alt"></i> Events</a>
        </div>
        <div class="sidebar-section">
            <h4>Account</h4>
            <a href="/blood/index.php"><i class="fas fa-home"></i> View Site</a>
            <a href="/blood/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="admin-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
            <div>
                <h1 style="font-family:var(--font-display);font-size:2rem">Donations Log</h1>
                <p style="color:var(--gray)">Track all blood donations</p>
            </div>
            <a href="?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Log Donation</a>
        </div>

        <!-- Add Donation Form -->
        <?php if ($action === 'new'): ?>
        <div class="card" style="margin-bottom:2rem">
            <div class="card-header-strip"><h3 style="font-family:var(--font-display)"><i class="fas fa-plus"></i> Log New Donation</h3></div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error" style="margin-bottom:1rem;border-radius:8px">
                    <?php foreach($errors as $e): ?><p><?php echo $e; ?></p><?php endforeach; ?>
                </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="add_donation" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Donor (Optional)</label>
                            <select name="donor_id" class="form-control" id="donorSelect" onchange="setBloodGroup(this)">
                                <option value="0">-- Anonymous / Walk-in --</option>
                                <?php $donors_list = []; while($d = $donors->fetch_assoc()) { $donors_list[] = $d; } ?>
                                <?php foreach($donors_list as $d): ?>
                                <option value="<?php echo $d['id']; ?>" data-blood="<?php echo $d['blood_group']; ?>">
                                    <?php echo htmlspecialchars($d['full_name']); ?> (<?php echo $d['blood_group']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Blood Group *</label>
                            <select name="blood_group" class="form-control" id="bloodGroupSelect">
                                <option value="">-- Select --</option>
                                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                                <option value="<?php echo $bg; ?>"><?php echo $bg; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Donation Date *</label>
                            <input type="date" name="donation_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Units Donated</label>
                            <input type="number" name="units" class="form-control" value="1" min="1" max="5">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Hospital / Location</label>
                        <input type="text" name="hospital" class="form-control" value="Campus Health Centre">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div style="display:flex;gap:1rem">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Donation</button>
                        <a href="/blood/admin/donations.php" class="btn btn-outline-dark">Cancel</a>
                    </div>
                </form>
                <script>
                function setBloodGroup(sel) {
                    const bg = sel.options[sel.selectedIndex].dataset.blood;
                    if (bg) document.getElementById('bloodGroupSelect').value = bg;
                }
                </script>
            </div>
        </div>
        <?php endif; ?>

        <!-- Donations Table -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Donor</th><th>Blood Group</th><th>Date</th><th>Units</th><th>Hospital</th><th>Notes</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if ($donations->num_rows === 0): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--gray)">No donation records yet</td></tr>
                    <?php else: ?>
                    <?php $i=1; while($don = $donations->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $don['full_name'] ? htmlspecialchars($don['full_name']) : '<em style="color:var(--gray)">Anonymous</em>'; ?></td>
                        <td><strong style="color:var(--red)"><?php echo $don['donor_blood'] ?? '—'; ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($don['donation_date'])); ?></td>
                        <td><?php echo $don['units']; ?></td>
                        <td><?php echo htmlspecialchars($don['hospital'] ?? 'Campus Health Centre'); ?></td>
                        <td style="font-size:0.85rem;color:var(--gray)"><?php echo htmlspecialchars($don['notes'] ?? '—'); ?></td>
                        <td>
                            <a href="?delete=<?php echo $don['id']; ?>" class="btn btn-sm" style="background:#f8d7da;color:#721c24" onclick="return confirm('Delete this record?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
