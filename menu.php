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
    <title>Menu Nagusia</title>
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
        .clickable-area {
            position: absolute;
            cursor: pointer;
            background-color: rgba(255, 255, 255, 0);
            transition: background-color 0.3s;
        }
        .clickable-area:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        #erreserbak {
            top: 20%;
            left: 15%;
            width: 20%;
            height: 25%;
        }
        #abisuak-ikusi {
            top: 45%;
            left: 15%;
            width: 20%;
            height: 25%;
        }
        #mezuak-ikusi {
            top: 45%;
            left: 40%;
            width: 20%;
            height: 25%;
        }
        #kontsumoa {
            top: 45%;
            left: 65%;
            width: 20%;
            height: 25%;
        }
        #fakturak-ikusi {
            top: 70%;
            left: 15%;
            width: 20%;
            height: 25%;
        }
        #erreserba-historikoa {
            top: 20%;
            left: 40%;
            width: 20%;
            height: 25%;
        }
        #saioa-itxi {
            top: 5%;
            right: 5%;
            width: 10%;
            height: 5%;
        }
    </style>
</head>
<body>
    <div class="background-container">
        <img src="resources/MENUA (1).png" alt="Menu Background" class="background-image">
        
        <div id="erreserbak" class="clickable-area" onclick="window.location.href='erreserbak.php'"></div>
        <div id="erreserba-historikoa" class="clickable-area" onclick="window.location.href='erreserba_historikoa.php'"></div>
        <div id="abisuak-ikusi" class="clickable-area" onclick="window.location.href='abisuak_ikusi.php'"></div>
        <div id="mezuak-ikusi" class="clickable-area" onclick="window.location.href='mezuak_ikusi.php'"></div>
        <div id="kontsumoa" class="clickable-area" onclick="window.location.href='kontsumoa.php'"></div>
        <div id="fakturak-ikusi" class="clickable-area" onclick="window.location.href='fakturak_ikusi.php'"></div>
        <div id="saioa-itxi" class="clickable-area" onclick="window.location.href='logout.php'"></div>
    </div>
</body>
</html>