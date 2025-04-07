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
    
    $sql = "SELECT 
            e.idEspazioa, 
            e.egoera, 
            e.izena, 
            e.gaitasuna,
            CASE 
                WHEN r.idBazkidea = :idBazkidea THEN 'MiReserva'
                WHEN e.egoera = 2 THEN 'Mantentze-lanetan'
                WHEN e.egoera = 1 THEN 'Okupatuta'
                WHEN ee.idErreserba IS NOT NULL THEN 'Reservado'
                ELSE 'Libre'
            END as egoera_testua,
            CASE 
                WHEN r.idBazkidea = :idBazkidea THEN 'azul'
                WHEN e.egoera = 2 THEN 'negro'
                WHEN ee.idErreserba IS NOT NULL THEN 'rojo'
                ELSE 'gris'
            END as color,
            CASE WHEN ee.idErreserba IS NOT NULL OR e.egoera IN (1, 2) THEN 1 ELSE 0 END as reserved,
            r.idBazkidea
            FROM espazioa e
            LEFT JOIN erreserbaelementua ee ON e.idEspazioa = ee.idEspazioa 
                AND ee.idErreserba IN (SELECT idErreserba FROM erreserba WHERE data = :date AND mota = :type)
            LEFT JOIN erreserba r ON ee.idErreserba = r.idErreserba";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':date' => $date,
        ':type' => $type,
        ':idBazkidea' => $idBazkidea
    ]);
    
    $spaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'spaces' => $spaces]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}