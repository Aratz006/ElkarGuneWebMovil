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
    <meta http-equiv="ScreenOrientation" content="landscape">
    <title>Erreserbak</title>
    <link rel="stylesheet" href="style.css">
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
                    . $espazio['idEspazioa']
                    . "</div>";
            }
            ?>
        </div>

        <button class="confirm-button" onclick="confirmReservation()"></button>
        <button class="cancel-button" onclick="window.location.href='menu.php'"></button>
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