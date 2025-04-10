<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        die('Erabiltzailea eta pasahitza bete behar duzu.');
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die('Erabiltzailea eta pasahitza bete behar duzu.');
    }

    try {
        $stmt = $pdo->prepare("SELECT idBazkidea FROM bazkidea WHERE erabiltzailea = ? AND pasahitza = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['erab'] = $username;
            $_SESSION['erabiltzailea'] = $user['idBazkidea'];
            header('Location: menu.php');
            exit();
        } else {
            echo '<script>alert("Erabiltzailea edo pasahitza okerrak."); window.location.href = "index.html";</script>';
        }
    } catch(PDOException $e) {
        die("Kontsultan errore bat egon da: " . $e->getMessage());
    }
} else {
    header('Location: index.html');
    exit();
}
?>