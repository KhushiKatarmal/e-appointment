    </main>
    <footer class="site-footer">
        <?php if (empty($hideFooterFaq)): ?>
        <div class="footer-faq">
            <h4>Quick FAQ</h4>
            <ul>
                <li><strong>How do I login?</strong> Use the <a href="login.php">Login</a> page and enter your credentials. If you're new, <a href="register.php">Register</a> first.</li>
                <li><strong>How do I book an appointment?</strong> Browse doctors on the <a href="home.php">Doctors</a> page, pick a doctor, select a slot, and confirm the booking.</li>
                <li><strong>What terms should I use to search?</strong> Try specialty names or symptoms, e.g. "cardiologist", "dentist", "toothache", "fever".</li>
            </ul>
            <p><a href="faq.php">Read more FAQs</a></p>
        </div>
        <?php endif; ?>
        <p>© 2026 dockNock · Smart health booking made simple.</p>
    </footer>
</div>
</body>
</html>
