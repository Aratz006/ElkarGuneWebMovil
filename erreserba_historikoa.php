<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Location: index.html');
    exit();
}

$idBazkidea = $_SESSION['erabiltzailea'];
$currentDate = date('Y-m-d');

try {
    // Verificar la sesión del usuario
    echo "<!-- Debug: ID Bazkidea = " . htmlspecialchars($idBazkidea) . " -->";
    echo "<!-- Debug: Current Date = " . htmlspecialchars($currentDate) . " -->";

    // Primero verificar si el usuario existe en la tabla bazkidea
    $checkUser = "SELECT idBazkidea FROM bazkidea WHERE idBazkidea = :idBazkidea";
    $checkStmt = $pdo->prepare($checkUser);
    $checkStmt->execute([':idBazkidea' => $idBazkidea]);
    
    if (!$checkStmt->fetch()) {
        echo "<!-- Debug: Usuario no encontrado en la tabla bazkidea -->";
        throw new PDOException("Usuario no encontrado en la tabla bazkidea");
    }

    $sql = "SELECT CONCAT(b.izena, ' ', b.abizenak) AS 'Bazkidea', 
                   e.idErreserba AS 'Erreserba zenbakia', 
                   e.data AS 'Data', 
                   CASE e.mota 
                       WHEN 0 THEN 'Bazkaria' 
                       WHEN 1 THEN 'Afaria' 
                   END AS 'Mota' 
            FROM erreserba e 
            JOIN bazkidea b ON e.idBazkidea = b.idBazkidea 
            WHERE e.idBazkidea = :idBazkidea 
            AND DATE(e.data) < CURDATE() 
            ORDER BY e.idErreserba ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':idBazkidea' => $idBazkidea]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Mostrar la consulta SQL con el valor real
    $debugSql = str_replace(':idBazkidea', $idBazkidea, $sql);
    echo "<!-- Debug: SQL Query = " . htmlspecialchars($debugSql) . " -->";
    
    // Debug: Mostrar número de resultados
    echo "<!-- Debug: Número de reservas encontradas = " . count($reservations) . " -->";
} catch (PDOException $e) {
    // Log del error
    error_log("Error en la consulta SQL: " . $e->getMessage());
    echo "<!-- Error: " . htmlspecialchars($e->getMessage()) . " -->";
    $reservations = [];
}
?>
<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="ScreenOrientation" content="autoRotate:disabled">
    <title>Erreserba Historikoa</title>
    <style>
        .table-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-height: 70vh;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: Arial, sans-serif;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #ffd700;
            color: black;
        }

        tr:hover {
            background-color: rgba(255, 215, 0, 0.1);
        }

        .no-reservations {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        @media screen and (orientation: portrait) {
            body {
                transform: rotate(-90deg);
                transform-origin: left top;
                width: 100vh;
                height: 100vw;
                position: absolute;
                top: 100%;
                left: 0;
            }
        }
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }
        .background-container {
            position: relative;
            width: 100vw;
            height: 100vh;
        }
        .background-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
        }
        .clickable-area {
            position: absolute;
            cursor: pointer;
            background-color: rgba(255, 255, 255, 0);
        }
        #itzuli {
            background-color: rgba(255, 255, 255, 0) !important;
            transition: none !important;
        }
        #itzuli {
            top: 2%;
            left: 1%;
            width: 15%;
            height: 10%;
        }
    </style>
</head>
<body>
    <div class="background-container">
        <img src="resources/ERRESERBA_HISTORIKOA.png" alt="Erreserba Historikoa Background" class="background-image">
        <div id="itzuli" class="clickable-area" onclick="window.location.href='menu.php'"></div>
        
        <div class="table-container">
            <?php if (count($reservations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bazkidea</th>
                            <th>Erreserba zenbakia</th>
                            <th>Data</th>
                            <th>Mota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['Bazkidea']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Erreserba zenbakia']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Data']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Mota']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-reservations">Ez dago erreserba historikorik</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>