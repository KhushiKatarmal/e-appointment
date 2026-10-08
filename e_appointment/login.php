<?php
require_once __DIR__ . '/modules/auth.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Please enter both email and password.';
    } elseif (loginUser($email, $password)) {
        header('Location: home.php');
        exit;
    } else {
        $message = 'Invalid email or password.';
    }
}
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<div class="auth-shell">
    <div class="auth-visual">
        <div class="auth-visual-card">
            <div class="auth-pill-row">
                <span class="auth-pill">Trusted care</span>
                <span class="auth-pill auth-pill-secondary">Fast booking</span>
            </div>
            <img src="images/medical-hero.svg" alt="Medical illustration">
            <div class="auth-visual-footer">
                <img src="images/doctor-profile.svg" alt="Doctor profile illustration">
                <div>
                    <strong>Secure appointments</strong>
                    <p>Access your care plan, schedule visits, and stay connected to your doctor.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="auth-panel">
        <div class="form-card auth-form-card">
            <h2>Welcome back</h2>
            <p>Login to access your appointments and doctor schedules.</p>
            <?php if (isset($_GET['registered'])): ?>
                <div class="message-box">Registration successful. Please login to continue.</div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php" onsubmit="return handleFormSubmit(event)">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

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
    </div>
</div>
<div class="loading-overlay" id="loginLoadingOverlay" aria-hidden="true">
    <div class="loading-panel">
        <div class="loading-icon" aria-hidden="true">
            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="32" cy="32" r="28" stroke="#ffffff" stroke-width="4" opacity="0.18"/>
                <path d="M20 34c2-6 5-10 8-10s5 4 8 10 5 10 8 10" stroke="#ffffff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M30 20v8" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
                <path d="M34 20v8" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
            </svg>
        </div>
        <div>
            <strong>Logging you in</strong>
            <p class="loading-text">Checking doctor availability and preparing your dashboard…</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
