<?php
require_once __DIR__ . '/modules/db.php';
require_once __DIR__ . '/modules/appointments.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

$message = '';
$doctors = getDoctors();

// Handle add / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $doctorId = $_POST['doctor_id'] ?? '';
        $day = $_POST['day_of_week'] ?? '';
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';
        $slot = (int)($_POST['slot_duration'] ?? 30);
        if ($doctorId && $day && $start && $end) {
            if (addDoctorScheduleEntry($doctorId, $day, $start, $end, $slot)) {
                $message = 'Schedule added.';
            } else {
                $message = 'Failed to add schedule.';
            }
        } else {
            $message = 'All fields are required.';
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $doctorId = $_POST['doctor_id'] ?? '';
        $day = $_POST['day_of_week'] ?? '';
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';
        $slot = (int)($_POST['slot_duration'] ?? 30);
        if ($scheduleId && $doctorId && $day && $start && $end) {
            if (updateDoctorScheduleEntry($scheduleId, $doctorId, $day, $start, $end, $slot)) {
                $message = 'Schedule updated.';
            } else {
                $message = 'No changes or update failed.';
            }
        } else {
            $message = 'Missing fields for update.';
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        if ($scheduleId) {
            if (deleteDoctorScheduleEntry($scheduleId)) {
                $message = 'Schedule deleted.';
            } else {
                $message = 'Unable to delete schedule.';
            }
        }
    }
}

// Fetch schedules grouped by doctor
$schedulesByDoctor = [];
foreach ($doctors as $d) {
    $entries = getDoctorScheduleEntries((string)$d['id']);
    $schedulesByDoctor[$d['id']] = $entries;
}
?>
<?php $hideFooterFaq = true; include __DIR__ . '/templates/header.php'; ?>
<div class="form-card">
    <h2>Doctor Schedules</h2>
    <p>View and manage doctor schedules. Existing schedules are preserved; new schedules are added alongside them.</p>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <?php foreach ($doctors as $doc): ?>
        <div class="info-card">
            <h3><?= htmlspecialchars($doc['name']) ?> — <?= htmlspecialchars($doc['specialization']) ?></h3>
            <div class="schedule-rows">
                <?php $entries = $schedulesByDoctor[$doc['id']] ?? []; ?>
                <?php if (empty($entries)): ?>
                    <p class="alert">No schedules defined for this doctor.</p>
                <?php else: ?>
                    <table class="table">
                        <thead><tr><th>Day</th><th>Start</th><th>End</th><th>Slot (min)</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($entries as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['day_of_week']) ?></td>
                                    <td><?= htmlspecialchars($e['start_time']) ?></td>
                                    <td><?= htmlspecialchars($e['end_time']) ?></td>
                                    <td><?= htmlspecialchars($e['slot_duration']) ?></td>
                                    <td>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($e['id']) ?>">
                                            <button type="submit" class="button button-outline" onclick="return confirm('Delete this schedule?')">Delete</button>
                                        </form>
                                        <details style="display:inline-block;margin-left:8px">
                                            <summary class="button button-outline">Edit</summary>
                                            <form method="post" style="margin-top:8px">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($e['id']) ?>">
                                                <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doc['id']) ?>">
                                                <label>Day<br><input name="day_of_week" value="<?= htmlspecialchars($e['day_of_week']) ?>"></label>
                                                <label>Start<br><input name="start_time" value="<?= htmlspecialchars($e['start_time']) ?>"></label>
                                                <label>End<br><input name="end_time" value="<?= htmlspecialchars($e['end_time']) ?>"></label>
                                                <label>Slot duration<br><input name="slot_duration" value="<?= htmlspecialchars($e['slot_duration']) ?>"></label>
                                                <br><button type="submit" class="button button-primary">Save</button>
                                            </form>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <hr>
                <h4>Add schedule for <?= htmlspecialchars($doc['name']) ?></h4>
                <form method="post">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doc['id']) ?>">
                    <label>Day of week<br>
                        <select name="day_of_week">
                            <option>Monday</option>
                            <option>Tuesday</option>
                            <option>Wednesday</option>
                            <option>Thursday</option>
                            <option>Friday</option>
                            <option>Saturday</option>
                            <option>Sunday</option>
                        </select>
                    </label>
                    <label>Start time<br><input name="start_time" type="time" required></label>
                    <label>End time<br><input name="end_time" type="time" required></label>
                    <label>Slot duration (minutes)<br><input name="slot_duration" type="number" value="30" min="5" required></label>
                    <br><button type="submit" class="button button-primary">Add Schedule</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
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

$doctorId = $_GET['doctor'] ?? $_POST['doctor_id'] ?? null;
if (!$doctorId) {
    header('Location: admin_doctors.php');
    exit;
}

