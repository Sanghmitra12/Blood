<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
requireAdmin();
$pageTitle = 'Blood Inventory';
$db = getDB();

// Update inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['units'] as $blood_group => $units) {
        $bg = $db->real_escape_string($blood_group);
        $u = max(0, intval($units));
        $db->query("UPDATE blood_inventory SET units_available=$u WHERE blood_group='$bg'");
    }
    setFlash('success', 'Inventory updated successfully!');
    redirect('/admin/inventory.php');
}

$inventory = $db->query("SELECT bi.*, COUNT(u.id) as donors FROM blood_inventory bi LEFT JOIN users u ON u.blood_group=bi.blood_group AND u.role='donor' AND u.is_available=1 GROUP BY bi.blood_group ORDER BY bi.blood_group");
$inv_data = [];
while ($row = $inventory->fetch_assoc()) $inv_data[] = $row;

include '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <h4>Main</h4>
            <a href="/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/admin/donors.php"><i class="fas fa-users"></i> Manage Donors</a>
            <a href="/admin/requests.php"><i class="fas fa-tint"></i> Blood Requests</a>
            <a href="/admin/donations.php"><i class="fas fa-hand-holding-heart"></i> Donations Log</a>
        </div>
        <div class="sidebar-section">
            <h4>Manage</h4>
            <a href="/admin/inventory.php" class="active"><i class="fas fa-warehouse"></i> Blood Inventory</a>
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
            <h1 style="font-family:var(--font-display);font-size:2rem">Blood Inventory</h1>
            <p style="color:var(--gray)">Update blood stock levels at the campus health centre</p>
        </div>

        <form method="POST">
            <div class="grid-4" style="margin-bottom:2rem">
                <?php foreach ($inv_data as $inv):
                    $bg_class = 'blood-' . str_replace(['+','-'], ['-pos','-neg'], $inv['blood_group']);
                    if ($inv['units_available'] === 0) { $status = 'Critical'; $sc = '#dc3545'; }
                    elseif ($inv['units_available'] < 5) { $status = 'Low'; $sc = '#fd7e14'; }
                    elseif ($inv['units_available'] < 15) { $status = 'Moderate'; $sc = '#ffc107'; }
                    else { $status = 'Good'; $sc = '#28a745'; }
                    $pct = min(100, ($inv['units_available'] / 30) * 100);
                ?>
                <div class="card" style="border-top:4px solid <?php echo $sc; ?>">
                    <div class="card-body" style="text-align:center">
                        <div class="donor-avatar <?php echo $bg_class; ?>" style="width:64px;height:64px;font-size:1.3rem;font-weight:900;margin:0 auto 1rem">
                            <?php echo $inv['blood_group']; ?>
                        </div>
                        <div style="font-size:0.8rem;font-weight:700;color:<?php echo $sc; ?>;margin-bottom:8px"><?php echo $status; ?></div>
                        <div style="height:6px;background:var(--gray-light);border-radius:3px;margin-bottom:12px">
                            <div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo $sc; ?>;border-radius:3px"></div>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label style="font-size:0.8rem">Units Available</label>
                            <input type="number" name="units[<?php echo $inv['blood_group']; ?>]" class="form-control" value="<?php echo $inv['units_available']; ?>" min="0" max="999" style="text-align:center;font-size:1.2rem;font-weight:700">
                        </div>
                        <div style="font-size:0.78rem;color:var(--gray);margin-top:8px">
                            <i class="fas fa-users"></i> <?php echo $inv['donors']; ?> donors available
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Changes</button>
        </form>

        <!-- History Table -->
        <div style="margin-top:3rem">
            <h3 style="font-family:var(--font-display);margin-bottom:1.5rem">Current Inventory Summary</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Blood Group</th><th>Units Available</th><th>Available Donors</th><th>Status</th><th>Last Updated</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inv_data as $inv):
                            if ($inv['units_available'] === 0) { $status = 'Critical'; $sc = '#dc3545'; }
                            elseif ($inv['units_available'] < 5) { $status = 'Low'; $sc = '#fd7e14'; }
                            elseif ($inv['units_available'] < 15) { $status = 'Moderate'; $sc = '#ffc107'; }
                            else { $status = 'Good'; $sc = '#28a745'; }
                        ?>
                        <tr>
                            <td><strong style="color:var(--red);font-size:1.15rem"><?php echo $inv['blood_group']; ?></strong></td>
                            <td><?php echo $inv['units_available']; ?> units</td>
                            <td><?php echo $inv['donors']; ?></td>
                            <td><span style="font-weight:700;color:<?php echo $sc; ?>"><?php echo $status; ?></span></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($inv['updated_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
