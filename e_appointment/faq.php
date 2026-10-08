<?php
// Public FAQ page - accessible without login
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<div class="container page-faq">
    <h2>Frequently Asked Questions</h2>
    <div class="faq-list">
        <div class="faq-item">
            <h3>How do I login?</h3>
            <p>Go to the <a href="login.php">Login</a> page and enter your email and password. If you don't have an account, <a href="register.php">create one</a> first.</p>
        </div>

        <div class="faq-item">
            <h3>How do I book an appointment?</h3>
            <p>From the <a href="home.php">Doctors</a> page, browse or search for a doctor, open their profile, choose an available time slot, and confirm the appointment.</p>
        </div>

        <div class="faq-item">
            <h3>What terms should I use to search for a doctor?</h3>
            <p>Search by specialty (e.g., "cardiologist", "dentist"), doctor name, or common symptoms (e.g., "toothache", "fever"). Using simple keywords works best.</p>
        </div>

        <div class="faq-item">
            <h3>Do I need an account to book?</h3>
            <p>Yes, you must be logged in to book appointments. Registering lets you manage bookings and view your appointment history.</p>
        </div>

        <div class="faq-item">
            <h3>Can I cancel or reschedule?</h3>
            <p>Yes — go to your <a href="profile.php">Profile</a> or <a href="appointment.php">Appointments</a> to cancel or reschedule a booking, subject to the clinic's cancellation policy.</p>
        </div>

        <div class="faq-item">
            <h3>How do I contact support?</h3>
            <p>If you need help, use the contact options on the site or email support@docknock.example (replace with real support address).</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
