<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../principal.php");
    exit();
}

$rol = $_SESSION["rol"];

if ($rol != 'Admin' && $rol != 'RRHH' && $rol != 'Supervisor') {
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

if ($mysql_ok) {
    $sql = "SELECT * FROM turnos ORDER BY id ASC";
    $stmt = $conn->query($sql);
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $path = __DIR__ . '/../../data/turnos.json';
    $turnos = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Turnos</title>

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

        .contenedor {
            width: 90%;
            margin: 30px auto;
        }

        .btn-anadir {
            background-color: #7CFF9E;
            color: black;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
            display: inline-block;
        }

        .btn-rotativo {
            background-color: #7CFF9E;
            color: black;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
        }

        .btn-rotativo:hover,
        .btn-anadir:hover {
            opacity: 0.8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th {
            background-color: #2e8b57;
            color: white;
            padding: 10px;
        }

        td {
            padding: 10px;
            text-align: center;
        }

        tbody tr:nth-child(odd) {
            background-color: #e8f5f0;
        }

        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .btn-editar {
            background-color: #2e8b57;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-borrar {
            background-color: #7CFF9E;
            color: black;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
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
        <a href="../salir.php">Salir</a>
    </div>
</div>

<h1>Gestión de Turnos</h1>

<div class="contenedor">

    <?php if (!$mysql_ok): ?>
        <div style="padding: 12px; background:#fff3cd; border-left:5px solid #ffca2c; margin-bottom:20px;">
            Modo offline activado — mostrando datos locales
        </div>
    <?php endif; ?>

    <?php if ($mysql_ok): ?>
        <a href="nuevo_turno.php" class="btn-anadir">Añadir Turno</a>
        <a href="asignar_turno.php" class="btn-rotativo">Asignar Turno Rotativo</a>
    <?php else: ?>
        <span style="color:gray;">Añadir no disponible offline</span>
        <span style="color:gray; margin-left:10px;">Asignación no disponible offline</span>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Tipo</th>
                <th>Jornada</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($turnos as $fila): ?>
                <tr>
                    <td><?= $fila['id']; ?></td>
                    <td><?= $fila['nombre']; ?></td>
                    <td><?= $fila['hora_inicio']; ?></td>
                    <td><?= $fila['hora_fin']; ?></td>
                    <td><?= $fila['tipo']; ?></td>
                    <td><?= $fila['jornada']; ?></td>

                    <td>
                        <?php if ($mysql_ok): ?>
                            <a href="editar_turno.php?id=<?= $fila['id']; ?>" class="btn-editar">Editar</a>
                            <a href="borrar_turno.php?id=<?= $fila['id']; ?>" class="btn-borrar"
                                onclick="return confirm('¿Seguro que deseas eliminar este turno?');">
                                Borrar
                            </a>
                        <?php else: ?>
                            <span style="color:gray;">No disponible offline</span>
                        <?php endif; ?>
                    </td>

               </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

</div>

</body>
</html>

