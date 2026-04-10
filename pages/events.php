<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once '../includes/auth.php';
require_once '../includes/db.php';

$pageTitle = 'Events';
$db = getDB();

// Register for event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $event_id = intval($_POST['event_id']);
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("INSERT IGNORE INTO event_registrations (event_id, user_id) VALUES (?,?)");
    $stmt->bind_param('ii', $event_id, $user_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        setFlash('success', 'You have successfully registered for the event!');
    } else {
        setFlash('error', 'You are already registered for this event.');
    }
    redirect('/pages/events.php');
}

//$events = $db->query("SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id=e.id) as reg_count FROM events ORDER BY event_date ASC");
$events = $db->query("
SELECT e.*, 
(SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) as reg_count 
FROM events e
ORDER BY event_date ASC
");


include '../includes/header.php';
?>

<div class="page-hero">
    <h1>Blood Donation Events</h1>
    <p>Campus drives, awareness camps, and donation events</p>
</div>

<section class="section">
    <div class="container">
        <?php if ($events->num_rows === 0): ?>
        <div style="text-align:center;padding:4rem;color:var(--gray)">
            <i class="fas fa-calendar-times" style="font-size:3rem;color:var(--red-light);margin-bottom:1rem;display:block"></i>
            <h3>No events scheduled</h3>
        </div>
        <?php else: ?>
        <?php while($ev = $events->fetch_assoc()):
            $d = new DateTime($ev['event_date']);
            $isPast = $ev['event_date'] < date('Y-m-d');
            $userRegistered = false;
            if (isLoggedIn()) {
                $uid = $_SESSION['user_id'];
                $eid = $ev['id'];
                $regCheck = $db->query("SELECT id FROM event_registrations WHERE event_id=$eid AND user_id=$uid");
                $userRegistered = $regCheck->num_rows > 0;
            }
        ?>
        <div class="card" style="margin-bottom:1.5rem;<?php echo $isPast ? 'opacity:0.7' : ''; ?>">
            <div style="display:flex;flex-wrap:wrap">
                <!-- Date Box -->
                <div class="event-date-box" style="display:flex;flex-direction:column;justify-content:center;padding:2rem;min-width:100px">
                    <div class="event-date-day"><?php echo $d->format('d'); ?></div>
                    <div class="event-date-month"><?php echo $d->format('M'); ?></div>
                    <div style="font-size:0.75rem;margin-top:4px"><?php echo $d->format('Y'); ?></div>
                </div>
                <!-- Event Info -->
                <div class="card-body" style="flex:1">
                    <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:1rem">
                        <div>
                            <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:6px"><?php echo htmlspecialchars($ev['title']); ?></h3>
                            <div style="color:var(--gray);font-size:0.88rem;margin-bottom:10px">
                                <span><i class="fas fa-map-marker-alt" style="color:var(--red)"></i> <?php echo htmlspecialchars($ev['venue']); ?></span> &nbsp;
                                <?php if($ev['event_time']): ?><span><i class="fas fa-clock" style="color:var(--red)"></i> <?php echo date('h:i A', strtotime($ev['event_time'])); ?></span> &nbsp;<?php endif; ?>
                                <span><i class="fas fa-user" style="color:var(--red)"></i> <?php echo htmlspecialchars($ev['organizer']); ?></span>
                            </div>
                            <p style="font-size:0.9rem;color:var(--gray);margin-bottom:12px"><?php echo htmlspecialchars($ev['description']); ?></p>
                            <span class="status-badge status-<?php echo $ev['status']; ?>"><?php echo $ev['status']; ?></span>
                            <span style="font-size:0.85rem;color:var(--gray);margin-left:12px"><i class="fas fa-users"></i> <?php echo $ev['reg_count']; ?> registered</span>
                        </div>
                        <div>
                            <?php if ($ev['status'] === 'Upcoming' && !$isPast): ?>
                                <?php if (!isLoggedIn()): ?>
                                    <a href="/login.php" class="btn btn-outline-dark btn-sm">Login to Register</a>
                                <?php elseif ($userRegistered): ?>
                                    <span class="btn btn-sm" style="background:#d4edda;color:#155724;cursor:default"><i class="fas fa-check"></i> Registered</span>
                                <?php else: ?>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="event_id" value="<?php echo $ev['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-calendar-check"></i> Register</button>
                                    </form>
                                <?php endif; ?>
                            <?php elseif($isPast || $ev['status'] === 'Completed'): ?>
                                <span style="font-size:0.85rem;color:var(--gray)"><i class="fas fa-history"></i> Past Event</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
