<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function findPatientByEmail(string $email): ?array
{
    $connection = dbConnect();
    $sql = 'SELECT patient_id AS id, full_name AS name, email, password, phone, created_at FROM patients WHERE email = ? LIMIT 1';
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

function registerUser(string $name, string $email, string $password, string $phone = ''): bool
{
    if (findPatientByEmail($email)) {
        return false;
    }
    $connection = dbConnect();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = 'INSERT INTO patients (full_name, email, password, phone, created_at) VALUES (?, ?, ?, ?, ?)';
    $stmt = $connection->prepare($sql);
    $createdAt = date('Y-m-d H:i:s');
    $stmt->bind_param('sssss', $name, $email, $hash, $phone, $createdAt);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function loginUser(string $email, string $password): bool
{
    $user = findPatientByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']);
        // Ensure any existing admin session is cleared when a patient logs in
        if (isset($_SESSION['admin'])) {
            unset($_SESSION['admin']);
        }
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function getAdminByUsername(string $username): ?array
{
    $connection = dbConnect();
    $sql = 'SELECT admin_id AS id, username, password, COALESCE(is_super,0) AS is_super FROM admin WHERE username = ? LIMIT 1';
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

function loginAdmin(string $username, string $password): bool
{
    $admin = getAdminByUsername($username);
    if ($admin && password_verify($password, $admin['password'])) {
        unset($admin['password']);
        // Cast is_super to int for consistency
        $admin['is_super'] = isset($admin['is_super']) ? (int)$admin['is_super'] : 0;
        // Ensure any existing patient session is cleared when an admin logs in
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        $_SESSION['admin'] = $admin;
        return true;
    }
    return false;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function logout(): void
{
    session_unset();
    session_destroy();
}
