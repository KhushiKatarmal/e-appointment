<?php
require_once __DIR__ . '/modules/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}
$message = '';
$doctor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        if ($doctorId > 0) {
            $conn = dbConnect();
            $stmt = $conn->prepare('DELETE FROM doctors WHERE doctor_id = ?');
            $stmt->bind_param('i', $doctorId);
            $stmt->execute();
            $message = $stmt->affected_rows > 0 ? 'Doctor deleted successfully.' : 'Doctor could not be found.';
        }
    }

    if ($action !== 'delete') {
        $name = trim($_POST['doctor_name'] ?? '');
        $specialty = trim($_POST['specialization'] ?? '');
        $experience = (int)($_POST['experience_years'] ?? 0);
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fee = (float)($_POST['consultation_fee'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $doctorId = $_POST['doctor_id'] ?? null;

        if ($name === '' || $specialty === '' || $email === '') {
            $message = 'Name, specialty, and email are required.';
        } else {
            $conn = dbConnect();
            $duplicateSql = 'SELECT doctor_id FROM doctors WHERE (email = ? OR (doctor_name = ? AND specialization = ?))';
            $duplicateStmt = $conn->prepare($duplicateSql . ($doctorId ? ' AND doctor_id <> ?' : ''));
            if ($doctorId) {
                $duplicateStmt->bind_param('sssi', $email, $name, $specialty, $doctorId);
            } else {
                $duplicateStmt->bind_param('sss', $email, $name, $specialty);
            }
            $duplicateStmt->execute();

            if ($duplicateStmt->get_result()->num_rows > 0) {
                $message = 'A doctor with this email or name and specialization already exists.';
            } elseif ($doctorId) {
                $sql = 'UPDATE doctors SET doctor_name = ?, specialization = ?, experience_years = ?, phone = ?, email = ?, consultation_fee = ?, status = ? WHERE doctor_id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssissdsi', $name, $specialty, $experience, $phone, $email, $fee, $status, $doctorId);
                $stmt->execute();
                $message = 'Doctor updated successfully.';
            } else {
                $sql = 'INSERT INTO doctors (doctor_name, specialization, experience_years, phone, email, consultation_fee, status) VALUES (?, ?, ?, ?, ?, ?, ?)';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssissds', $name, $specialty, $experience, $phone, $email, $fee, $status);
                $stmt->execute();
                $message = 'Doctor added successfully.';
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $doctor = dbFetch('SELECT doctor_id AS id, doctor_name, specialization, experience_years AS experience_years, phone, email, consultation_fee, status FROM doctors WHERE doctor_id = ? LIMIT 1', [$_GET['edit']]);
}

$doctors = dbFetchAll('SELECT doctor_id AS id, doctor_name AS name, specialization, experience_years AS experience, consultation_fee AS fee, status FROM doctors ORDER BY doctor_name ASC');
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<div class="form-card">
    <h2><?= $doctor ? 'Edit doctor' : 'Add new doctor' ?></h2>
    <p>Use this form to keep the doctor list current and control active status.</p>
    <?php if ($message): ?>
        <div class="message-box"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" action="admin_doctors.php">
        <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doctor['id'] ?? '') ?>">
        <label for="doctor_name">Doctor name</label>
        <input type="text" name="doctor_name" id="doctor_name" value="<?= htmlspecialchars($doctor['doctor_name'] ?? '') ?>" required>
        <label for="specialization">Specialization</label>
        <input type="text" name="specialization" id="specialization" value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>" required>
        <label for="experience_years">Experience years</label>
        <input type="number" name="experience_years" id="experience_years" value="<?= htmlspecialchars($doctor['experience_years'] ?? '') ?>" min="0" required>
        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($doctor['email'] ?? '') ?>" required>
        <label for="consultation_fee">Consultation fee</label>
        <input type="number" step="0.01" name="consultation_fee" id="consultation_fee" value="<?= htmlspecialchars($doctor['consultation_fee'] ?? '') ?>" required>
        <label for="status">Status</label>
        <select name="status" id="status" required>
            <option value="active" <?= ($doctor['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($doctor['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <div class="form-footer">
            <button type="submit" class="button button-primary"><?= $doctor ? 'Save changes' : 'Add doctor' ?></button>
        </div>
    </form>
</div>

<div class="doctor-list">
    <?php foreach ($doctors as $doc): ?>
        <div class="doctor-card">
            <h3><?= htmlspecialchars($doc['name']) ?></h3>
            <div class="doctor-details">
                <span><?= htmlspecialchars($doc['specialization']) ?></span>
                <span><?= htmlspecialchars($doc['experience']) ?> yrs</span>
            </div>
            <div class="info-row">
                <div><strong>Fee</strong><p>₹<?= htmlspecialchars($doc['fee']) ?></p></div>
                <div><strong>Status</strong><p><?= htmlspecialchars($doc['status']) ?></p></div>
            </div>
            <div class="action-row">
                <a href="admin_doctors.php?edit=<?= urlencode($doc['id']) ?>" class="button button-outline">Edit</a>
                <a href="admin_schedule.php?doctor=<?= urlencode($doc['id']) ?>" class="button button-primary">Manage schedule</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete this doctor and all of their schedules and appointments?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doc['id']) ?>">
                    <button type="submit" class="button button-danger">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
