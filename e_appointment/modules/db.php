<?php
function dbPdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=e_appointment;charset=utf8mb4';
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function dbConnect(): mysqli
{
    static $connection = null;
    if ($connection === null) {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $connection = new mysqli('localhost', 'root', '', 'e_appointment');
            $connection->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            die('Database Connection Error: ' . $e->getMessage() . 
                '<br>Please ensure:<br>1. XAMPP MySQL server is running<br>2. Database "e_appointment" exists<br>3. Check your connection credentials');
        }
    }
    return $connection;
}

function dbPrepare(string $sql, array $params = []): mysqli_stmt
{
    $conn = dbConnect();
    $stmt = $conn->prepare($sql);
    if ($params) {
        $types = '';
        $refs = [];
        foreach ($params as $key => $value) {
            $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
            $refs[] = &$params[$key];
        }
        array_unshift($refs, $types);
        mysqli_stmt_bind_param($stmt, ...$refs);
    }
    $stmt->execute();
    return $stmt;
}

function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = dbPrepare($sql, $params);
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function dbFetch(string $sql, array $params = []): ?array
{
    $rows = dbFetchAll($sql, $params);
    return $rows[0] ?? null;
}
