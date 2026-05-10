<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../principal.php");
    exit();
}

$rol = $_SESSION["rol"];

if ($rol != 'Admin' && $rol != 'RRHH') {
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
    header("Location: lista_empleados.php?offline=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar Empleado</title>

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

        input, select {
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
    </style>
</head>

<body>

<div class="topbar">
    <div>Workforce</div>
    <div class="menu">
        <a href="../secundaria.php">Inicio</a>
        <a href="lista_empleados.php">Empleados</a>
        <a href="../turnos/lista_turnos.php">Turnos</a>
        <a href="../informes/lista_informes.php">Informes</a>
        <a href="../logout.php">Salir</a>
    </div>
</div>

<h1>Insertar Nuevo Empleado</h1>

<div class="form-container">

    <form action="insertar_guardar.php" method="POST">

        <label>DNI</label>
        <input type="text" name="dni" required>

        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Apellidos</label>
        <input type="text" name="apellidos" required>

        <label>Puesto</label>
        <input type="text" name="puesto" required>

        <label>Estado</label>
        <select name="estado" required>
            <option value="Activo">Activo</option>
            <option value="Baja">Baja</option>
            <option value="Vacaciones">Vacaciones</option>
        </select>

        <label>Jornada</label>
        <select name="jornada" required>
            <option value="">Seleccione...</option>
            <option value="completa">Completa</option>
            <option value="36h">36 horas</option>
            <option value="30h">30 horas</option>
        </select>

        <button type="submit" class="btn-guardar">Guardar</button>
        <a href="lista_empleados.php" class="btn-cancelar">Cancelar</a>

    </form>

</div>

</body>
</html>
