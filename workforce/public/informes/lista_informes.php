<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../principal.php");
    exit();
}

$rol = $_SESSION["rol"];

if ($rol != 'Admin' && $rol != 'RRHH' && $rol != 'Supervisor' && $rol != 'Responsable') {
    header("Location: ../secundaria.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=workforce", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysql_ok = true;
} catch (Exception $e) {
    $mysql_ok = false;
}

if (!$mysql_ok) {

    $empleados = json_decode(file_get_contents(__DIR__ . '/../../data/empleados.json'), true);
    $turnos = json_decode(file_get_contents(__DIR__ . '/../../data/turnos.json'), true);
 
    $pathInformes = __DIR__ . '/../../data/informes.json';
    $informes = file_exists($pathInformes) 
                ? json_decode(file_get_contents($pathInformes), true)
                : [];

} else {
   
    $empleados = [];
    $turnos = [];
    $informes = [];
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Informes</title>
    <link rel="stylesheet" href="../css/estilos.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .topbar {
            background-color: #0b3d2e;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .menu a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
        }

        h1 {
            text-align: center;
            margin-top: 25px;
            color: #0b3d2e;
        }

        .contenedor-tarjetas {
            display: flex;
            gap: 25px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
            padding-bottom: 40px;
        }

        .tarjeta {
            background: linear-gradient(135deg, #ffffff 0%, #f4f7f6 100%);
            width: 280px;
            min-height: 220px;
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #0b3d2e;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .tarjeta:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            background: linear-gradient(135deg, #e8fff0 0%, #ffffff 100%);
        }

        .tarjeta a {
            text-decoration: none;
            color: #0b3d2e;
            font-weight: bold;
        }

        .icono {
            width: 70px;
            height: 70px;
            background-color: #3cb371;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .tarjeta h3 {
            margin-bottom: 8px;
            font-size: 20px;
        }

        .tarjeta p {
            margin: 0;
            font-size: 15px;
            color: #333;
        }
    </style>
</head>

<body>

<div class="topbar">
    <div>Workforce</div>
    <div class="menu">
        <a href="../secundaria.php">Inicio</a>
        <a href="../empleados/lista_empleados.php">Empleados</a>
        <a href="../turnos/lista_turnos.php">Turnos</a>
        <a href="lista_informes.php">Informes</a>
        <a href="../salir.php">Salir</a>
    </div>
</div>

<div class="container mt-4">

    <h1>Gestión de Informes</h1>

    <?php if (!$mysql_ok): ?>
        <div class="alert alert-warning" style="padding: 12px; background:#fff3cd; border-left:5px solid #ffca2c; margin-bottom:20px;">
            Modo offline activado — mostrando datos locales
        </div>
    <?php endif; ?>

    <div class="contenedor-tarjetas">

        <div class="tarjeta">
            <a href="informe_diario.php">
                <div class="icono">📅</div>
                <h3>Informe Diario</h3>
                <p>Turnos asignados para el día de hoy</p>
            </a>
        </div>

        <div class="tarjeta">
            <a href="informe_semanal.php">
                <div class="icono">🗓️</div>
                <h3>Informe Semanal</h3>
                <p>Resumen de turnos de la semana actual</p>
            </a>
        </div>

        <div class="tarjeta">
            <a href="informe_mensual.php">
                <div class="icono">📊</div>
                <h3>Informe Mensual</h3>
                <p>Turnos asignados durante el mes</p>
            </a>
        </div>

    </div>

</div> 

</body>
</html>
