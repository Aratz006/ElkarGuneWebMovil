<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Location: index.html');
    exit();
}

$idBazkidea = $_SESSION['erabiltzailea'];
$mota = isset($_POST['mota']) ? $_POST['mota'] : 0;
$erreserbaData = isset($_POST['data']) ? $_POST['data'] : '';
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
            top: 20px;
            left: 20px;
            z-index: 10;
        }
        .back-button {
            opacity: 0;
            width: 50px;
            height: 50px;
            cursor: pointer;
            position: absolute;
        }
        .reservation-type {
            position: absolute;
            top: 30%;
            left: 69%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 32%;
            z-index: 10;
            height: 11%;
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
            top: 15.9%;
            left: 73%;
            transform: translateX(-50%);
            z-index: 10;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .date-input {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1.2em;
            width: 200px;
            cursor: pointer;
        }
        .date-input:hover {
            border-color: #ffd700;
        }
        .date-input:focus {
            outline: none;
            border-color: #ffd700;
            box-shadow: 0 0 5px rgba(255, 215, 0, 0.3);
        }
        .spaces-grid {
            position: relative;
            flex-grow: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 20px;
        }
        .space {
            position: absolute;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.3s;
        }
        .space:nth-child(1) { top: 20%; left: 30%; }
        .space:nth-child(2) { top: 42%; left: 30%; }
        .space:nth-child(3) { top: 65%; left: 30%; }
        .space:nth-child(4) { top: 16%; left: 41.5%; }
        .space:nth-child(5) { top: 32%; left: 41.5%; }
        .space:nth-child(6) { top: 49%; left: 41.5%; }
        .space:nth-child(7) { top: 65%; left: 41.5%; }
        .space:nth-child(8) { top:85%; left: 28.65%; }
        .space:nth-child(9) { top: 85%; left: 35.2%; }
        .space:nth-child(10) { top: 85%; left: 41.8%; }
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
            padding: 15px 30px;
            background-color: #ffd700;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: calc(100% - 40px);
            margin: 20px;
            font-weight: bold;
            font-size: 1.1em;
        }
        #fecha {
            border: none;
            background: transparent;
            font-size: 16px;
            cursor: pointer;
        }
        #tipo-reserva {
            display: none;
        }
        }
        .confirm-button:hover {
            background-color: #e6c200;
        }
        .reservations-table {
            position: absolute;
            top: 45%;
            left: 73%;
            transform: translateX(-50%);
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .reservations-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .reservations-table th, .reservations-table td {
            padding: 8px;
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
                const spaceData = data.spaces.find(s => s.id === spaceId);
                
                if (spaceData) {
                    if (spaceData.egoera !== 0) {
                        space.className = 'space negro';
                    } else if (spaceData.reserved) {
                        space.className = 'space rojo';
                    } else {
                        space.className = 'space gris';
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