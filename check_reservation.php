<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ez baimenduta']);
    exit();
}

$idBazkidea = $_SESSION['erabiltzailea'];
$date = isset($_POST['date']) ? $_POST['date'] : '';
$type = isset($_POST['type']) ? (int)$_POST['type'] : 0;

// Validar campos requeridos
if (empty($date)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Beharrezko eremuak falta dira']);
    exit();
}

try {
    // Verificar si ya existe una reserva para este usuario en esta fecha y tipo
    $checkQuery = "SELECT idErreserba FROM erreserba WHERE idBazkidea = :idBazk AND mota = :mota AND data = :data";
    $checkCmd = $pdo->prepare($checkQuery);
    $checkCmd->execute([
        ':idBazk' => $idBazkidea,
        ':mota' => $type,
        ':data' => date('Y-m-d', strtotime($date))
    ]);

    $idReserva = $checkCmd->fetchColumn();
    $exists = ($idReserva !== false);

    header('Content-Type: application/json');
    echo json_encode([
        'exists' => $exists,
        'idReserva' => $exists ? $idReserva : null
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Errorea erreserba egiaztatzerakoan: ' . $e->getMessage()]);
}