<?php
require_once __DIR__ . '/../modules/auth.php';
$user = currentUser();
$admin = $_SESSION['admin'] ?? null;
// If both user and admin are present, prefer admin only for nav rendering
if ($user && $admin) {
    $user = null;
}
?>
<nav class="top-nav">
    <?php if (!empty($admin)): ?>
        <a href="admin_dashboard.php" class="nav-logo">Home</a>
    <?php else: ?>
        <a href="home.php" class="nav-logo">Home</a>
    <?php endif; ?>
    <div class="nav-links">
        <?php if ($admin): ?>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_doctors.php">Doctors</a>
            <a href="logout.php" class="button button-outline">Logout</a>
        <?php elseif ($user): ?>
            <a href="doctors.php">Doctors</a>
            <a href="appointment.php">Appointment</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="button button-outline">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="button button-primary">Register</a>
        <?php endif; ?>
    </div>
</nav>
