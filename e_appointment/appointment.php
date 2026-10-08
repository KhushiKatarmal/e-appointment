<?php
require_once __DIR__ . '/modules/auth.php';
require_once __DIR__ . '/modules/appointments.php';
requireLogin();

$user = currentUser();
$doctors = getDoctors();
$appointments = userAppointments($user['id']);
$nextAppointment = null;
foreach ($appointments as $appt) {
    if (($appt['status'] ?? '') !== 'Cancelled') {
        $nextAppointment = $appt;
        break;
    }
}
$selectedDoctorId = $_GET['doctor'] ?? null;
$selectedDoctor = null;
$message = '';

if (!empty($doctors)) {
    if ($selectedDoctorId !== null && $selectedDoctorId !== '') {
        $selectedDoctor = getDoctorById($selectedDoctorId);
    }
    if (!$selectedDoctor) {
        $selectedDoctor = $doctors[0];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedDoctorId = $_POST['doctor_id'] ?? null;
    if ($postedDoctorId !== null && $postedDoctorId !== '') {
        $resolved = getDoctorById($postedDoctorId);
        if ($resolved) {
            $selectedDoctor = $resolved;
        } else {
            error_log('appointment.php: posted doctor_id not found: ' . var_export($postedDoctorId, true));
            if (empty($selectedDoctor) && !empty($doctors)) {
                $selectedDoctor = $doctors[0];
            }
        }
    } else {
        if (empty($selectedDoctor) && !empty($doctors)) {
            $selectedDoctor = $doctors[0];
        }
    }

    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';

    if (!$date || !$time) {
        $message = 'Please choose a date and time slot.';
    } else {
        if (empty($selectedDoctor) || !isset($selectedDoctor['id']) || $selectedDoctor['id'] === '') {
            error_log('appointment.php: invalid selectedDoctor on POST: ' . var_export($selectedDoctor, true));
            $message = 'Invalid doctor selection. Please try again.';
        } else {
            $result = addAppointment($user['id'], (string)$selectedDoctor['id'], $date, $time);
            if (!empty($result['success'])) {
                header('Location: appointment.php?booked=1&doctor=' . urlencode($selectedDoctor['id']));
                exit;
            }
            // Map error codes to user-friendly messages
            $err = $result['error'] ?? null;
            if ($err === 'conflict') {
                $message = 'Selected slot is no longer available. Please choose a different time.';
            } elseif ($err === 'invalid_ids') {
                $message = 'Invalid doctor selection. Please try again.';
            } else {
                $message = 'Unable to book the appointment. Please try again.';
            }
        }
    }
}

if (isset($_GET['booked'])) {
    $message = 'Your appointment was booked successfully.';
}

$availableDates = $selectedDoctor ? getDoctorAvailabilities($selectedDoctor['id'], 14) : [];
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<?php $defaultDate = $_POST['date'] ?? array_key_first($availableDates) ?? ''; ?>
<?php $defaultTimes = $defaultDate && isset($availableDates[$defaultDate]) ? $availableDates[$defaultDate] : []; ?>
<?php $defaultTime = $_POST['time'] ?? ($defaultTimes[0] ?? ''); ?>
<div class="mobile-booking-shell">
    <?php if (!$selectedDoctor): ?>
        <div class="alert">No doctors are currently available. Please check back later or visit the home page.</div>
    <?php else: ?>
        <div class="booking-topbar">
            <a href="home.php" class="button button-outline">← All Doctors</a>
            <div>
                <h2>Appointment</h2>
                <p>Schedule a visit with <?= htmlspecialchars($selectedDoctor['name']) ?>.</p>
            </div>
        </div>

        <div class="booking-card">
            <section class="doctor-panel">
                <div class="doctor-profile-card">
                    <img src="images/doctor-profile.svg" alt="Doctor illustration">
                    <div>
                        <span class="badge"><?= htmlspecialchars($selectedDoctor['specialization']) ?></span>
                        <h3><?= htmlspecialchars($selectedDoctor['name']) ?></h3>
                        <p><?= htmlspecialchars($selectedDoctor['specialization']) ?> · <?= htmlspecialchars($selectedDoctor['experience']) ?> yrs</p>
                        <p class="doctor-fee">Fee: ₹<?= htmlspecialchars($selectedDoctor['fee']) ?></p>
                    </div>
                </div>
                <div class="doctor-actions">
                    <button type="button" class="icon-button" aria-label="Call doctor">📞</button>
                    <button type="button" class="icon-button" aria-label="Chat with doctor">💬</button>
                    <button type="button" class="icon-button" aria-label="Video call">🎥</button>
                </div>

                <div class="detail-card">
                    <h3>Details</h3>
                    <p>Choose a date and time from the available slots below. Your appointment request will be submitted immediately.</p>
                </div>
            </section>

            <section class="booking-panel">
                <?php if ($message): ?>
                    <div class="alert"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form id="bookingForm" method="post" action="appointment.php">
                    <input type="hidden" name="doctor_id" id="doctorIdInput" value="<?= htmlspecialchars($selectedDoctor['id']) ?>">
                    <input type="hidden" name="date" id="dateInput" value="<?= htmlspecialchars($defaultDate) ?>">
                    <input type="hidden" name="time" id="timeInput" value="<?= htmlspecialchars($defaultTime) ?>">

                    <div class="pill-group">
                        <div class="pill-headline">
                            <h3>Select date</h3>
                            <span><?= count($availableDates) ?> days available</span>
                        </div>
                        <div class="date-row">
                            <?php foreach ($availableDates as $date => $slots): ?>
                                <button type="button" class="date-pill <?= ($defaultDate === $date) ? 'active' : '' ?>" data-date="<?= htmlspecialchars($date) ?>">
                                    <span><?= htmlspecialchars((new DateTime($date))->format('D')) ?></span>
                                    <strong><?= htmlspecialchars((new DateTime($date))->format('j')) ?></strong>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="pill-group">
                        <div class="pill-headline">
                            <h3>Select time slot</h3>
                            <span>Tap one slot to book</span>
                        </div>
                        <div class="slot-options">
                            <?php if (empty($defaultTimes)): ?>
                                <div class="alert">No slots available for the selected day.</div>
                            <?php else: ?>
                                <?php foreach ($defaultTimes as $slot): ?>
                                    <button type="button" class="slot-pill <?= ($defaultTime === $slot) ? 'active' : '' ?>" data-time="<?= htmlspecialchars($slot) ?>"><?= htmlspecialchars($slot) ?></button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-footer bottom-footer">
                        <button type="submit" class="button button-primary full-width">Book an Appointment</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="schedule-card schedule-compact">
            <div class="schedule-header">
                <div>
                    <h2><?= htmlspecialchars($selectedDoctor['name']) ?>’s schedule</h2>
                    <p>Available slots for the next two weeks.</p>
                </div>
                <span class="schedule-summary">Tabular view</span>
            </div>
            <?php if (empty($availableDates)): ?>
                <div class="alert">No available slots found for the next 14 days.</div>
            <?php else: ?>
                <table class="schedule-table schedule-clickable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Available slots</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availableDates as $date => $slots): ?>
                            <tr class="schedule-row" data-date="<?= htmlspecialchars($date) ?>">
                                <td><?= htmlspecialchars((new DateTime($date))->format('D, M j')) ?></td>
                                <td>
                                    <div class="slot-row">
                                        <?php foreach ($slots as $slot): ?>
                                            <span class="schedule-pill"><?= htmlspecialchars($slot) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <script>
            window.__availableDates = <?= json_encode($availableDates) ?>;
        </script>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
