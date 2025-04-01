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
    global $pdo, $mota, $erreserbaData;
    $sql = "SELECT id, egoera FROM espazioa";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button {
            padding: 10px 20px;
            background-color: #ffd700;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .reservation-type {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .type-button {
            padding: 10px 20px;
            background-color: #f0f0f0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .type-button.active {
            background-color: #ffd700;
        }
        .date-selector {
            margin-bottom: 20px;
        }
        .date-input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .spaces-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .space {
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
        }
        .space.available {
            background-color: #f0f0f0;
        }
        .space.unavailable {
            background-color: #666;
            color: white;
            cursor: not-allowed;
        }
        .confirm-button {
            padding: 10px 20px;
            background-color: #ffd700;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <button class="back-button" onclick="window.location.href='menu.php'">Itzuli</button>
            <h1>Erreserbak</h1>
        </div>

        <div class="reservation-type">
            <button class="type-button <?php echo $mota == 0 ? 'active' : ''; ?>" onclick="setType(0)">Afaria</button>
            <button class="type-button <?php echo $mota == 1 ? 'active' : ''; ?>" onclick="setType(1)">Bazkaria</button>
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
                echo "<div class='space {$class}' data-id='{$espazio['id']}'>"
                    . "Espazioa " . $espazio['id']
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
            // Update spaces display
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
                    alert('Erreserba ondo gorde da!');
                    window.location.href = 'menu.php';
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