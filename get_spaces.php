<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$date = isset($_POST['date']) ? $_POST['date'] : '';
$type = isset($_POST['type']) ? (int)$_POST['type'] : 0;

if (empty($date)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Date is required']);
    exit();
}

try {
    $idBazkidea = $_SESSION['erabiltzailea'];
    
    $sql = "SELECT e.izena FROM espazioa e JOIN erreserbaelementua ee ON e.idEspazioa = ee.idEspazioa JOIN erreserba er ON ee.idErreserba = er.idErreserba WHERE er.mota = :type AND er.data = :date";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':date' => $date,
        ':type' => $type,
        // ':idBazkidea' => $idBazkidea
    ]);
    
    $spaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'spaces' => $spaces]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}