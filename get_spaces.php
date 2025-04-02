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
    $sql = "SELECT e.idEspazioa, e.egoera, e.izena, e.gaitasuna,
            CASE WHEN ee.idErreserba IS NOT NULL THEN 1 ELSE 0 END as reserved,
            r.idBazkidea,
            CASE 
                WHEN e.egoera = 0 THEN 'Libre'
                WHEN e.egoera = 1 THEN 'Okupatuta'
                WHEN e.egoera = 2 THEN 'Mantentze-lanetan'
                ELSE 'Ezezaguna'
            END as egoera_testua
            FROM espazioa e
            LEFT JOIN erreserbaelementua ee ON e.idEspazioa = ee.idEspazioa 
            LEFT JOIN erreserba r ON ee.idErreserba = r.idErreserba AND r.data = :date AND r.mota = :type";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':date' => $date,
        ':type' => $type
    ]);
    
    $spaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'spaces' => $spaces]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}