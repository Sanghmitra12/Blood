<?php $base = "/blood/"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — ' : ''; ?>BloodLink Campus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <!-- <link rel="stylesheet" href="/assets/css/style.css"> -->
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">


</head>
<body>

<!-- <nav class="navbar">
    <a href="/index.php" class="nav-brand">
        <span class="brand-icon">&#9829;</span> BloodLink
        <small>Campus</small>
    </a>
    <button class="nav-toggle" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
    <ul class="nav-links" id="navLinks">
        <li><a href="../index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
        <li><a href="../pages/donors.php" <?php echo basename($_SERVER['PHP_SELF']) == 'donors.php' ? 'class="active"' : ''; ?>>Find Donors</a></li>
        <li><a href="../pages/request.php" <?php echo basename($_SERVER['PHP_SELF']) == 'request.php' ? 'class="active"' : ''; ?>>Request Blood</a></li>
        <li><a href="../pages/events.php" <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'class="active"' : ''; ?>>Events</a></li>
        <li><a href="../pages/inventory.php" <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'class="active"' : ''; ?>>Inventory</a></li>
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
            <li><a href="../admin/dashboard.php" class="btn-nav admin-btn"><i class="fas fa-cog"></i> Admin</a></li>
            <?php else: ?>
            <li><a href="../donor/profile.php" class="btn-nav"><i class="fas fa-user"></i> Profile</a></li>
            <?php endif; ?>
            <li><a href="../logout.php" class="btn-nav logout-btn">Logout</a></li>
        <?php else: ?>
            <li><a href="../login.php" class="btn-nav">Login</a></li>
            <li><a href="../register.php" class="btn-nav btn-primary-nav">Register</a></li>
        <?php endif; ?>
    </ul>
</nav> -->

<nav class="navbar">
    <a href="<?php echo $base; ?>index.php" class="nav-brand">
        <span class="brand-icon">&#9829;</span> BloodLink
        <small>Campus</small>
    </a>

    <button class="nav-toggle" onclick="toggleNav()">
        <i class="fas fa-bars"></i>
    </button>

    <ul class="nav-links" id="navLinks">
        <li><a href="<?php echo $base; ?>index.php">Home</a></li>
        <li><a href="<?php echo $base; ?>/pages/donors.php">Find Donors</a></li>
        <li><a href="<?php echo $base; ?>/pages/request.php">Request Blood</a></li>
        <li><a href="<?php echo $base; ?>/pages/events.php">Events</a></li>
        <li><a href="<?php echo $base; ?>/pages/inventory.php">Inventory</a></li>

        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <li><a href="<?php echo $base; ?>admin/dashboard.php" class="btn-nav admin-btn">Admin</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base; ?>donor/profile.php" class="btn-nav">Profile</a></li>
            <?php endif; ?>
            <li><a href="<?php echo $base; ?>logout.php" class="btn-nav logout-btn">Logout</a></li>
        <?php else: ?>
            <li><a href="<?php echo $base; ?>login.php" class="btn-nav">Login</a></li>
            <li><a href="<?php echo $base; ?>register.php" class="btn-nav btn-primary-nav">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>


<?php
$flash = getFlash();
if ($flash): ?>
<div class="flash-message flash-<?php echo $flash['type']; ?>">
    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($flash['message']); ?>
    <button onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>
