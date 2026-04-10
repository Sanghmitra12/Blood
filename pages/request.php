<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$pageTitle = 'Request Blood';
$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $req_name = sanitize($db, $_POST['requester_name']);
    $req_email = sanitize($db, $_POST['requester_email']);
    $req_phone = sanitize($db, $_POST['requester_phone']);
    $pat_name = sanitize($db, $_POST['patient_name']);
    $blood_group = sanitize($db, $_POST['blood_group']);
    $units = intval($_POST['units_needed']);
    $hospital = sanitize($db, $_POST['hospital_name']);
    $urgency = sanitize($db, $_POST['urgency']);
    $req_date = sanitize($db, $_POST['required_date']);
    $message = sanitize($db, $_POST['message']);

    if (!$req_name) $errors[] = 'Your name is required.';
    if (!$req_phone) $errors[] = 'Phone number is required.';
    if (!$pat_name) $errors[] = 'Patient name is required.';
    if (!$blood_group) $errors[] = 'Blood group is required.';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO blood_requests (requester_name,requester_email,requester_phone,patient_name,blood_group,units_needed,hospital_name,urgency,required_date,message) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssissss', $req_name,$req_email,$req_phone,$pat_name,$blood_group,$units,$hospital,$urgency,$req_date,$message);
        if ($stmt->execute()) {
            setFlash('success', 'Blood request submitted successfully! We will connect you with donors shortly.');
            redirect('/blood/pages/request.php');
        } else {
            $errors[] = 'Submission failed. Please try again.';
        }
    }
}

// Show recent requests
$requests = $db->query("SELECT * FROM blood_requests WHERE status='Pending' ORDER BY urgency='Critical' DESC, urgency='Urgent' DESC, created_at DESC LIMIT 10");

include '../includes/header.php';
?>

<div class="page-hero">
    <h1>Request Blood</h1>
    <p>Submit an urgent blood request and connect with campus donors</p>
</div>

<section class="section">
    <div class="container">
        <div class="grid-2" style="gap:3rem;align-items:start">
            <!-- Request Form -->
            <div>
                <div class="card">
                    <div class="card-header-strip">
                        <h3 style="font-family:var(--font-display)"><i class="fas fa-tint"></i> Submit Blood Request</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                        <div class="flash-message flash-error" style="margin-bottom:1rem;border-radius:8px">
                            <?php foreach($errors as $e): ?><p><?php echo $e; ?></p><?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Your Name *</label>
                                    <input type="text" name="requester_name" class="form-control" value="<?php echo htmlspecialchars(isLoggedIn() ? $_SESSION['user_name'] : ($_POST['requester_name'] ?? '')); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Your Phone *</label>
                                    <input type="tel" name="requester_phone" class="form-control" value="<?php echo htmlspecialchars($_POST['requester_phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Your Email</label>
                                <input type="email" name="requester_email" class="form-control" value="<?php echo htmlspecialchars($_POST['requester_email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Patient Name *</label>
                                <input type="text" name="patient_name" class="form-control" value="<?php echo htmlspecialchars($_POST['patient_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Blood Group Required *</label>
                                    <select name="blood_group" class="form-control" required>
                                        <option value="">-- Select --</option>
                                        <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                                        <option value="<?php echo $bg; ?>" <?php echo (($_POST['blood_group'] ?? '') === $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Units Needed</label>
                                    <input type="number" name="units_needed" class="form-control" min="1" max="10" value="<?php echo intval($_POST['units_needed'] ?? 1); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Hospital Name</label>
                                <input type="text" name="hospital_name" class="form-control" value="<?php echo htmlspecialchars($_POST['hospital_name'] ?? ''); ?>">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Urgency Level</label>
                                    <select name="urgency" class="form-control">
                                        <option value="Normal">Normal</option>
                                        <option value="Urgent">Urgent</option>
                                        <option value="Critical">Critical</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Required By Date</label>
                                    <input type="date" name="required_date" class="form-control" value="<?php echo htmlspecialchars($_POST['required_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Additional Information</label>
                                <textarea name="message" class="form-control" rows="3" placeholder="Any specific requirements or notes..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Requests -->
            <div>
                <h3 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:1.5rem">
                    <i class="fas fa-heartbeat" style="color:var(--red)"></i> Active Requests
                </h3>
                <?php if ($requests->num_rows === 0): ?>
                <p style="color:var(--gray)">No pending requests at this time.</p>
                <?php else: ?>
                <?php while($req = $requests->fetch_assoc()): ?>
                <div class="card" style="margin-bottom:1rem">
                    <div class="card-body" style="display:flex;gap:1rem;align-items:center">
                        <div style="font-family:var(--font-display);font-size:1.8rem;font-weight:900;color:var(--red);min-width:60px;text-align:center">
                            <?php echo $req['blood_group']; ?>
                        </div>
                        <div style="flex:1">
                            <strong><?php echo htmlspecialchars($req['patient_name']); ?></strong>
                            <div style="font-size:0.85rem;color:var(--gray)">
                                <?php if($req['hospital_name']): ?><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($req['hospital_name']); ?> &nbsp;<?php endif; ?>
                                <i class="fas fa-tint"></i> <?php echo $req['units_needed']; ?> unit(s)
                            </div>
                            <div style="margin-top:4px">
                                <span class="urgency-badge urgency-<?php echo $req['urgency']; ?>"><?php echo $req['urgency']; ?></span>
                            </div>
                        </div>
                        <?php if(isLoggedIn()): ?>
                        <a href="tel:<?php echo $req['requester_phone']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-phone"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if(!isLoggedIn()): ?>
                <p style="font-size:0.85rem;color:var(--gray);text-align:center;margin-top:1rem">
                    <a href="/login.php" style="color:var(--red)">Login</a> to see contact details
                </p>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
