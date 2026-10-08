<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function getAppointments(): array
{
    $sql = 'SELECT a.appointment_id AS id, a.patient_id AS userId, a.doctor_id AS doctorId, d.doctor_name AS doctorName, d.specialization AS specialty, a.appointment_date AS date, a.appointment_time AS time, a.status, a.created_at FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id ORDER BY a.appointment_date ASC, a.appointment_time ASC';
    return dbFetchAll($sql);
}

/**
 * Attempt to add an appointment.
 * Returns an array with keys: success (bool) and error (string|null).
 */
function addAppointment(string $userId, string $doctorId, string $date, string $time): array
{
    $patientId = (int)$userId;
    $docId = (int)$doctorId;
    $date = trim($date);
    $time = trim($time);

    if ($patientId <= 0 || $docId <= 0 || $date === '' || $time === '') {
        return ['success' => false, 'error' => 'invalid_ids'];
    }

    try {
        $pdo = dbPdo();
        $conflictStmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != "Cancelled"');
        $conflictStmt->execute([$docId, $date, $time]);
        $conflictCount = (int)$conflictStmt->fetchColumn();
        if ($conflictCount > 0) {
            error_log(sprintf('addAppointment conflict: doctor=%d date=%s time=%s conflicts=%d', $docId, $date, $time, $conflictCount));
            return ['success' => false, 'error' => 'conflict'];
        }

        $createdAt = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$patientId, $docId, $date, $time, 'Pending', $createdAt]);
        return ['success' => $stmt->rowCount() > 0, 'error' => null];
    } catch (PDOException $e) {
        error_log('addAppointment error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'db_error'];
    }
}

function userAppointments(string $userId): array
{
    ensureAppointmentCancellationTracking();
    $sql = 'SELECT a.appointment_id AS id, 
    a.patient_id AS userId, a.doctor_id AS doctorId, d.doctor_name AS doctorName, 
    d.specialization AS specialty, a.appointment_date AS date, a.appointment_time AS time, 
    a.status, a.cancelled_by AS cancelledBy FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id WHERE a.patient_id = ? ORDER BY a.appointment_date ASC, a.appointment_time ASC';
    return dbFetchAll($sql, [$userId]);
}

function ensureAppointmentCancellationTracking(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }

    $connection = dbConnect();
    $result = $connection->query("SHOW COLUMNS FROM appointments LIKE 'cancelled_by'");
    if ($result->num_rows === 0) {
        $connection->query("ALTER TABLE appointments ADD COLUMN cancelled_by VARCHAR(20) DEFAULT NULL");
    }
    $checked = true;
}

