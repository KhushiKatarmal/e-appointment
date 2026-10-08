<?php
require_once __DIR__ . '/modules/auth.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $message = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Enter a valid email address.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } elseif (registerUser($name, $email, $password, $phone)) {
        header('Location: login.php?registered=1');
        exit;
    } else {
        $message = 'A user with that email already exists.';
    }
}
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<div class="auth-shell">
    <div class="auth-visual">
        <div class="auth-visual-card">
            <div class="auth-pill-row">
                <span class="auth-pill">Join care team</span>
                <span class="auth-pill auth-pill-secondary">Personalized support</span>
            </div>
            <img src="images/medical-hero.svg" alt="Medical illustration">
            <div class="auth-visual-footer">
                <img src="images/doctor-profile.svg" alt="Doctor profile illustration">
                <div>
                    <strong>Build your profile</strong>
                    <p>Create an account to book appointments, view schedules, and keep your care history close at hand.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="auth-panel">
        <div class="form-card auth-form-card">
            <h2>Create your account</h2>
            <p>Register to book doctor appointments, view schedules, and manage your profile.</p>
            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post" action="register.php" onsubmit="return handleFormSubmit(event)">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

                <label for="phone">Phone number</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm" required>

                <div class="form-footer">
                    <button type="submit" class="button button-primary">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="loading-overlay" id="registerLoadingOverlay" aria-hidden="true">
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
            <strong>Creating your account</strong>
            <p class="loading-text">Setting up your profile and preparing your secure dashboard…</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
