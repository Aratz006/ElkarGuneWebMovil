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

// Validar campos requeridos
if (empty($date) || $space === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Beharrezko eremuak falta dira']);
    exit();
}

// Validar que la fecha esté dentro del rango permitido
$gaur = date('Y-m-d');
$fechaLimite = date('Y-m-d', strtotime('+2 months'));

if ($date < $gaur || $date > $fechaLimite) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Data ez da baliozkoa. Data gaur eta hurrengo bi hilabeteen artean egon behar da']);
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
    
    // Verificar si el espacio ya está reservado
    if ($erreserbaElementua->erreEleEgiaztatu($space, $date, $type)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Espazioa dagoeneko erreserbatuta dago']);
        exit();
    }
    
    // Crear nueva reserva
    $erreserba = new Erreserba();
    $idErreserba = $erreserba->erreserbaSartu($idBazkidea, $type, $date);
    
    // Añadir elemento de reserva
    $erreserbaElementua->erreEleGehitu($idErreserba, $space);
    
    // Actualizar comensales
    $erreserba->erreserbaEguneratu($idErreserba);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Erreserba ondo gorde da']);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Errorea erreserba egiterakoan: ' . $e->getMessage()]);
}