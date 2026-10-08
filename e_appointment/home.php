<?php
require_once __DIR__ . '/modules/auth.php';
require_once __DIR__ . '/modules/appointments.php';
requireLogin();
$user = currentUser();
$searchQuery = normalizeDoctorSearchTerm($_GET['q'] ?? '');
$doctors = $searchQuery !== '' ? searchDoctors($searchQuery) : getDoctors();
$keywordMap = getDoctorKeywordMap();
$appointments = userAppointments($user['id']);
$nextAppointment = $appointments[0] ?? null;
$nextAppointmentTime = null;
if ($nextAppointment) {
    $nextAppointmentTime = DateTime::createFromFormat('H:i:s', $nextAppointment['time']) ?: DateTime::createFromFormat('H:i', $nextAppointment['time']);
}
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<div class="hero-banner">
    <div class="hero-copy">
        <span class="badge">Trusted care</span>
        <h2>Find your doctor and book appointments faster.</h2>
        <p>Browse specialists, compare profiles, and reserve the best slots from a clean medical dashboard.</p>
        <div class="hero-icons">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 4a7 7 0 100 14 7 7 0 000-14z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <strong>Find Doctors</strong>
                    <p>Search by specialty or name</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 7h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M4 12h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M17 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <strong>Easy Booking</strong>
                    <p>Reserve slots in seconds</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 4H4a1 1 0 00-1 1v14a1 1 0 001 1h16a1 1 0 001-1V5a1 1 0 00-1-1z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 12h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <strong>Online Consult</strong>
                    <p>Connect with doctors from home</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 20a8 8 0 100-16 8 8 0 000 16z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M7 12h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 7v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <strong>Secure & Safe</strong>
                    <p>Your health data stays protected</p>
                </div>
            </div>
        </div>
        <div class="cta-row">
            <a href="appointment.php" class="button button-primary hero-cta">Book appointment</a>
            <span class="cta-note">Fast booking, secure care, and instant doctor availability.</span>
        </div>
        <div class="info-row">
            <div>
                <strong>Ready now</strong>
                <p><?= count($doctors) ?> available doctors</p>
            </div>
            <div>
                <strong>Next booking</strong>
                <?php if ($nextAppointment): ?>
                    <p><?= htmlspecialchars($nextAppointment['date']) ?> at <?= $nextAppointmentTime ? htmlspecialchars($nextAppointmentTime->format('g:i A')) : htmlspecialchars($nextAppointment['time']) ?></p>
                <?php else: ?>
                    <p>No appointment yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="hero-visual">
        <img src="images/home-hero.svg" alt="Medical booking illustration" class="hero-image">
    </div>
</div>

<div class="grid-2 home-layout">
    <div>
        <div class="form-card search-card">
            <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
            <p>Search for a specialist, choose a doctor, and book faster.</p>
            <form method="get" action="home.php" class="search-form">
                <label for="doctorSearch">Search a Doctor</label>
                <input type="search" id="doctorSearch" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Try: tooth ache, chest pain, child fever, eye pain" autocomplete="off">
            </form>
        </div>

        <div class="doctor-grid" id="doctorGrid">
    <?php if (empty($doctors)): ?>
        <div class="alert">No doctors found.</div>
    <?php else: ?>
        <?php foreach ($doctors as $doctor): ?>
            <?php $doctorKeywords = array_values(array_unique(array_map('strtolower', array_merge([$doctor['specialization']], $keywordMap[$doctor['specialization']] ?? [])))); ?>
            <div class="doctor-card" data-name="<?= htmlspecialchars(strtolower($doctor['name'])) ?>" data-specialty="<?= htmlspecialchars(strtolower($doctor['specialization'])) ?>" data-keywords="<?= htmlspecialchars(implode(' ', $doctorKeywords)) ?>">
                <div class="doctor-image"></div>
                <div class="doctor-card-body">
                    <div class="doctor-card-top">
                        <h3><?= htmlspecialchars($doctor['name']) ?></h3>
                        <span class="badge"><?= htmlspecialchars($doctor['specialization']) ?></span>
                    </div>
                    <p class="doctor-summary">Experienced <?= htmlspecialchars($doctor['experience']) ?> years doctor offering friendly and professional care.</p>
                    <div class="info-row doctor-card-meta">
                        <span>Fee: ₹<?= htmlspecialchars($doctor['fee']) ?></span>
                        <span><?= htmlspecialchars(ucfirst($doctor['status'])) ?></span>
                    </div>
                </div>
                <div class="action-row">
                    <?php if ($doctor['status'] === 'active'): ?>
                        <a href="appointment.php?doctor=<?= urlencode($doctor['id']) ?>" class="button button-primary">Book</a>
                    <?php else: ?>
                        <span class="badge">Inactive</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
        <div id="doctorSearchEmpty" class="alert" style="display:none;">No doctors found.</div>
    </div>
    <aside class="sidebar-widgets">
        <div class="widget-card">
            <div class="widget-title">Upcoming appointment</div>
            <?php if ($nextAppointment): ?>
                <?php $nextAppointmentTime = DateTime::createFromFormat('H:i:s', $nextAppointment['time']) ?: DateTime::createFromFormat('H:i', $nextAppointment['time']); ?>
                <strong class="widget-stat"><?= htmlspecialchars($nextAppointment['date']) ?></strong>
                <p class="widget-note">Appointment with <?= htmlspecialchars($nextAppointment['doctorName']) ?> (<?= htmlspecialchars($nextAppointment['specialty']) ?>) at <?= $nextAppointmentTime ? htmlspecialchars($nextAppointmentTime->format('g:i A')) : htmlspecialchars($nextAppointment['time']) ?></p>
            <?php else: ?>
                <p class="widget-note">You have no appointments yet. Book a doctor to see your next visit here.</p>
            <?php endif; ?>
        </div>
        <div class="widget-card">
            <div class="widget-title">Doctors online</div>
            <strong class="widget-stat"><?= count($doctors) ?></strong>
            <p class="widget-note">Active specialists ready to help you today.</p>
        </div>
    </aside>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
