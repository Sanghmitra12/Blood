<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
requireAdmin();
$pageTitle = 'Manage Events';
$db = getDB();

$action = $_GET['action'] ?? '';
$edit_id = isset($_GET['edit']) && is_numeric($_GET['edit']) ? intval($_GET['edit']) : null;
$errors = [];

// Add/Edit event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    $title = sanitize($db, $_POST['title']);
    $description = sanitize($db, $_POST['description']);
    $event_date = sanitize($db, $_POST['event_date']);
    $event_time = sanitize($db, $_POST['event_time']);
    $venue = sanitize($db, $_POST['venue']);
    $organizer = sanitize($db, $_POST['organizer']);
    $status = sanitize($db, $_POST['status']);

    if (!$title) $errors[] = 'Title is required.';
    if (!$event_date) $errors[] = 'Date is required.';

    if (empty($errors)) {
        if ($edit_id) {
            $db->query("UPDATE events SET title='$title', description='$description', event_date='$event_date', event_time='$event_time', venue='$venue', organizer='$organizer', status='$status' WHERE id=$edit_id");
            setFlash('success', 'Event updated successfully!');
        } else {
            $stmt = $db->prepare("INSERT INTO events (title, description, event_date, event_time, venue, organizer, status) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssss', $title, $description, $event_date, $event_time, $venue, $organizer, $status);
            $stmt->execute();
            setFlash('success', 'Event created successfully!');
        }
        redirect('/blood/admin/events.php');
    }
}

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM events WHERE id=$id");
    setFlash('success', 'Event deleted.');
    redirect('/blood/admin/events.php');
}

$events = $db->query("SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id=e.id) as reg_count FROM events ORDER BY event_date DESC");
$edit_event = $edit_id ? $db->query("SELECT * FROM events WHERE id=$edit_id")->fetch_assoc() : null;

include '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <h4>Main</h4>
            <a href="/blood/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/blood/admin/donors.php"><i class="fas fa-users"></i> Manage Donors</a>
            <a href="/blood/admin/requests.php"><i class="fas fa-tint"></i> Blood Requests</a>
            <a href="/blood/admin/donations.php"><i class="fas fa-hand-holding-heart"></i> Donations Log</a>
        </div>
        <div class="sidebar-section">
            <h4>Manage</h4>
            <a href="/blood/admin/inventory.php"><i class="fas fa-warehouse"></i> Blood Inventory</a>
            <a href="/blood/admin/events.php" class="active"><i class="fas fa-calendar-alt"></i> Events</a>
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
                <h1 style="font-family:var(--font-display);font-size:2rem">Manage Events</h1>
                <p style="color:var(--gray)">Create and manage campus blood donation events</p>
            </div>
            <a href="?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> New Event</a>
        </div>

        <!-- Add/Edit Form -->
        <?php if ($action === 'new' || $edit_id): ?>
        <div class="card" style="margin-bottom:2rem">
            <div class="card-header-strip">
                <h3 style="font-family:var(--font-display)">
                    <i class="fas fa-<?php echo $edit_id ? 'edit' : 'plus'; ?>"></i> 
                    <?php echo $edit_id ? 'Edit Event' : 'Create New Event'; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error" style="margin-bottom:1rem;border-radius:8px">
                    <?php foreach($errors as $e): ?><p><?php echo $e; ?></p><?php endforeach; ?>
                </div>
                <?php endif; ?>
                <form method="POST" <?php if($edit_id) echo "action=\"?edit=$edit_id\""; ?>>
                    <input type="hidden" name="save_event" value="1">
                    <div class="form-group">
                        <label>Event Title *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_event['title'] ?? ($_POST['title'] ?? '')); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_event['description'] ?? ($_POST['description'] ?? '')); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Event Date *</label>
                            <input type="date" name="event_date" class="form-control" value="<?php echo $edit_event['event_date'] ?? ($_POST['event_date'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Event Time</label>
                            <input type="time" name="event_time" class="form-control" value="<?php echo $edit_event['event_time'] ?? ($_POST['event_time'] ?? '09:00'); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Venue</label>
                            <input type="text" name="venue" class="form-control" value="<?php echo htmlspecialchars($edit_event['venue'] ?? ($_POST['venue'] ?? '')); ?>">
                        </div>
                        <div class="form-group">
                            <label>Organizer</label>
                            <input type="text" name="organizer" class="form-control" value="<?php echo htmlspecialchars($edit_event['organizer'] ?? ($_POST['organizer'] ?? '')); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <?php foreach(['Upcoming','Ongoing','Completed','Cancelled'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo ($edit_event['status'] ?? 'Upcoming') === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex;gap:1rem">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo $edit_id ? 'Update' : 'Create'; ?> Event</button>
                        <a href="/blood/admin/events.php" class="btn btn-outline-dark">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Events Table -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Title</th><th>Date</th><th>Time</th><th>Venue</th><th>Organizer</th><th>Registrations</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if ($events->num_rows === 0): ?>
                    <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--gray)">No events yet</td></tr>
                    <?php else: ?>
                    <?php $i=1; while($ev = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($ev['title']); ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($ev['event_date'])); ?></td>
                        <td><?php echo $ev['event_time'] ? date('h:i A', strtotime($ev['event_time'])) : '—'; ?></td>
                        <td style="font-size:0.85rem"><?php echo htmlspecialchars($ev['venue'] ?? '—'); ?></td>
                        <td style="font-size:0.85rem"><?php echo htmlspecialchars($ev['organizer'] ?? '—'); ?></td>
                        <td><span style="font-weight:700;color:var(--red)"><?php echo $ev['reg_count']; ?></span></td>
                        <td><span class="status-badge status-<?php echo $ev['status']; ?>"><?php echo $ev['status']; ?></span></td>
                        <td style="white-space:nowrap">
                            <a href="?edit=<?php echo $ev['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $ev['id']; ?>" class="btn btn-sm" style="background:#f8d7da;color:#721c24" onclick="return confirm('Delete this event?')"><i class="fas fa-trash"></i></a>
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