$doctor = getDoctorById($doctorId);
if (!$doctor) {
    header('Location: admin_doctors.php');
    exit;
}

$message = '';
$editingEntry = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $scheduleId = !empty($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : null;
    $dayOfWeek = trim($_POST['day_of_week'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $slotDuration = (int)($_POST['slot_duration'] ?? 0);

    if ($action === 'save_schedule') {
        if ($dayOfWeek === '' || $startTime === '' || $endTime === '' || $slotDuration <= 0) {
            $message = 'Please fill in all schedule fields and use a positive slot duration.';
        } elseif (strtotime($endTime) <= strtotime($startTime)) {
            $message = 'End time must be later than start time.';
        } else {
            if ($scheduleId) {
                updateDoctorScheduleEntry($scheduleId, $doctorId, $dayOfWeek, $startTime, $endTime, $slotDuration);
                $message = 'Schedule entry updated successfully.';
            } else {
                addDoctorScheduleEntry($doctorId, $dayOfWeek, $startTime, $endTime, $slotDuration);
                $message = 'Schedule entry added successfully.';
            }
            $editingEntry = null;
        }
    }

    if ($action === 'delete_schedule' && $scheduleId) {
        deleteDoctorScheduleEntry($scheduleId);
        $message = 'Schedule entry deleted successfully.';
    }
}

if (isset($_GET['edit_entry'])) {
    $editingEntry = getDoctorScheduleEntryById((int)$_GET['edit_entry']);
    if ($editingEntry && (string)$editingEntry['doctor_id'] !== (string)$doctor['id']) {
        $editingEntry = null;
    }
}

$scheduleEntries = getDoctorScheduleEntries($doctor['id']);
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<div class="grid-2">
    <div class="form-card">
        <h2>Manage <?= htmlspecialchars($doctor['name']) ?>’s schedule</h2>
        <p>Define the weekly availability and slot length used for appointments.</p>
        <?php if ($message): ?>
            <div class="message-box"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <div class="note-box">
            <p>Schedule entries determine when this doctor can receive appointments. Each row becomes a set of time slots on the selected day.</p>
        </div>
        <form method="post" action="admin_schedule.php">
            <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doctor['id']) ?>">
            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($editingEntry['id'] ?? '') ?>">

            <label for="day_of_week">Day of week</label>
            <select id="day_of_week" name="day_of_week" required>
                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                    <option value="<?= $day ?>" <?= ($editingEntry['day_of_week'] ?? '') === $day ? 'selected' : '' ?>><?= $day ?></option>
                <?php endforeach; ?>
            </select>

            <label for="start_time">Start time</label>
            <input type="time" id="start_time" name="start_time" value="<?= htmlspecialchars($editingEntry['start_time'] ?? '') ?>" required>

            <label for="end_time">End time</label>
            <input type="time" id="end_time" name="end_time" value="<?= htmlspecialchars($editingEntry['end_time'] ?? '') ?>" required>

            <label for="slot_duration">Slot duration (minutes)</label>
            <input type="number" id="slot_duration" name="slot_duration" min="15" step="5" value="<?= htmlspecialchars($editingEntry['slot_duration'] ?? '30') ?>" required>

            <div class="form-footer">
                <button type="submit" class="button button-primary" name="action" value="save_schedule"><?= $editingEntry ? 'Save changes' : 'Add entry' ?></button>
                <?php if ($editingEntry): ?>
                    <a href="admin_schedule.php?doctor=<?= urlencode($doctor['id']) ?>" class="button button-outline">Add new entry</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="schedule-card">
        <h2>Current schedule entries</h2>
        <p>These entries are used to generate appointment slots for the next two weeks.</p>
        <?php if (empty($scheduleEntries)): ?>
            <div class="alert">No schedule entries have been defined for this doctor yet.</div>
        <?php else: ?>
            <?php foreach ($scheduleEntries as $entry): ?>
                <div class="doctor-card">
                    <h3><?= htmlspecialchars($entry['day_of_week']) ?></h3>
                    <div class="doctor-details">
                        <span><?= htmlspecialchars($entry['start_time']) ?> → <?= htmlspecialchars($entry['end_time']) ?></span>
                        <span>Slots: <?= htmlspecialchars($entry['slot_duration']) ?> min</span>
                    </div>
                    <div class="action-row">
                        <a href="admin_schedule.php?doctor=<?= urlencode($doctor['id']) ?>&edit_entry=<?= urlencode($entry['id']) ?>" class="button button-outline">Edit</a>
                        <form method="post" action="admin_schedule.php" style="display:inline-flex; gap: 8px; margin:0;">
                            <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doctor['id']) ?>">
                            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($entry['id']) ?>">
                            <button type="submit" name="action" value="delete_schedule" class="button button-outline">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
