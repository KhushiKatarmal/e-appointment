<?php
require_once __DIR__ . '/modules/auth.php';
require_once __DIR__ . '/modules/appointments.php';
requireLogin();
$user = currentUser();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id']) && $_POST['action'] === 'cancel_appointment') {
    $appointmentId = (int)$_POST['appointment_id'];
    if (cancelAppointmentByUser($appointmentId, $user['id'])) {
        $message = 'Your appointment was cancelled successfully.';
    } else {
        $message = 'Unable to cancel the appointment. Please try again.';
    }
}
$appointments = userAppointments($user['id']);
$nextAppointment = null;
$nextAppointmentTime = null;
foreach ($appointments as $appt) {
    if (($appt['status'] ?? '') !== 'Cancelled') {
        $nextAppointment = $appt;
        $nextAppointmentTime = DateTime::createFromFormat('H:i:s', $nextAppointment['time']);
        break;
    }
}
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<div class="hero-banner">
    <div class="hero-copy">
        <span class="badge">My health</span>
        <h2>Keep track of your appointments and doctor profile.</h2>
        <p>See upcoming visits, doctor details, and booking status in a single view.</p>
    </div>
    <div>
        <img src="images/doctor-profile.svg" alt="Medical profile illustration" class="hero-image">
    </div>
</div>

<div class="grid-2">
    <div class="form-card">
        <h2>Your profile</h2>
        <p>Manage your account and review upcoming appointments.</p>
        <div class="info-row">
            <div>
                <strong>Name</strong>
                <p><?= htmlspecialchars($user['name']) ?></p>
            </div>
            <div>
                <strong>Email</strong>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
    </div>
    <aside class="sidebar-widgets">
        <div class="widget-card">
            <div class="widget-title">Next visit</div>
            <?php if ($nextAppointment): ?>
                <?php $nextAppointmentTime = DateTime::createFromFormat('H:i:s', $nextAppointment['time']) ?: DateTime::createFromFormat('H:i', $nextAppointment['time']); ?>
                <strong class="widget-stat"><?= htmlspecialchars($nextAppointment['date']) ?></strong>
                <p class="widget-note">Appointment with <?= htmlspecialchars($nextAppointment['doctorName']) ?> (<?= htmlspecialchars($nextAppointment['specialty']) ?>) at <?= $nextAppointmentTime ? htmlspecialchars($nextAppointmentTime->format('g:i A')) : htmlspecialchars($nextAppointment['time']) ?></p>
            <?php else: ?>
                <p class="widget-note">You currently have no booked appointments. Use the appointment page to schedule one.</p>
            <?php endif; ?>
        </div>
        <div class="widget-card">
            <div class="widget-title">Appointment count</div>
            <strong class="widget-stat"><?= count($appointments) ?></strong>
            <p class="widget-note">Total appointments booked in your account.</p>
        </div>
    </aside>
</div>

<div class="form-card">
    <h2>My appointments</h2>
    <?php if (empty($appointments)): ?>
        <p class="alert">No appointments yet. Book one to see it here.</p>
    <?php else: ?>
        <div class="appointment-list">
            <?php foreach ($appointments as $item): ?>
                <?php $itemTime = DateTime::createFromFormat('H:i:s', $item['time']) ?: DateTime::createFromFormat('H:i', $item['time']); ?>
                <div class="appointment-item">
                    <div>
                        <strong><?= htmlspecialchars($item['doctorName']) ?></strong>
                        <p><?= htmlspecialchars($item['specialty']) ?> · <?= htmlspecialchars($item['date']) ?> at <?= $itemTime ? htmlspecialchars($itemTime->format('g:i A')) : htmlspecialchars($item['time']) ?></p>
                        <?php if (($item['cancelledBy'] ?? '') === 'Admin'): ?>
                            <p class="alert">Your appointment was canceled by the administrator.</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($item['status'] === 'Cancelled'): ?>
                            <span class="badge canceled">Cancelled</span>
                        <?php else: ?>
                            <form method="post" class="cancel-form">
                                <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($item['id']) ?>">
                                <button type="submit" name="action" value="cancel_appointment" class="button button-outline">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
