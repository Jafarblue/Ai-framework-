<?php
declare(strict_types=1);

$host = 'localhost';
$dbname = 'powerreport';
$dbuser = 'root';
$dbpass = ''; // Default XAMPP password. Set your MySQL password if required.

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbuser, $dbpass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Could not connect to the database. Check the database configuration.');
}