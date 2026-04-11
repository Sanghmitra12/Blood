<?php $base = "/blood/"; ?>
<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';


requireLogin();
requireAdmin();
$pageTitle = 'Admin Dashboard';
$db = getDB();

$stats = [
    'donors' => $db->query("SELECT COUNT(*) as c FROM users WHERE role='donor'")->fetch_assoc()['c'],
    'available' => $db->query("SELECT COUNT(*) as c FROM users WHERE role='donor' AND is_available=1")->fetch_assoc()['c'],
    'requests' => $db->query("SELECT COUNT(*) as c FROM blood_requests WHERE status='Pending'")->fetch_assoc()['c'],
    'donations' => $db->query("SELECT COUNT(*) as c FROM donations")->fetch_assoc()['c'],
    'events' => $db->query("SELECT COUNT(*) as c FROM events WHERE status='Upcoming'")->fetch_assoc()['c'],
    'critical' => $db->query("SELECT COUNT(*) as c FROM blood_requests WHERE urgency='Critical' AND status='Pending'")->fetch_assoc()['c'],
];

$recent_requests = $db->query("SELECT * FROM blood_requests ORDER BY created_at DESC LIMIT 8");
$recent_donors = $db->query("SELECT * FROM users WHERE role='donor' ORDER BY created_at DESC LIMIT 5");

include '../includes/header.php';
?>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <h4>Main</h4>
            <a href="/blood/admin/dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/blood/admin/donors.php"><i class="fas fa-users"></i> Manage Donors</a>
            <a href="/blood/admin/requests.php"><i class="fas fa-tint"></i> Blood Requests</a>
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

    <!-- Content -->
    <div class="admin-content">
        <div style="margin-bottom:2rem">
            <h1 style="font-family:var(--font-display);font-size:2rem">Admin Dashboard</h1>
            <p style="color:var(--gray)">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here's an overview.</p>
        </div>

        <!-- Stat Cards -->
        <div class="grid-4" style="margin-bottom:2rem">
            <?php $stat_items = [
                ['Total Donors', $stats['donors'], 'fas fa-users', 'var(--charcoal)'],
                ['Available Donors', $stats['available'], 'fas fa-user-check', '#27ae60'],
                ['Pending Requests', $stats['requests'], 'fas fa-tint', 'var(--red)'],
                ['Critical Requests', $stats['critical'], 'fas fa-exclamation-triangle', '#e74c3c'],
            ];
            foreach($stat_items as $si): ?>
            <div class="card" style="border-left:4px solid <?php echo $si[3]; ?>">
                <div class="card-body" style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <div style="font-size:0.8rem;color:var(--gray);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px"><?php echo $si[0]; ?></div>
                        <div style="font-family:var(--font-display);font-size:2.2rem;font-weight:900;color:<?php echo $si[3]; ?>"><?php echo $si[1]; ?></div>
                    </div>
                    <i class="<?php echo $si[2]; ?>" style="font-size:2rem;color:<?php echo $si[3]; ?>;opacity:0.2"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="grid-2" style="align-items:start">
            <!-- Recent Requests -->
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                    <h3 style="font-family:var(--font-display)">Recent Blood Requests</h3>
                    <a href="/blood/admin/requests.php" class="btn btn-outline-dark btn-sm">View All</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Blood</th><th>Patient</th><th>Urgency</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php while($req = $recent_requests->fetch_assoc()): ?>
                            <tr>
                                <td><strong style="color:var(--red)"><?php echo $req['blood_group']; ?></strong></td>
                                <td><?php echo htmlspecialchars($req['patient_name']); ?></td>
                                <td><span class="urgency-badge urgency-<?php echo $req['urgency']; ?>"><?php echo $req['urgency']; ?></span></td>
                                <td><span class="status-badge status-<?php echo $req['status']; ?>"><?php echo $req['status']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Donors -->
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                    <h3 style="font-family:var(--font-display)">New Donors</h3>
                    <a href="/blood/admin/donors.php" class="btn btn-outline-dark btn-sm">View All</a>
                </div>
                <?php while($donor = $recent_donors->fetch_assoc()):
                    $bg_class = 'blood-' . str_replace(['+','-'], ['-pos','-neg'], $donor['blood_group']);
                    $initial = strtoupper(substr($donor['full_name'], 0, 1));
                ?>
                <div class="card" style="margin-bottom:0.75rem">
                    <div class="card-body" style="display:flex;align-items:center;gap:1rem;padding:0.9rem 1.2rem">
                        <div class="donor-avatar <?php echo $bg_class; ?>" style="width:40px;height:40px;font-size:1rem;margin:0;flex-shrink:0"><?php echo $initial; ?></div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:0.9rem"><?php echo htmlspecialchars($donor['full_name']); ?></div>
                            <div style="font-size:0.78rem;color:var(--gray)"><?php echo htmlspecialchars($donor['department'] ?? ''); ?></div>
                        </div>
                        <span class="blood-badge" style="font-size:0.85rem;padding:2px 10px"><?php echo $donor['blood_group']; ?></span>
                        <span class="availability-badge <?php echo $donor['is_available'] ? 'badge-available' : 'badge-unavailable'; ?>" style="font-size:0.75rem">
                            <?php echo $donor['is_available'] ? 'Available' : 'N/A'; ?>
                        </span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="margin-top:2rem;background:var(--charcoal);border-radius:var(--radius);padding:1.5rem">
            <h3 style="font-family:var(--font-display);color:var(--white);margin-bottom:1rem">Quick Actions</h3>
            <div style="display:flex;gap:1rem;flex-wrap:wrap">
                <a href="/blood/admin/events.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Event</a>
                <a href="/blood/admin/inventory.php" class="btn btn-gold btn-sm"><i class="fas fa-edit"></i> Update Inventory</a>
                <a href="/blood/admin/requests.php" class="btn btn-outline btn-sm"><i class="fas fa-tint"></i> View Requests</a>
                <a href="/blood/admin/donations.php?action=new" class="btn btn-outline btn-sm"><i class="fas fa-hand-holding-heart"></i> Log Donation</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
