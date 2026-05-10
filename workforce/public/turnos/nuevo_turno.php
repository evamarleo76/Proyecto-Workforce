<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../principal.php");
    exit();
}

$rol = $_SESSION["rol"];

if ($rol != 'Admin' && $rol != 'RRHH' && $rol != 'Supervisora') {
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
    header("Location: lista_turnos.php?offline=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Turno</title>

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

        .menu a:hover {
            text-decoration: underline;
        }

        h1 {
            text-align: center;
            margin-top: 25px;
            color: #0b3d2e;
        }

        .form-container {
            width: 450px;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #cccccc;
        }

        label {
            font-weight: bold;
            color: #0b3d2e;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 15px;
            border: 1px solid #cccccc;
            border-radius: 6px;
        }

        .btn-guardar {
            background-color: #2e8b57;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn-cancelar {
            background-color: #7CFF9E;
            color: black;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
        }

        .btn-guardar:hover,
        .btn-cancelar:hover {
            opacity: 0.8;
        }
    </style>
</head>

<body>

<div class="topbar">
    <div>Workforce</div>
    <div class="menu">
        <a href="../secundaria.php">Inicio</a>
        <a href="../empleados/lista_empleados.php">Empleados</a>
        <a href="lista_turnos.php">Turnos</a>
        <a href="../informes/lista_informes.php">Informes</a>
        <a href="../logout.php">Salir</a>
    </div>
</div>

<h1>Nuevo Turno</h1>

<div class="form-container">

    <form action="insertar_turno.php" method="POST">

        <label>Nombre del turno:</label>
        <input type="text" name="nombre" required>

        <label>Hora inicio:</label>
        <input type="time" name="hora_inicio" required>

        <label>Hora fin:</label>
        <input type="time" name="hora_fin" required>

        <button type="submit" class="btn-guardar">Guardar</button>
        <a href="lista_turnos.php" class="btn-cancelar">Cancelar</a>

    </form>

</div>

</body>
</html>
