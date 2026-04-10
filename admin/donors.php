<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
requireAdmin();
$pageTitle = 'Manage Donors';
$db = getDB();

// Toggle availability
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $db->query("UPDATE users SET is_available = 1 - is_available WHERE id=$id AND role='donor'");
    setFlash('success', 'Donor availability updated.');
    redirect('/admin/donors.php');
}

// Delete donor
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM users WHERE id=$id AND role='donor'");
    setFlash('success', 'Donor removed.');
    redirect('/admin/donors.php');
}

$search = sanitize($db, $_GET['search'] ?? '');
$blood = sanitize($db, $_GET['blood'] ?? '');

$where = "WHERE role='donor'";
if ($search) $where .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%' OR student_id LIKE '%$search%')";
if ($blood) $where .= " AND blood_group='$blood'";

$donors = $db->query("SELECT * FROM users $where ORDER BY created_at DESC");

include '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <h4>Main</h4>
            <a href="/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/admin/donors.php" class="active"><i class="fas fa-users"></i> Manage Donors</a>
            <a href="/admin/requests.php"><i class="fas fa-tint"></i> Blood Requests</a>
            <a href="/admin/donations.php"><i class="fas fa-hand-holding-heart"></i> Donations Log</a>
        </div>
        <div class="sidebar-section">
            <h4>Manage</h4>
            <a href="/admin/inventory.php"><i class="fas fa-warehouse"></i> Blood Inventory</a>
            <a href="/admin/events.php"><i class="fas fa-calendar-alt"></i> Events</a>
        </div>
        <div class="sidebar-section">
            <h4>Account</h4>
            <a href="/index.php"><i class="fas fa-home"></i> View Site</a>
            <a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="admin-content">
        <div style="margin-bottom:2rem">
            <h1 style="font-family:var(--font-display);font-size:2rem">Manage Donors</h1>
            <p style="color:var(--gray)"><?php echo $donors->num_rows; ?> donor(s) found</p>
        </div>

        <!-- Filters -->
        <form method="GET" class="search-bar" style="margin-bottom:1.5rem">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email or ID..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="blood" class="form-control">
                <option value="">All Blood Groups</option>
                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                <option value="<?php echo $bg; ?>" <?php echo $blood === $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="/admin/donors.php" class="btn btn-outline-dark">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Blood</th><th>Dept</th><th>ID</th><th>Available</th><th>Joined</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($donors->num_rows === 0): ?>
                    <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--gray)">No donors found</td></tr>
                    <?php else: ?>
                    <?php $i = 1; while($d = $donors->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($d['full_name']); ?></strong></td>
                        <td style="font-size:0.85rem"><?php echo htmlspecialchars($d['email']); ?></td>
                        <td><?php echo htmlspecialchars($d['phone'] ?? '—'); ?></td>
                        <td><strong style="color:var(--red)"><?php echo $d['blood_group']; ?></strong></td>
                        <td><?php echo htmlspecialchars($d['department'] ?? '—'); ?></td>
                        <td style="font-size:0.85rem"><?php echo htmlspecialchars($d['student_id'] ?? '—'); ?></td>
                        <td>
                            <a href="?toggle=<?php echo $d['id']; ?>" class="availability-badge <?php echo $d['is_available'] ? 'badge-available' : 'badge-unavailable'; ?>" style="text-decoration:none;cursor:pointer">
                                <?php echo $d['is_available'] ? 'Yes' : 'No'; ?>
                            </a>
                        </td>
                        <td style="font-size:0.82rem"><?php echo date('d M Y', strtotime($d['created_at'])); ?></td>
                        <td>
                            <a href="?delete=<?php echo $d['id']; ?>" class="btn btn-sm" style="background:#f8d7da;color:#721c24" onclick="return confirm('Delete this donor?')">
                                <i class="fas fa-trash"></i>
                            </a>
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
