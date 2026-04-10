<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$pageTitle = 'Blood Inventory';
$db = getDB();

$inventory = $db->query("SELECT bi.*, COUNT(u.id) as donors FROM blood_inventory bi LEFT JOIN users u ON u.blood_group=bi.blood_group AND u.role='donor' AND u.is_available=1 GROUP BY bi.blood_group ORDER BY bi.blood_group");

include '../includes/header.php';
?>

<div class="page-hero">
    <h1>Blood Bank Inventory</h1>
    <p>Real-time blood stock levels at our campus health centre</p>
</div>

<section class="section">
    <div class="container">
        <div style="background:var(--white);border-radius:var(--radius);padding:2rem;box-shadow:var(--shadow);margin-bottom:3rem">
            <h3 style="font-family:var(--font-display);margin-bottom:1.5rem;color:var(--charcoal)">
                <i class="fas fa-chart-bar" style="color:var(--red)"></i> Stock Overview
            </h3>
            <div class="grid-4">
                <?php $inventory_data = []; while($inv = $inventory->fetch_assoc()) { $inventory_data[] = $inv; } ?>
                <?php foreach($inventory_data as $inv):
                    $bg_class = 'blood-' . str_replace(['+','-'], ['-pos','-neg'], $inv['blood_group']);
                    $pct = min(100, ($inv['units_available'] / 30) * 100);
                    if ($inv['units_available'] === 0) $status = ['Critical', '#dc3545'];
                    elseif ($inv['units_available'] < 5) $status = ['Low', '#fd7e14'];
                    elseif ($inv['units_available'] < 15) $status = ['Moderate', '#ffc107'];
                    else $status = ['Good', '#28a745'];
                ?>
                <div style="text-align:center;padding:1.5rem;border:2px solid var(--gray-light);border-radius:var(--radius)">
                    <div class="donor-avatar <?php echo $bg_class; ?>" style="width:72px;height:72px;font-size:1.6rem;font-weight:900;margin:0 auto 1rem">
                        <?php echo $inv['blood_group']; ?>
                    </div>
                    <div style="font-size:2.2rem;font-family:var(--font-display);font-weight:900;color:var(--charcoal)"><?php echo $inv['units_available']; ?></div>
                    <div style="font-size:0.8rem;color:var(--gray);margin-bottom:8px">units available</div>
                    <div style="height:8px;background:var(--gray-light);border-radius:4px;margin-bottom:8px;overflow:hidden">
                        <div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo $status[1]; ?>;border-radius:4px;transition:width 1s"></div>
                    </div>
                    <span style="font-size:0.75rem;font-weight:700;color:<?php echo $status[1]; ?>"><?php echo $status[0]; ?></span>
                    <div style="font-size:0.78rem;color:var(--gray);margin-top:4px"><i class="fas fa-users"></i> <?php echo $inv['donors']; ?> donors available</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Blood Group</th>
                        <th>Units Available</th>
                        <th>Available Donors</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($inventory_data as $inv):
                        if ($inv['units_available'] === 0) $status = ['Critical', '#dc3545'];
                        elseif ($inv['units_available'] < 5) $status = ['Low', '#fd7e14'];
                        elseif ($inv['units_available'] < 15) $status = ['Moderate', '#ffc107'];
                        else $status = ['Good', '#28a745'];
                    ?>
                    <tr>
                        <td><strong style="font-size:1.1rem;color:var(--red)"><?php echo $inv['blood_group']; ?></strong></td>
                        <td><?php echo $inv['units_available']; ?> units</td>
                        <td><?php echo $inv['donors']; ?> donors</td>
                        <td><span style="font-weight:700;color:<?php echo $status[1]; ?>"><?php echo $status[0]; ?></span></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($inv['updated_at'])); ?></td>
                        <td>
                            <a href="/blood/pages/donors.php?blood=<?php echo urlencode($inv['blood_group']); ?>" class="btn btn-outline-dark btn-sm">Find Donors</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="background:var(--red-pale);border-left:4px solid var(--red);padding:1.2rem 1.5rem;border-radius:0 8px 8px 0;margin-top:2rem">
            <p style="color:var(--red-dark);font-size:0.9rem"><i class="fas fa-info-circle"></i> <strong>Note:</strong> Inventory is updated after each donation. Contact the Health Centre for emergency requirements. Campus Health Centre: +91 98765 43210 (open Mon–Sat, 9AM–5PM)</p>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
