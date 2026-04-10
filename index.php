<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'includes/auth.php';
require_once 'includes/db.php';

$pageTitle = 'Home';
$db = getDB();

// Stats
$totalDonors = $db->query("SELECT COUNT(*) as c FROM users WHERE role='donor'")->fetch_assoc()['c'];
$availableDonors = $db->query("SELECT COUNT(*) as c FROM users WHERE role='donor' AND is_available=1")->fetch_assoc()['c'];
$totalDonations = $db->query("SELECT COUNT(*) as c FROM donations")->fetch_assoc()['c'];
$totalRequests = $db->query("SELECT COUNT(*) as c FROM blood_requests")->fetch_assoc()['c'];

// Recent donors
$recentDonors = $db->query("SELECT * FROM users WHERE role='donor' ORDER BY created_at DESC LIMIT 6");

// Upcoming events
$events = $db->query("SELECT * FROM events WHERE status='Upcoming' ORDER BY event_date ASC LIMIT 3");

// Inventory
$inventory = $db->query("SELECT * FROM blood_inventory ORDER BY blood_group");

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-tag">🏥 University Campus Blood Network</div>
    <h1>Give Blood,<br><span>Save Lives</span> on Campus</h1>
    <p>Connect with fellow students and staff. One donation can save up to 3 lives. Be a hero in your community.</p>
    <div class="hero-actions">
        <a href="/blood/register.php" class="btn btn-primary"><i class="fas fa-hand-holding-heart"></i> Become a Donor</a>
        <a href="/blood/pages/request.php" class="btn btn-outline"><i class="fas fa-tint"></i> Request Blood</a>
        <a href="/blood/pages/donors.php" class="btn btn-outline"><i class="fas fa-search"></i> Find Donors</a>
    </div>
</section>

<!-- STATS -->
<div class="stats-strip">
    <div class="stat-item">
        <div class="stat-num pulse" data-target="<?php echo $totalDonors; ?>"><?php echo $totalDonors; ?></div>
        <div class="stat-label">Registered Donors</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" style="color:var(--gold)" data-target="<?php echo $availableDonors; ?>"><?php echo $availableDonors; ?></div>
        <div class="stat-label">Available Now</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="<?php echo $totalDonations; ?>"><?php echo $totalDonations; ?></div>
        <div class="stat-label">Total Donations</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="<?php echo $totalRequests; ?>"><?php echo $totalRequests; ?></div>
        <div class="stat-label">Blood Requests</div>
    </div>
</div>

<!-- BLOOD INVENTORY -->
<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <div class="hero-tag" style="background:var(--red-pale);color:var(--red);border-color:var(--red-light)">Live Inventory</div>
            <h2>Blood Stock Status</h2>
            <div class="divider"></div>
            <p>Current units available in the campus blood bank</p>
        </div>
        <div class="grid-4">
            <?php while($inv = $inventory->fetch_assoc()):
                $bg_class = 'blood-' . str_replace(['+','-'], ['-pos','-neg'], $inv['blood_group']);
                $pct = min(100, ($inv['units_available'] / 30) * 100);
                $color = $inv['units_available'] < 5 ? '#e74c3c' : ($inv['units_available'] < 10 ? '#f39c12' : '#27ae60');
            ?>
            <a href="/blood/pages/inventory.php" class="blood-group-card" style="text-decoration:none;color:inherit">
                <div class="donor-avatar <?php echo $bg_class; ?>" style="width:64px;height:64px;font-size:1.4rem;margin-bottom:0.8rem">
                    <?php echo $inv['blood_group']; ?>
                </div>
                <div class="blood-group-symbol"><?php echo $inv['blood_group']; ?></div>
                <div class="blood-group-units"><?php echo $inv['units_available']; ?> units</div>
                <div class="blood-group-bar" style="background:<?php echo $color; ?>;width:<?php echo $pct; ?>%"></div>
            </a>
            <?php endwhile; ?>
        </div>
        <div style="text-align:center;margin-top:2rem">
            <a href="/blood/pages/inventory.php" class="btn btn-outline-dark">View Full Inventory</a>
        </div>
    </div>
</section>