function cancelAppointmentByUser(int $appointmentId, string $userId): bool
{
    ensureAppointmentCancellationTracking();
    $connection = dbConnect();
    $sql = 'UPDATE appointments SET status = ?, cancelled_by = ? WHERE appointment_id = ? AND patient_id = ? AND status != ?';
    $stmt = $connection->prepare($sql);
    $status = 'Cancelled';
    $cancelledBy = 'Patient';
    $stmt->bind_param('ssiss', $status, $cancelledBy, $appointmentId, $userId, $status);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function cancelAppointment(int $appointmentId): bool
{
    ensureAppointmentCancellationTracking();
    $connection = dbConnect();
    $sql = 'UPDATE appointments SET status = ?, cancelled_by = ? WHERE appointment_id = ? AND status != ?';
    $stmt = $connection->prepare($sql);
    $status = 'Cancelled';
    $cancelledBy = 'Admin';
    $stmt->bind_param('ssis', $status, $cancelledBy, $appointmentId, $status);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function getDoctors(): array
{
    $sql = 'SELECT doctor_id AS id, doctor_name AS name, specialization, experience_years AS experience, phone, email, consultation_fee AS fee, status FROM doctors WHERE status = "active" ORDER BY doctor_name ASC';
    return dbFetchAll($sql);
}

function normalizeDoctorSearchTerm(string $query): string
{
    return trim(strtolower($query));
}

function getDoctorKeywordMap(): array
{
    $pdo = dbPdo();
    $stmt = $pdo->prepare('SELECT specialization, keyword FROM specialization_keywords ORDER BY specialization, keyword');
    $stmt->execute();

    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $specialization = trim((string)$row['specialization']);
        $keyword = trim((string)$row['keyword']);
        if ($specialization === '' || $keyword === '') {
            continue;
        }
        $map[$specialization][] = $keyword;
    }

    return $map;
}

function buildDoctorSearchCandidates(string $query): array
{
    $normalized = normalizeDoctorSearchTerm($query);
    if ($normalized === '') {
        return [];
    }

    $tokens = preg_split('/\s+/', $normalized) ?: [];
    $tokens = array_values(array_filter($tokens, static fn (string $token): bool => $token !== ''));
    if ($tokens === []) {
        return [];
    }

    $candidates = [$normalized];

    if (count($tokens) === 1) {
        $candidates[] = $tokens[0];
        return array_values(array_unique(array_filter($candidates, static fn (string $candidate): bool => $candidate !== '')));
    }

    $maxPhraseLength = min(count($tokens), 3);
    for ($phraseLength = 2; $phraseLength <= $maxPhraseLength; $phraseLength++) {
        for ($start = 0; $start + $phraseLength <= count($tokens); $start++) {
            $phrase = implode(' ', array_slice($tokens, $start, $phraseLength));
            if ($phrase !== '') {
                $candidates[] = $phrase;
            }
        }
    }

    return array_values(array_unique(array_filter($candidates, static fn (string $candidate): bool => $candidate !== '')));
}

function getDoctorKeywordMatches(string $query): array
{
    $normalized = normalizeDoctorSearchTerm($query);
    if ($normalized === '') {
        return [];
    }

    $pdo = dbPdo();
    $specializations = [];

    foreach (buildDoctorSearchCandidates($normalized) as $candidate) {
        $pattern = '%' . $candidate . '%';
        $stmt = $pdo->prepare('SELECT DISTINCT specialization FROM specialization_keywords WHERE LOWER(keyword) LIKE ?');
        $stmt->execute([$pattern]);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $specialization) {
            $specializations[trim((string)$specialization)] = true;
        }
    }

    return array_keys($specializations);
}

function searchDoctors(string $query): array
{
    $normalized = normalizeDoctorSearchTerm($query);
    if ($normalized === '') {
        return getDoctors();
    }

    $pdo = dbPdo();
    $sql = 'SELECT doctor_id AS id, doctor_name AS name, specialization, experience_years AS experience, phone, email, consultation_fee AS fee, status FROM doctors WHERE status = ? AND (LOWER(doctor_name) LIKE ? OR LOWER(specialization) LIKE ?)';
    $params = ['active', '%' . $normalized . '%', '%' . $normalized . '%'];

    $keywordSpecializations = getDoctorKeywordMatches($normalized);
    if (!empty($keywordSpecializations)) {
        $placeholders = implode(',', array_fill(0, count($keywordSpecializations), '?'));
        $sql .= ' OR LOWER(specialization) IN (' . $placeholders . ')';
        $params = array_merge($params, array_map('strtolower', $keywordSpecializations));
    }

    $sql .= ' ORDER BY doctor_name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $uniqueDoctors = [];
    foreach ($results as $doctor) {
        $uniqueDoctors[(int)$doctor['id']] = $doctor;
    }

    return array_values($uniqueDoctors);
}

function getDoctorById(string $id): ?array
{
    return dbFetch('SELECT doctor_id AS id, doctor_name AS name, specialization, experience_years AS experience, phone, email, consultation_fee AS fee, status FROM doctors WHERE doctor_id = ? LIMIT 1', [$id]);
}

