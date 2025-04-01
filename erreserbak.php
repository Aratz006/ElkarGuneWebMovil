<?php
session_start();
if (!isset($_SESSION['erabiltzailea'])) {
    header('Location: index.html');
    exit();
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
    </style>
</head>
<body>
    <div class="background-container">
        <img src="resources/ERRESERBAK.png" alt="Erreserbak Background" class="background-image">
    </div>
</body>
</html>