<!-- RECENT DONORS -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <h2>Our Donors</h2>
            <div class="divider"></div>
            <p>Meet the heroes on our campus</p>
        </div>
        <div class="grid-3">
            <?php while($donor = $recentDonors->fetch_assoc()):
                $bg_class = 'blood-' . str_replace(['+','-'], ['-pos','-neg'], $donor['blood_group']);
                $initial = strtoupper(substr($donor['full_name'], 0, 1));
            ?>
            <div class="donor-card-wrap" data-blood="<?php echo $donor['blood_group']; ?>">
                <div class="card donor-card">
                    <div class="card-body">
                        <div class="donor-avatar <?php echo $bg_class; ?>"><?php echo $initial; ?></div>
                        <div class="donor-name"><?php echo htmlspecialchars($donor['full_name']); ?></div>
                        <div class="donor-dept"><?php echo htmlspecialchars($donor['department'] ?? 'N/A'); ?></div>
                        <div class="blood-badge"><?php echo $donor['blood_group']; ?></div><br>
                        <span class="availability-badge <?php echo $donor['is_available'] ? 'badge-available' : 'badge-unavailable'; ?>">
                            <?php echo $donor['is_available'] ? '✓ Available' : '✗ Not Available'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div style="text-align:center;margin-top:2rem">
            <a href="/blood/pages/donors.php" class="btn btn-primary">View All Donors</a>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section section-dark">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <div class="divider"></div>
            <p>Simple steps to donate or request blood on campus</p>
        </div>
        <div class="grid-4" style="gap:2rem">
            <?php $steps = [
                ['fas fa-user-plus','Register','Create your donor profile with blood group and contact details'],
                ['fas fa-search','Find','Search for matching blood group donors on campus'],
                ['fas fa-phone-alt','Connect','Contact the donor or submit an official blood request'],
                ['fas fa-heart','Donate','Visit the campus health centre and save a life'],
            ]; foreach($steps as $i => $step): ?>
            <div style="text-align:center">
                <div style="width:72px;height:72px;border-radius:50%;background:rgba(192,57,43,0.2);border:2px solid var(--red);display:flex;align-items:center;justify-content:center;margin:0 auto 1.2rem;font-size:1.5rem;color:var(--red)">
                    <i class="<?php echo $step[0]; ?>"></i>
                </div>
                <div style="font-family:var(--font-display);font-size:0.75rem;color:var(--red);letter-spacing:2px;text-transform:uppercase;margin-bottom:6px">Step <?php echo $i+1; ?></div>
                <h3 style="font-family:var(--font-display);color:var(--white);margin-bottom:8px"><?php echo $step[1]; ?></h3>
                <p style="color:#999;font-size:0.88rem"><?php echo $step[2]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- UPCOMING EVENTS -->
<?php if($events->num_rows > 0): ?>
<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <h2>Upcoming Events</h2>
            <div class="divider"></div>
            <p>Campus blood donation drives and awareness events</p>
        </div>
        <?php while($ev = $events->fetch_assoc()):
            $d = new DateTime($ev['event_date']);
        ?>
        <div class="card event-card" style="margin-bottom:1rem">
            <div class="event-info">
                <div class="event-date-box">
                    <div class="event-date-day"><?php echo $d->format('d'); ?></div>
                    <div class="event-date-month"><?php echo $d->format('M Y'); ?></div>
                </div>
                <div class="event-body">
                    <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                    <div class="event-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ev['venue']); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($ev['event_time'])); ?></span>
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($ev['organizer']); ?></span>
                    </div>
                </div>
                <span class="status-badge status-<?php echo $ev['status']; ?>"><?php echo $ev['status']; ?></span>
            </div>
        </div>
        <?php endwhile; ?>
        <div style="text-align:center;margin-top:1.5rem">
            <a href="/blood/pages/events.php" class="btn btn-outline-dark">All Events</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section section-red" style="text-align:center">
    <div class="container">
        <div style="font-size:3rem;margin-bottom:1rem">🩸</div>
        <h2 style="font-family:var(--font-display);font-size:2.5rem;color:var(--white);margin-bottom:1rem">Ready to Save a Life?</h2>
        <p style="color:rgba(255,255,255,0.8);max-width:500px;margin:0 auto 2rem">Join hundreds of campus donors. Register today and make a difference in someone's life.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="/blood/register.php" class="btn" style="background:var(--white);color:var(--red)"><i class="fas fa-hand-holding-heart"></i> Register Now</a>
            <a href="/blood/pages/request.php" class="btn btn-outline"><i class="fas fa-tint"></i> Request Blood</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
