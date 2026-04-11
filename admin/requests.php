<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
requireAdmin();
$pageTitle = 'Blood Requests';
$db = getDB();

// Update status
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = sanitize($db, $_GET['status']);
    if (in_array($status, ['Pending','Fulfilled','Cancelled'])) {
        $db->query("UPDATE blood_requests SET status='$status' WHERE id=$id");
        setFlash('success', "Request marked as $status.");
    }
    redirect('/admin/requests.php');
}

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM blood_requests WHERE id=$id");
    setFlash('success', 'Request deleted.');
    redirect('/admin/requests.php');
}

$filter = sanitize($db, $_GET['filter'] ?? '');
$blood = sanitize($db, $_GET['blood'] ?? '');
$where = "WHERE 1";
if ($filter) $where .= " AND status='$filter'";
if ($blood) $where .= " AND blood_group='$blood'";

$requests = $db->query("SELECT * FROM blood_requests $where ORDER BY urgency='Critical' DESC, urgency='Urgent' DESC, created_at DESC");

include '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <h4>Main</h4>
            <a href="/blood/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/blood/admin/donors.php"><i class="fas fa-users"></i> Manage Donors</a>
            <a href="/blood/admin/requests.php" class="active"><i class="fas fa-tint"></i> Blood Requests</a>
            <a href="/blood/admin/donations.php"><i class="fas fa-hand-holding-heart"></i> Donations Log</a>
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
        <div style="margin-bottom:2rem">
            <h1 style="font-family:var(--font-display);font-size:2rem">Blood Requests</h1>
            <p style="color:var(--gray)"><?php echo $requests->num_rows; ?> request(s)</p>
        </div>

        <!-- Filter Bar -->
        <form method="GET" class="search-bar" style="margin-bottom:1.5rem">
            <select name="filter" class="form-control">
                <option value="">All Statuses</option>
                <option value="Pending" <?php echo $filter==='Pending'?'selected':''; ?>>Pending</option>
                <option value="Fulfilled" <?php echo $filter==='Fulfilled'?'selected':''; ?>>Fulfilled</option>
                <option value="Cancelled" <?php echo $filter==='Cancelled'?'selected':''; ?>>Cancelled</option>
            </select>
            <select name="blood" class="form-control">
                <option value="">All Blood Groups</option>
                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                <option value="<?php echo $bg; ?>" <?php echo $blood===$bg?'selected':''; ?>><?php echo $bg; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="/blood/admin/requests.php" class="btn btn-outline-dark">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Requester</th><th>Patient</th><th>Blood</th><th>Units</th>
                        <th>Hospital</th><th>Urgency</th><th>Required By</th><th>Status</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests->num_rows === 0): ?>
                    <tr><td colspan="11" style="text-align:center;padding:2rem;color:var(--gray)">No requests found</td></tr>
                    <?php else: ?>
                    <?php $i=1; while($r = $requests->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($r['requester_name']); ?></strong><br>
                            <small style="color:var(--gray)"><?php echo htmlspecialchars($r['requester_phone']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($r['patient_name']); ?></td>
                        <td><strong style="color:var(--red);font-size:1.1rem"><?php echo $r['blood_group']; ?></strong></td>
                        <td><?php echo $r['units_needed']; ?></td>
                        <td style="font-size:0.85rem"><?php echo htmlspecialchars($r['hospital_name'] ?? '—'); ?></td>
                        <td><span class="urgency-badge urgency-<?php echo $r['urgency']; ?>"><?php echo $r['urgency']; ?></span></td>
                        <td style="font-size:0.85rem"><?php echo $r['required_date'] ? date('d M Y', strtotime($r['required_date'])) : '—'; ?></td>
                        <td><span class="status-badge status-<?php echo $r['status']; ?>"><?php echo $r['status']; ?></span></td>
                        <td style="font-size:0.8rem"><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                        <td style="white-space:nowrap">
                            <?php if ($r['status'] === 'Pending'): ?>
                            <a href="?id=<?php echo $r['id']; ?>&status=Fulfilled" class="btn btn-sm" style="background:#d4edda;color:#155724" title="Mark Fulfilled"><i class="fas fa-check"></i></a>
                            <a href="?id=<?php echo $r['id']; ?>&status=Cancelled" class="btn btn-sm" style="background:#fff3cd;color:#856404" title="Cancel"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $r['id']; ?>" class="btn btn-sm" style="background:#f8d7da;color:#721c24" onclick="return confirm('Delete this request?')" title="Delete"><i class="fas fa-trash"></i></a>
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
