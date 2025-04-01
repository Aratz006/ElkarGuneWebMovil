<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$idBazkidea = $_SESSION['erabiltzailea'];
$date = isset($_POST['date']) ? $_POST['date'] : '';
$type = isset($_POST['type']) ? (int)$_POST['type'] : 0;
$space = isset($_POST['space']) ? (int)$_POST['space'] : 0;

if (empty($date) || $space === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

try {
    // Verificar si el espacio ya está reservado
    $checkSql = "SELECT COUNT(*) FROM erreserba 
                 WHERE id_espazioa = :space 
                 AND data = :date 
                 AND mota = :type";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
        ':space' => $space,
        ':date' => $date,
        ':type' => $type
    ]);
    
    if ($checkStmt->fetchColumn() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Space already reserved']);
        exit();
    }

    // Insertar la nueva reserva
    $sql = "INSERT INTO erreserba (id_bazkidea, id_espazioa, data, mota) 
            VALUES (:idBazkidea, :space, :date, :type)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':idBazkidea' => $idBazkidea,
        ':space' => $space,
        ':date' => $date,
        ':type' => $type
    ]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}