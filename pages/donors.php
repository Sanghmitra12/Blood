<?php 
$base = "/blood/";

require_once '../includes/auth.php';
require_once '../includes/db.php';

$pageTitle = 'Find Donors';
$db = getDB();

$blood_filter = isset($_GET['blood']) ? sanitize($db, $_GET['blood']) : '';
$dept_filter = isset($_GET['dept']) ? sanitize($db, $_GET['dept']) : '';
$search = isset($_GET['search']) ? sanitize($db, $_GET['search']) : '';

$where = "WHERE role='donor' AND is_available=1";
if ($blood_filter) $where .= " AND blood_group='$blood_filter'";
if ($dept_filter) $where .= " AND department LIKE '%$dept_filter%'";
if ($search) $where .= " AND (full_name LIKE '%$search%' OR student_id LIKE '%$search%')";

$donors = $db->query("SELECT * FROM users $where ORDER BY full_name");
$departments = $db->query("SELECT DISTINCT department FROM users WHERE role='donor' AND department IS NOT NULL ORDER BY department");

include '../includes/header.php';
?>

<div class="page-hero">
    <h1>Find Blood Donors</h1>
    <p>Search for available donors on our campus</p>
</div>

<section class="section">
    <div class="container">

        <!-- Search & Filter -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="<?php echo htmlspecialchars($search); ?>">

            <select name="blood" class="form-control">
                <option value="">All Blood Groups</option>
                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                    <option value="<?php echo $bg; ?>" <?php echo $blood_filter === $bg ? 'selected' : ''; ?>>
                        <?php echo $bg; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="dept" class="form-control">
                <option value="">All Departments</option>
                <?php while($d = $departments->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($d['department']); ?>" <?php echo $dept_filter === $d['department'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['department']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>

            <!-- ✅ FIXED RESET -->
            <a href="<?php echo $base; ?>pages/donors.php" class="btn btn-outline-dark">
                Reset
            </a>
        </form>

        <!-- Results -->
        <?php if ($donors->num_rows === 0): ?>
            <div style="text-align:center;padding:4rem;color:var(--gray)">
                <i class="fas fa-user-slash" style="font-size:3rem;color:var(--red-light);margin-bottom:1rem;display:block"></i>
                <h3>No donors found</h3>
                <p>Try adjusting your search filters</p>
            </div>
        <?php else: ?>

            <p style="color:var(--gray);margin-bottom:1.5rem">
                <?php echo $donors->num_rows; ?> donor(s) found
            </p>

            <div class="grid-3">
                <?php while($donor = $donors->fetch_assoc()):
                    $bg_class = 'blood-' . str_replace(['+','-'], ['-pos','-neg'], $donor['blood_group']);
                    $initial = strtoupper(substr($donor['full_name'], 0, 1));
                ?>

                <div class="card donor-card">
                    <div class="card-body">
                        <div class="donor-avatar <?php echo $bg_class; ?>">
                            <?php echo $initial; ?>
                        </div>

                        <div class="donor-name">
                            <?php echo htmlspecialchars($donor['full_name']); ?>
                        </div>

                        <div class="donor-dept">
                            <?php echo htmlspecialchars($donor['department'] ?? 'N/A'); ?>
                        </div>

                        <?php if ($donor['student_id']): ?>
                            <div style="font-size:0.8rem;color:var(--gray);margin-bottom:6px">
                                ID: <?php echo htmlspecialchars($donor['student_id']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="blood-badge">
                            <?php echo $donor['blood_group']; ?>
                        </div><br>

                        <span class="availability-badge badge-available">✓ Available</span>

                        <?php if ($donor['phone'] && isLoggedIn()): ?>
                            <div style="margin-top:1rem">
                                <a href="tel:<?php echo $donor['phone']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-phone"></i> Contact
                                </a>
                            </div>

                        <?php elseif (!isLoggedIn()): ?>
                            <div style="margin-top:1rem">
                                <!-- ✅ FIXED LOGIN LINK -->
                                <a href="<?php echo $base; ?>login.php" class="btn btn-outline-dark btn-sm">
                                    Login to Contact
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <?php endwhile; ?>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
