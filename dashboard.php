<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['erabiltzailea'])) {
    header('Location: index.html');
    exit();
}

$erabiltzailea = $_SESSION['erabiltzailea'];

try {
    $stmt = $pdo->prepare("SELECT * FROM bazkidea WHERE erabiltzailea = ?");
    $stmt->execute([$erabiltzailea]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    die("Kontsultan errore bat egon da: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Elkargune</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40;
        }
        .welcome-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .user-info {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Elkargune</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-light">Ongi etorri, <?php echo htmlspecialchars($erabiltzailea); ?></span>
                <a href="logout.php" class="btn btn-outline-light ms-3">Saioa Itxi</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="welcome-section">
            <h2>Ongi etorri zure Kontrol-panelera</h2>
            <p>Hemen zure kontuaren informazioa eta aukerak kudeatu ditzakezu.</p>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Zure Informazioa</h5>
                        <div class="user-info">
                            <p><strong>Erabiltzailea:</strong> <?php echo htmlspecialchars($user['erabiltzailea']); ?></p>
                            <p><strong>Izena:</strong> <?php echo htmlspecialchars($user['izena'] ?? 'Ez dago eskuragarri'); ?></p>
                            <p><strong>Abizena:</strong> <?php echo htmlspecialchars($user['abizena'] ?? 'Ez dago eskuragarri'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>