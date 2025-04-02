<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Location: index.html');
    exit();
}

$idBazkidea = $_SESSION['erabiltzailea'];
$mota = isset($_POST['mota']) ? $_POST['mota'] : 0; // Por defecto Bazkaria (0)
$erreserbaData = isset($_POST['data']) ? $_POST['data'] : date('Y-m-d'); // Fecha actual por defecto
$gaur = date('Y-m-d');
$fechaLimite = date('Y-m-d', strtotime('+2 months'));

function getEspazioakEgoera() {
    global $pdo;
    $sql = "SELECT idEspazioa, egoera FROM espazioa";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getErreserbak($data, $mota) {
    global $pdo;
    $sql = "SELECT e.idEspazioa, er.idBazkidea, er.data, er.mota 
            FROM espazioa e 
            JOIN erreserbaelementua ee ON e.idEspazioa = ee.idEspazioa 
            JOIN erreserba er ON ee.idErreserba = er.idErreserba 
            WHERE er.data = :data AND er.mota = :mota";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data' => $data,
        ':mota' => $mota
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function actualizarColoresEspacios() {
    global $pdo, $idBazkidea, $mota, $erreserbaData;
    
    // Obtener estado base de todos los espacios
    $espacios = getEspazioakEgoera();
    $reservas = getErreserbak($erreserbaData, $mota);
    
    $resultado = [];
    foreach ($espacios as $espacio) {
        $idEspacio = $espacio['idEspazioa'];
        $estado = $espacio['egoera'];
        
        if ($estado != 0) {
            $resultado[$idEspacio] = 'negro'; // No disponible
        } else {
            $resultado[$idEspacio] = 'gris'; // Disponible por defecto
        }
        
        // Comprobar si está reservado
        foreach ($reservas as $reserva) {
            if ($reserva['idEspazioa'] == $idEspacio) {
                if ($reserva['idBazkidea'] == $idBazkidea) {
                    $resultado[$idEspacio] = 'azul'; // Reservado por el usuario actual
                } else {
                    $resultado[$idEspacio] = 'rojo'; // Reservado por otro usuario
                }
                break;
            }
        }
    }
    
    return $resultado;
}
?>
<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="ScreenOrientation" content="autoRotate:disabled">
    <title>Erreserbak</title>
    <style>
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
            font-family: Arial, sans-serif;
        }
        body {
            background-image: url('resources/ERRESERBAK.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        .container {
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .header {
            position: absolute;
            top: 2vh;
            left: 2vh;
            z-index: 10;
        }
        .back-button {
            opacity: 0;
            width: 8vh;
            height: 8vh;
            cursor: pointer;
            position: absolute;
        }
        .reservation-type {
            position: absolute;
            top: 30vh;
            left: 69vw;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 32vw;
            z-index: 10;
            height: 11vh;
        }
        .type-button {
            opacity: 0;
            width: 45%;
            height: 80%;
            cursor: pointer;
            position: absolute;
        }
        .type-button:first-child {
            position: absolute;
            left: 10%;
            top: 50%;
        }
        .type-button:last-child {
            position: absolute;
            left: 53.5%;
            top: 10%;
        }
        .date-selector {
            position: absolute;
            top: 15.9vh;
            left: 73vw;
            transform: translateX(-50%);
            z-index: 10;
            padding: 1vh;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 25vw;
            max-width: 300px;
        }
        .date-input {
            padding: 1vh;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 2.5vh;
            width: 100%;
            cursor: pointer;
            box-sizing: border-box;
        }
        .spaces-grid {
            position: relative;
            flex-grow: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 1vh;
            padding: 2vh;
        }
        .space {
            position: absolute;
            width: 10vh;
            height: 10vh;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.3s;
            font-size: 1.8vh;
        }
        .space:nth-child(1) { top: 20vh; left: 30vw; }
        .space:nth-child(2) { top: 42vh; left: 30vw; }
        .space:nth-child(3) { top: 65vh; left: 30vw; }
        .space:nth-child(4) { top: 16vh; left: 41.5vw; }
        .space:nth-child(5) { top: 32vh; left: 41.5vw; }
        .space:nth-child(6) { top: 49vh; left: 41.5vw; }
        .space:nth-child(7) { top: 65vh; left: 41.5vw; }
        .space:nth-child(8) { top: 85vh; left: 28.65vw; }
        .space:nth-child(9) { top: 85vh; left: 35.2vw; }
        .space:nth-child(10) { top: 85vh; left: 41.8vw; }
        .space.gris {
            background-color: rgba(0, 0, 0, 0.2);
            color: rgb(0, 0, 0);
            cursor: pointer;
        }
        .space.negro {
            background-color: rgba(0, 0, 0, 1);
            color: white;
            cursor: not-allowed;
        }
        .space.rojo {
            background-color: rgba(255, 0, 0, 0.2);
            color: rgb(255, 0, 0);
            cursor: not-allowed;
        }
        .space.azul {
            background-color: rgba(0, 20, 255, 0.2);
            color: rgb(0, 20, 255);
            cursor: pointer;
        }
        .confirm-button {
            padding: 2vh 4vw;
            background-color: #ffd700;
            border: none;
            border-radius: 1vh;
            cursor: pointer;
            width: calc(100% - 4vh);
            margin: 2vh;
            font-weight: bold;
            font-size: 2.5vh;
            position: fixed;
            bottom: 2vh;
            left: 0;
            z-index: 100;
        }
        .reservations-table {
            position: absolute;
            top: 45vh;
            left: 73vw;
            transform: translateX(-50%);
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 1vh;
            padding: 2vh;
            box-shadow: 0 0.5vh 2vh rgba(0, 0, 0, 0.1);
            width: 40vw;
            max-height: 40vh;
            overflow-y: auto;
        }
        .reservations-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1vh;
            font-size: 1.8vh;
        }
        .reservations-table th, .reservations-table td {
            padding: 1vh;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .reservations-table th {
            background-color: #ffd700;
            color: black;
        }
        .reservations-table tr:hover {
            background-color: rgba(255, 215, 0, 0.1);
        }
        .no-reservations {
            text-align: center;
            padding: 15px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="back-button" onclick="window.location.href='menu.php'"></div>
        </div>

        <div class="reservation-type">
            <input type="hidden" id="reservationType" value="<?php echo $mota; ?>">
            <div class="type-button <?php echo $mota == 0 ? 'active' : ''; ?>" onclick="setType(0)" data-type="0"></div>
            <div class="type-button <?php echo $mota == 1 ? 'active' : ''; ?>" onclick="setType(1)" data-type="1"></div>
        </div>

        <div class="date-selector">
            <input type="date" class="date-input" id="reservationDate" 
                   min="<?php echo $gaur; ?>" 
                   max="<?php echo $fechaLimite; ?>" 
                   value="<?php echo $erreserbaData; ?>">
        </div>

        <div class="spaces-grid">
            <?php
            $espazioak = getEspazioakEgoera();
            foreach ($espazioak as $espazio) {
                $class = $espazio['egoera'] == 0 ? 'available' : 'unavailable';
                echo "<div class='space {$class}' data-id='{$espazio['idEspazioa']}'>"
                    . "Espazioa " . $espazio['idEspazioa']
                    . "</div>";
            }
            ?>
        </div>

        <button class="confirm-button" onclick="confirmReservation()">Erreserbatu</button>
    </div>

    <script>
    function setType(type) {
        document.getElementById('reservationType').value = type;
        document.querySelectorAll('.type-button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type == type);
        });
        updateSpaces();
    }

    function updateSpaces() {
        const date = document.getElementById('reservationDate').value;
        const type = document.getElementById('reservationType').value;
        
        if (!date) {
            alert('Mesedez, aukeratu data bat!');
            return;
        }

        fetch('get_spaces.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `date=${date}&type=${type}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            document.querySelectorAll('.space').forEach(space => {
                const spaceId = space.dataset.id;
                const spaceData = data.spaces.find(s => s.idEspazioa === spaceId);
                
                if (spaceData) {
                    if (spaceData.egoera === 2) { // Mantentze-lanetan
                        space.className = 'space negro';
                    } else if (spaceData.reserved) {
                        if (spaceData.idBazkidea === '<?php echo $idBazkidea; ?>') {
                            space.className = 'space azul'; // Reservado por el usuario actual
                        } else {
                            space.className = 'space rojo'; // Reservado por otro usuario
                        }
                    } else {
                        space.className = 'space gris'; // Disponible
                    }
                }
            });
        });
    }

    function confirmReservation() {
        const date = document.getElementById('reservationDate').value;
        const type = document.getElementById('reservationType').value;
        const selectedSpace = document.querySelector('.space.selected');

        if (!date || !selectedSpace) {
            alert('Mesedez, aukeratu data eta espazioa!');
            return;
        }

        if (confirm('Ziur zaude erreserba egin nahi duzula?')) {
            fetch('save_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `date=${date}&type=${type}&space=${selectedSpace.dataset.id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'menu.php';
                } else if (data.error) {
                    alert(data.error);
                    updateSpaces();
                } else {
                    alert('Errorea erreserba egiterakoan.');
                }
            });
        }
    }

    document.getElementById('reservationDate').addEventListener('change', updateSpaces);

    document.querySelectorAll('.space').forEach(space => {
        space.addEventListener('click', function() {
            if (this.classList.contains('available')) {
                document.querySelectorAll('.space').forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
            }
        });
    });
    </script>
</body>
</html>