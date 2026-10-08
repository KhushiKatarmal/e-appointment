<?php
require_once __DIR__ . '/modules/db.php';
require_once __DIR__ . '/modules/auth.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (loginAdmin($username, $password)) {
        header('Location: admin_dashboard.php');
        exit;
    }
    $message = 'Invalid admin credentials.';
}
?>
<?php $hideNav = true; $hideFooterFaq = true; include __DIR__ . '/templates/header.php'; ?>
<div class="form-card" style="max-width: 520px; margin: 32px auto;">
    <h2>Admin login</h2>
    <p>Manage doctors, schedules, and appointments.</p>
    <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" action="admin_login.php">
        <label for="username">Admin username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        <label for="password">Password</label>
        <div class="pw-field">
            <input type="password" id="password" name="password" required>
            <button type="button" class="toggle-password" data-target="password" aria-label="Show password">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="form-footer">
            <button type="submit" class="button button-primary">Login</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