function getDoctorSchedule(string $doctorId): array
{
    return dbFetchAll('SELECT schedule_id, day_of_week, start_time, end_time, slot_duration FROM doctor_schedule WHERE doctor_id = ? ORDER BY FIELD(day_of_week, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"), start_time ASC', [$doctorId]);
}

function getDoctorScheduleEntries(string $doctorId): array
{
    return dbFetchAll('SELECT schedule_id AS id, day_of_week, start_time, end_time, slot_duration FROM doctor_schedule WHERE doctor_id = ? ORDER BY FIELD(day_of_week, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"), start_time ASC', [$doctorId]);
}

function getDoctorScheduleEntryById(int $scheduleId): ?array
{
    return dbFetch('SELECT schedule_id AS id, doctor_id, day_of_week, start_time, end_time, slot_duration FROM doctor_schedule WHERE schedule_id = ? LIMIT 1', [$scheduleId]);
}

function addDoctorScheduleEntry(int $doctorId, string $dayOfWeek, string $startTime, string $endTime, int $slotDuration): bool
{
    $sql = 'INSERT INTO doctor_schedule (doctor_id, day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?, ?)';
    $connection = dbConnect();
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('isssi', $doctorId, $dayOfWeek, $startTime, $endTime, $slotDuration);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function updateDoctorScheduleEntry(int $scheduleId, int $doctorId, string $dayOfWeek, string $startTime, string $endTime, int $slotDuration): bool
{
    $sql = 'UPDATE doctor_schedule SET day_of_week = ?, start_time = ?, end_time = ?, slot_duration = ? WHERE schedule_id = ? AND doctor_id = ?';
    $connection = dbConnect();
    $stmt = $connection->prepare($sql);
    // types: s (day), s (start), s (end), i (slot), i (schedule_id), i (doctor_id)
    $stmt->bind_param('sssiii', $dayOfWeek, $startTime, $endTime, $slotDuration, $scheduleId, $doctorId);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function deleteDoctorScheduleEntry(int $scheduleId): bool
{
    $sql = 'DELETE FROM doctor_schedule WHERE schedule_id = ?';
    $connection = dbConnect();
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $scheduleId);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function getDefaultDoctorSchedule(): array
{
    return [
        ['day_of_week' => 'Monday', 'start_time' => '09:00', 'end_time' => '12:00', 'slot_duration' => 60],
        ['day_of_week' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '14:00', 'slot_duration' => 60],
        ['day_of_week' => 'Wednesday', 'start_time' => '09:00', 'end_time' => '13:00', 'slot_duration' => 60],
        ['day_of_week' => 'Thursday', 'start_time' => '11:00', 'end_time' => '15:00', 'slot_duration' => 60],
        ['day_of_week' => 'Friday', 'start_time' => '09:00', 'end_time' => '12:00', 'slot_duration' => 60],
    ];
}

function getDoctorAvailability(string $doctorId, string $date): array
{
    $schedule = getDoctorSchedule($doctorId);
    if (empty($schedule)) {
        $schedule = getDefaultDoctorSchedule();
    }
    $day = (new DateTime($date))->format('l');
    $slots = [];
    foreach ($schedule as $item) {
        if ($item['day_of_week'] === $day) {
            $start = new DateTime($item['start_time']);
            $end = new DateTime($item['end_time']);
            $duration = (int)$item['slot_duration'];
            while ($start < $end) {
                $slots[] = $start->format('H:i');
                $start->modify("+{$duration} minutes");
            }
        }
    }
    return array_values(array_unique($slots));
}

function getDoctorAvailabilities(string $doctorId, int $days = 14): array
{
    $available = [];
    $today = new DateTime();
    for ($i = 0; $i < $days; $i++) {
        $date = (clone $today)->modify("+$i day");
        $slots = getDoctorAvailability($doctorId, $date->format('Y-m-d'));
        if ($slots) {
            $available[$date->format('Y-m-d')] = $slots;
        }
    }
    return $available;
}
