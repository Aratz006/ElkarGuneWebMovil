<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    exit();
}

$idBazkidea = $_SESSION['erabiltzailea'];
$mota = isset($_POST['mota']) ? $_POST['mota'] : 2; // Por defecto Bazkaria (0)
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
    $sql = "SELECT esp.idEspazioa, esp.egoera, esp.izena AS 'Espazioa', e.idErreserba, e.idBazkidea, e.mota, 
    e.data,e.komentsalak FROM espazioa esp 
           LEFT JOIN erreserbaelementua ee ON esp.idEspazioa = ee.idEspazioa
           LEFT JOIN erreserba e ON ee.idErreserba = e.idErreserba AND e.data = :data AND e.mota = :mota
           ORDER BY esp.idEspazioa ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data' => $data,
        ':mota' => $mota
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function actualizarColoresEspacios() {
    global $pdo, $idBazkidea, $mota, $erreserbaData;
    
    $sql = "SELECT 
            esp.idEspazioa, 
            esp.egoera,
            e.idBazkidea,
            CASE 
                WHEN esp.egoera = 2 THEN 'negro'
                WHEN e.idErreserba IS NOT NULL AND e.idBazkidea = :idBazkidea THEN 'azul'
                WHEN e.idErreserba IS NOT NULL THEN 'rojo'
                ELSE 'gris'
            END as color
            FROM espazioa esp
            LEFT JOIN erreserbaelementua ee ON esp.idEspazioa = ee.idEspazioa
            LEFT JOIN erreserba e ON ee.idErreserba = e.idErreserba 
                AND e.data = :data AND e.mota = :mota";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data' => $erreserbaData,
        ':mota' => $mota,
        ':idBazkidea' => $idBazkidea
    ]);

    
    $espacios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $resultado = [];
    
    foreach ($espacios as $espacio) {
        $resultado[$espacio['idEspazioa']] = $espacio['color'];
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

        <div class="reservations-table">
            <table>
                <thead> 
                    <tr>
                        <th>Espazioa</th>
                        <th>Egoera</th>
                    </tr>
                </thead>
                <tbody id="reservationsTableBody">
                    <tr>
                        <td colspan="2" class="no-reservations">Ez dago erreserbarik</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="spaces-grid">
            <?php
            $espazioak = getEspazioakEgoera();
            foreach ($espazioak as $espazio) {
                $class = $espazio['egoera'] == 0 ? 'unavailable' : 'available';
                //echo "class= {$class}";
                echo "<div class='space {$class}' data-id='{$espazio['idEspazioa']}' value='{$espazio['idEspazioa']}'>";
                echo $espazio['idEspazioa'];
                echo "</div>";
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
    function erreserbaElemInsert() {
        const selectedSpace = event.target;
        if (!selectedSpace.classList.contains('available') && !selectedSpace.classList.contains('rojo')) {
            document.querySelectorAll('.space').forEach(s => {
                s.classList.remove('selected');
                s.classList.remove('azul');
            });
            selectedSpace.classList.add('selected');
            selectedSpace.classList.add('azul');
        } else if (selectedSpace.classList.contains('unavailable')) {
            alert('Espazio hau mantentze-lanetan dago eta ezin da aukeratu.');
        } else if (selectedSpace.classList.contains('rojo')) {
            alert('Espazio hau beste erabiltzaile batek erreserbatuta dago.');
            s.classList.remove('selected');
            s.classList.remove('rojo');
        }
    }

    function updateSpaces() {
        const date = document.getElementById('reservationDate').value;
        const type = document.getElementById('reservationType').value;
        
        if (!date) {
            alert('Mesedez, aukeratu data bat!');
            return;
        }

        // Actualizar la tabla de reservas
        updateReservationsTable(date, type);

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
            
            // Actualizar los espacios
            document.querySelectorAll('.space').forEach(space => {
                const spaceId = space.dataset.id;
                const spaceData = data.spaces.find(s => s.idEspazioa === spaceId);
                
                if (spaceData) {
                    // Usar el color proporcionado por el servidor
                    space.className = `space ${spaceData.color}`;
                    // Mantener la clase 'selected' si el espacio está seleccionado
                    if (space.classList.contains('selected')) {
                        space.classList.add('selected');
                    }
                }
            });
            
            // Actualizar la tabla de reservas
            updateReservationsTable(date, type);
        });

    function updateReservationsTable(date, type) {
        fetch('get_spaces.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `date=${date}&type=${type}`
        })
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('reservationsTableBody');
            if (!data.spaces || data.spaces.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="2" class="no-reservations">Ez dago erreserbarik</td></tr>';
                return;
            }

            let reservationsHtml = '';
            data.spaces.forEach(space => {
                let estado = '';
                let clase = '';
                
                if (space.egoera === 0) {
                    estado = 'Mantentze-lanetan';
                    clase = 'unavaliable';
                } else if (space.reserved) {
                    if (space.idBazkidea === '<?php echo $idBazkidea; ?>') {
                        estado = 'Zure erreserba';
                        clase = 'azul';
                    } else {
                        estado = 'Erreserbatuta';
                        clase = 'rojo';
                    }
                } else {
                    estado = 'Libre';
                    clase = 'available';
                }

                reservationsHtml += `
                    <tr>
                        <td>${space.idEspazioa}</td>
                        <td class="${clase}">${estado}</td>
                    </tr>`;
            });

            tableBody.innerHTML = reservationsHtml;
        });
        };
    }

    function confirmReservation() {
        const date = document.getElementById('reservationDate').value;
        const type = document.getElementById('reservationType').value;
        const selectedSpace = document.querySelector('.space.selected');

        if (!date || !selectedSpace) {
            alert('Mesedez, aukeratu data eta espazioa!');
            return;
        }

        if (confirm('Erreserba egitera/eguneratzera zoaz. Erreserbaren datuak zuzenak dira?')) {
            // Primero verificamos si ya existe una reserva para este usuario en esta fecha y tipo
            fetch('check_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `date=${date}&type=${type}`
            })
            .then(response => response.json())
            .then(checkData => {
                if (checkData.exists) {
                    // Si existe una reserva, actualizamos la existente
                    return fetch('update_reservation.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `date=${date}&type=${type}&space=${selectedSpace.dataset.id}&idReserva=${checkData.idReserva}`
                    });
                } else {
                    // Si no existe, creamos una nueva
                    return fetch('save_reservation.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `date=${date}&type=${type}&space=${selectedSpace.dataset.id}`
                    });
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    if (confirm('Beste erreserba bat egin nahi duzu?')) {
                        updateSpaces();
                    } else {
                        window.location.href = 'menu.php';
                    }
                } else if (data.error) {
                    alert(data.error);
                    updateSpaces();
                } else {
                    alert('Errorea erreserba egiterakoan.');
                }
            });
    }

    function updateReservationsTable(date, type) {
        fetch('get_spaces.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `date=${date}&type=${type}`
        })
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('reservationsTableBody');
            if (!data.spaces || data.spaces.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="2" class="no-reservations">Ez dago erreserbarik</td></tr>';
                return;
            }

            let reservationsHtml = '';
            data.spaces.forEach(space => {
                let estado = '';
                let clase = '';
                
                if (space.egoera === 2) {
                    estado = 'Mantentze-lanetan';
                    clase = 'negro';
                } else if (space.reserved) {
                    if (space.idBazkidea === '<?php echo $idBazkidea; ?>') {
                        estado = 'Zure erreserba';
                        clase = 'azul';
                    } else {
                        estado = 'Erreserbatuta';
                        clase = 'rojo';
                    }
                } else {
                    estado = 'Libre';
                    clase = 'gris';
                }

                reservationsHtml += `
                    <tr>
                        <td>${space.idEspazioa}</td>
                        <td class="${clase}">${estado}</td>
                    </tr>`;
            });

            tableBody.innerHTML = reservationsHtml;
        });
        }
    }

    document.getElementById('reservationDate').addEventListener('change', updateSpaces);

    let selectedSpaces = [];

    document.querySelectorAll('.space').forEach(space => {
        space.addEventListener('click', function() {
            if (this.classList.contains('rojo')) {
                alert('Espazio hau beste erabiltzaile batengatik erreserbatuta dago.');
                return;
            }
            if (this.classList.contains('unavailable')) {
                alert('Espazio hau aukeraezina dago mantentze lanak direla eta.');
                return;
            }
            
            const spaceId = this.dataset.id;
            const index = selectedSpaces.indexOf(spaceId);
            
            if (index === -1) {
                selectedSpaces.push(spaceId);
                this.classList.add('selected');
                this.classList.add('azul');
            } else {
                selectedSpaces.splice(index, 1);
                this.classList.remove('selected');
                this.classList.remove('azul');
            }
        });
    });

    // Actualizar espacios al cargar la página
    updateSpaces();
    </script>
</body>
</html>

