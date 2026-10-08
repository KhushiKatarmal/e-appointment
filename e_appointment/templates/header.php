<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dockNock</title>
    <link rel="stylesheet" href="css/style.css">
    <script defer src="js/main.js"></script>
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <div class="brand">
                <div class="brand-mark">
                    <img src="images/logo.svg" alt="dockNock Logo">
                </div>
                <div>
                    <h1>dockNock</h1>
                    <p>Book doctors easily with clean schedules and fast appointments.</p>
                </div>
            </div>
        </header>
        <?php if (empty($hideNav)): ?>
            <?php include __DIR__ . '/nav.php'; ?>
        <?php endif; ?>
        <main class="page-content">
