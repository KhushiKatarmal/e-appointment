<?php
require_once __DIR__ . '/modules/auth.php';
require_once __DIR__ . '/modules/appointments.php';
requireLogin();

$searchQuery = normalizeDoctorSearchTerm($_GET['q'] ?? '');
$doctors = $searchQuery !== '' ? searchDoctors($searchQuery) : getDoctors();
$keywordMap = getDoctorKeywordMap();
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<div class="form-card search-card">
    <h2>Find a doctor</h2>
    <p>Search for a specialist by name or area of care.</p>
    <form method="get" action="doctors.php" class="search-form">
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
<?php include __DIR__ . '/templates/footer.php'; ?>