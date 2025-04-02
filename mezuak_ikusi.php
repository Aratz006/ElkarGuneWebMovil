<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Location: index.html');
    exit();
}

try {
    $erabiltzailea = $_SESSION['erabiltzailea'];
    $sql = "SELECT m.data AS 'Data', m.mezua AS 'Mezua' 
           FROM mezuak m 
           INNER JOIN bazkidea b ON m.idBazkidea = b.idBazkidea 
           WHERE b.erabiltzailea = :erabiltzailea 
           ORDER BY m.data DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':erabiltzailea', $erabiltzailea);
    $stmt->execute();
    $mezuak = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error en la consulta SQL: " . $e->getMessage());
    $mezuak = [];
}
?>
<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="ScreenOrientation" content="autoRotate:disabled">
    <title>Mezuak Ikusi</title>
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

        .no-mezuak {
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
        <img src="resources/MEZUAK_IKUSI.png" alt="Mezuak Background" class="background-image">
        <div id="itzuli" class="clickable-area" onclick="window.location.href='menu.php'"></div>
        
        <div class="table-container">
            <?php if (count($mezuak) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Mezua</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mezuak as $mezua): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mezua['Data']); ?></td>
                                <td><?php echo htmlspecialchars($mezua['Mezua']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-mezuak">Ez dago mezurik</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>