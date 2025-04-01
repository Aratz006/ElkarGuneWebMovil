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
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="screen-orientation" content="landscape">
    <meta name="x5-orientation" content="landscape">
    <meta name="full-screen" content="yes">
    <meta name="x5-fullscreen" content="true">
    <meta name="browsermode" content="application">
    <meta name="x5-page-mode" content="app">
    <meta name="msapplication-tap-highlight" content="no">
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
                overflow-x: hidden;
            }
            .background-container {
                transform: scale(0.75);
                transform-origin: center;
            }
        }
        @media screen and (max-width: 480px) {
            .clickable-area {
                transition: none;
            }
            .background-image {
                object-fit: contain;
            }
            .background-container {
                transform: scale(0.7);
                transform-origin: center;
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
            top: 10%;
            left: 20%;
            width: 28%;
            height: 80%;
        }
        #abisuak-ikusi {
            top: 15%;
            left: 70%;
            width: 11%;
            height: 32%;
        }
        #mezuak-ikusi {
            top: 55%;
            left: 70%;
            width: 11%;
            height: 32%;
        }
        #fakturak-ikusi {
            top: 55%;
            left: 56%;
            width: 11%;
            height: 32%;
        }
        #erreserba-historikoa {
            top: 15%;
            left: 56%;
            width: 11%;
            height: 32%;
        }
        #saioa-itxi {
            top: 75%;
            left: 0.4%;
            width: 9.8%;
            height: 8%;
            z-index: 1000;
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