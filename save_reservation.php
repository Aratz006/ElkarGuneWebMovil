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
        echo json_encode(['error' => 'Espazioa dagoeneko erreserbatuta dago']);
        exit();
    }

    // Verificar si el espacio existe y está disponible
    $checkSpaceSql = "SELECT egoera FROM espazioa WHERE id = :space";
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