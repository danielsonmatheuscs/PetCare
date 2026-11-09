<?php
require_once __DIR__ . '/auth.php';
$databaseUrl = getenv('DATABASE_URL') ?: '';
try {
    if ($databaseUrl !== '') {
        // Render/Supabase PostgreSQL
        $parts = parse_url($databaseUrl);
        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? 5432;
        $name = ltrim($parts['path'] ?? '/postgres', '/');
        $user = urldecode($parts['user'] ?? '');
        $pass = urldecode($parts['pass'] ?? '');
        if (!$host || !$user || !$pass) throw new RuntimeException('DATABASE_URL inválida.');
        $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode=require";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } else {
        // WAMP/MySQL local
        $host = getenv('DB_HOST') ?: 'localhost';
        $name = getenv('DB_NAME') ?: 'petcare';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';
        $pdo = new PDO("mysql:host={$host};dbname={$name};charset={$charset}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
} catch (Throwable $e) {
    error_log('PetCare DB: ' . $e->getMessage());
    http_response_code(500);
    exit('Falha de conexão com o banco. Confira o WAMP/MySQL ou as variáveis DATABASE_URL do ambiente.');
}
