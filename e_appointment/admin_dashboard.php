<?php
require_once __DIR__ . '/modules/db.php';
require_once __DIR__ . '/modules/appointments.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}
$conn = dbConnect();
// Ensure `is_super` column exists (safe on MySQL 8+)
try {
    $conn->query("ALTER TABLE admin ADD COLUMN IF NOT EXISTS is_super TINYINT(1) NOT NULL DEFAULT 0");
} catch (mysqli_sql_exception $e) {
    // If ALTER with IF NOT EXISTS isn't supported, attempt a conditional add
    try {
        $row = $conn->query("SHOW COLUMNS FROM admin LIKE 'is_super'")->fetch_assoc();
        if (!$row) {
            $conn->query("ALTER TABLE admin ADD COLUMN is_super TINYINT(1) NOT NULL DEFAULT 0");
        }
    } catch (mysqli_sql_exception $e) {
        // ignore
    }
}
// Make the first admin a super admin if none exist
try {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM admin WHERE is_super = 1");
    $has = $res ? (int)$res->fetch_assoc()['cnt'] : 0;
    if ($has === 0) {
        $conn->query("UPDATE admin SET is_super = 1 ORDER BY admin_id ASC LIMIT 1");
    }
} catch (mysqli_sql_exception $e) {
    // ignore
}
$message = '';
$adminMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id']) && $_POST['action'] === 'cancel_appointment') {
    $appointmentId = (int)$_POST['appointment_id'];
    if (cancelAppointment($appointmentId)) {
        $message = 'Appointment cancelled successfully.';
    } else {
        $message = 'Unable to cancel the appointment. Please try again.';
    }
}

// Admin management: create or update admin accounts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $currentAdmin = $_SESSION['admin'];
    $action = $_POST['admin_action'];
    if ($action === 'create') {
        // only super admins can create other admins
        if (empty($currentAdmin['is_super'])) {
            $adminMessage = 'Only super admins can create new admin accounts.';
        } else {
            $newUser = trim($_POST['new_username'] ?? '');
            $newPass = $_POST['new_password'] ?? '';
            if ($newUser === '' || $newPass === '') {
                $adminMessage = 'Username and password are required.';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO admin (username, password, is_super) VALUES (?, ?, ?)');
                $isSuper = isset($_POST['new_is_super']) && $_POST['new_is_super'] === '1' ? 1 : 0;
                $stmt->bind_param('ssi', $newUser, $hash, $isSuper);
                try {
                    $stmt->execute();
                    $adminMessage = 'Admin created successfully.';
                } catch (mysqli_sql_exception $e) {
                    $adminMessage = 'Error creating admin: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'update') {
        // allow updating own or others' credentials only for super admin or self
        $updateId = (int)($_POST['update_id'] ?? 0);
        $newUser = trim($_POST['update_username'] ?? '');
        $newPass = $_POST['update_password'] ?? '';
        $newIsSuper = isset($_POST['update_is_super']) && $_POST['update_is_super'] === '1' ? 1 : 0;
        // fetch target admin
        $target = dbFetch('SELECT admin_id AS id, username FROM admin WHERE admin_id = ?', [$updateId]);
        if (!$target) {
            $adminMessage = 'Admin not found.';
        } else {
            $allowed = ($currentAdmin['is_super'] ?? 0) === 1 || $currentAdmin['id'] == $updateId;
            if (!$allowed) {
                $adminMessage = 'Not authorized to update this admin.';
            } else {
                $params = [];
                $set = [];
                if ($newUser !== '') { $set[] = 'username = ?'; $params[] = $newUser; }
                if ($newPass !== '') { $set[] = 'password = ?'; $params[] = password_hash($newPass, PASSWORD_DEFAULT); }
                if (($currentAdmin['is_super'] ?? 0) === 1) { $set[] = 'is_super = ?'; $params[] = $newIsSuper; }
                if (!empty($set)) {
                    $params[] = $updateId;
                    $sql = 'UPDATE admin SET ' . implode(', ', $set) . ' WHERE admin_id = ?';
                    try {
                        $stmt = dbPrepare($sql, $params);
                        $adminMessage = 'Admin updated successfully.';
                    } catch (Exception $e) {
                        $adminMessage = 'Error updating admin: ' . $e->getMessage();
                    }
                } else {
                    $adminMessage = 'No changes provided.';
                }
            }
        }
    }
}
$doctors = dbFetchAll('SELECT doctor_id AS id, doctor_name AS name, specialization, experience_years AS experience, consultation_fee AS fee, status FROM doctors ORDER BY doctor_name ASC');
$appointments = getAppointments();
$admins = dbFetchAll('SELECT admin_id AS id, username, COALESCE(is_super,0) AS is_super FROM admin ORDER BY admin_id ASC');
?>
<?php $hideFooterFaq = true; include __DIR__ . '/templates/header.php'; ?>

<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($adminMessage): ?>
    <div class="alert"><?= htmlspecialchars($adminMessage) ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="form-card">
        <h2>Admin dashboard</h2>
        <div class="action-row">
            <a href="admin_doctors.php" class="button button-primary">Manage doctors</a>
            <a href="admin_schedule.php" class="button button-outline">Schedules</a>
            <a href="admin_appointments.php" class="button button-outline">Appointments</a>
        </div>
    </div>

    <div class="form-card">
        <h2>Admin management</h2>
        <p>Manage administrators. Only super admins can create new admin accounts or change `is_super` status.</p>
        <div class="admin-list">
            <table class="table">
                <thead><tr><th>Super</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?= $a['is_super'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="admin_action" value="update">
                                    <input type="hidden" name="update_id" value="<?= htmlspecialchars($a['id']) ?>">
                                    <input type="password" name="update_password" placeholder="New password">
                                    <?php if (($_SESSION['admin']['is_super'] ?? 0) === 1): ?>
                                        <label style="margin-left:6px">Super
                                            <select name="update_is_super">
                                                <option value="0" <?= $a['is_super'] ? '' : 'selected' ?>>No</option>
                                                <option value="1" <?= $a['is_super'] ? 'selected' : '' ?>>Yes</option>
                                            </select>
                                        </label>
                                    <?php endif; ?>
                                    <button type="submit" class="button button-outline">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <hr>
        <details>
            <summary class="button button-primary">Create new admin</summary>
            <form method="post" style="margin-top:12px">
                <input type="hidden" name="admin_action" value="create">
                <label>Username<br><input name="new_username" type="text" required></label>
                <label>Password<br><input name="new_password" type="password" required></label>
                <?php if (($_SESSION['admin']['is_super'] ?? 0) === 1): ?>
                    <label>Super admin?<br>
                        <select name="new_is_super">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </label>
                <?php endif; ?>
                <br>
                <button type="submit" class="button button-primary">Create Admin</button>
            </form>
        </details>
    </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
