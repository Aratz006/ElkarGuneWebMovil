<?php
$host = '172.16.237.119:3306';
$dbname = 'elkargune';
$username = 'java';
$password = '1mg3';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Konexio errorea: " . $e->getMessage());
}
?>