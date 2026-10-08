<?php
require_once __DIR__ . '/modules/db.php';
require_once __DIR__ . '/modules/appointments.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    $action = $_POST['action'];
    $appointmentId = (int)$_POST['appointment_id'];
    if ($action === 'cancel') {
        if (cancelAppointment($appointmentId)) {
            $message = 'Appointment cancelled.';
        } else {
            $message = 'Unable to cancel appointment.';
        }
    }
}
$appointments = getAppointments();
?>
<?php $hideFooterFaq = true; include __DIR__ . '/templates/header.php'; ?>
<div class="form-card">
    <h2>Manage Appointments</h2>
    <p>View all booked appointments and cancel them when necessary.</p>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if (empty($appointments)): ?>
        <p class="alert">No appointments found.</p>
    <?php else: ?>
        <div class="appointment-list">
            <?php foreach ($appointments as $item): ?>
                <?php $itemTime = DateTime::createFromFormat('H:i:s', $item['time']) ?: DateTime::createFromFormat('H:i', $item['time']); ?>
                <div class="appointment-item">
                    <div>
                        <strong><?= htmlspecialchars($item['doctorName']) ?> (<?= htmlspecialchars($item['specialty']) ?>)</strong>
                        <p><?= htmlspecialchars($item['date']) ?> at <?= $itemTime ? htmlspecialchars($itemTime->format('g:i A')) : htmlspecialchars($item['time']) ?></p>
                        <p>Patient ID: <?= htmlspecialchars($item['userId']) ?> · Status: <?= htmlspecialchars($item['status']) ?></p>
                    </div>
                    <div>
                        <?php if ($item['status'] === 'Cancelled'): ?>
                            <span class="badge canceled">Cancelled</span>
                        <?php else: ?>
                            <form method="post" onsubmit="return confirm('Cancel this appointment?')">
                                <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($item['id']) ?>">
                                <button type="submit" name="action" value="cancel" class="button button-outline">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
