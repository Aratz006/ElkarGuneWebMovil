<?php
session_start();
require_once 'config.php';
require_once 'classes/Erreserba.php';
require_once 'classes/ErreserbaElementua.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ez baimenduta']);
    exit();
}

$idBazkidea = $_SESSION['erabiltzailea'];
$date = isset($_POST['date']) ? $_POST['date'] : '';
$type = isset($_POST['type']) ? (int)$_POST['type'] : 0;
$space = isset($_POST['space']) ? (int)$_POST['space'] : 0;
$idReserva = isset($_POST['idReserva']) ? (int)$_POST['idReserva'] : 0;

// Validar campos requeridos
if (empty($date) || $space === 0 || $idReserva === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Beharrezko eremuak falta dira']);
    exit();
}

try {
    // Verificar si el espacio existe y está disponible
    $checkSpaceSql = "SELECT egoera FROM espazioa WHERE idEspazioa = :space";
    $checkSpaceStmt = $pdo->prepare($checkSpaceSql);
    $checkSpaceStmt->execute([':space' => $space]);
    $spaceStatus = $checkSpaceStmt->fetchColumn();

    if ($spaceStatus === false) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Espazioa ez da existitzen']);
        exit();
    }

    if ($spaceStatus != 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Espazioa ez dago eskuragarri']);
        exit();
    }

    $erreserbaElementua = new ErreserbaElementua();
    
    // Verificar si el espacio ya está reservado por otro usuario
    if ($erreserbaElementua->erreEleEgiaztatu($space, $date, $type)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Espazioa dagoeneko erreserbatuta dago']);
        exit();
    }
    
    // Eliminar elementos de reserva anteriores
    $erreserbaElementua->erreEleEzabatu($idReserva);
    
    // Añadir nuevo elemento de reserva
    $erreserbaElementua->erreEleGehitu($idReserva, $space);
    
    // Actualizar comensales
    $erreserba = new Erreserba();
    $erreserba->erreserbaEguneratu($idReserva);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Erreserba ondo eguneratu da']);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Errorea erreserba eguneratzerakoan: ' . $e->getMessage()]);
}