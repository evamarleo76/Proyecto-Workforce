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

  
    $sql = "SELECT * FROM empleados 
            ORDER BY 
                CASE 
                    WHEN puesto = 'Admin' THEN 1
                    WHEN puesto = 'RRHH' THEN 2
                    WHEN puesto = 'Supervisor' THEN 3
                    WHEN puesto = 'Responsable' THEN 4
                    ELSE 5
                END,
                CASE 
                    WHEN jornada = 'completa' THEN 1
                    WHEN jornada = '36h' THEN 2
                    WHEN jornada = '30h' THEN 3
                END,
                nombre ASC";

    $stmt = $conn->query($sql);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {

    $path = __DIR__ . '/../../data/empleados.json';
    
    if (file_exists($path)) {
        $empleados = json_decode(file_get_contents($path), true);
    } else {
        $empleados = []; // evitar errores si no existe el JSON
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Empleados</title>

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
            font-weight: normal;
        }

        .menu a:hover {
            text-decoration: underline;
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

        .baja {
            background-color: #ffdddd !important;
            color: #a30000 !important;
            font-weight: bold;
        }

        .vacaciones {
            background-color: #fff7c2 !important;
            color: #8a6d00 !important;
            font-weight: bold;
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

        .btn-editar:hover,
        .btn-borrar:hover,
        .btn-anadir:hover {
            opacity: 0.8;
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
        <a href="../salir.php">Salir</a>
    </div>
</div>

<h1>Gestión de Empleados</h1>

<div class="contenedor">

    <?php if (!$mysql_ok): ?>
        <div class="alert alert-warning" style="padding: 12px; background:#fff3cd; border-left:5px solid #ffca2c; margin-bottom:20px;">
             Modo offline activado — mostrando datos locales
        </div>
    <?php endif; ?>
    
    <?php if ($mysql_ok): ?>
        <a href="insertar.php" class="btn-anadir">Añadir Empleado</a>
    <?php else: ?>
        <span style="color:gray; display:inline-block; margin-bottom:10px;">
            Añadir no disponible en modo offline
        </span>
    <?php endif; ?>
    
    <table border="1">
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Puesto</th>
                <th>Jornada</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($empleados as $fila): ?>
                <tr class="
                    <?php 
                        if ($fila['estado'] == 'Baja') echo 'baja';
                        else if ($fila['estado'] == 'Vacaciones') echo 'vacaciones';
                    ?>
                ">
                    <td><?= $fila['dni']; ?></td>
                    <td><?= $fila['nombre']; ?></td>
                    <td><?= $fila['apellidos']; ?></td>
                    <td><?= $fila['puesto']; ?></td>
                    <td><?= ($fila['jornada'] == 'completa') ? 'Completa' : $fila['jornada']; ?></td>
                    <td><?= $fila['estado']; ?></td>

                    <td>
                        <?php if ($mysql_ok): ?>
                            <a href="editar.php?id=<?= $fila['id']; ?>" class="btn-editar">Editar</a>
                            <a href="eliminar.php?id=<?= $fila['id']; ?>" class="btn-borrar"
                                onclick="return confirm('¿Seguro que deseas eliminar este empleado?');">
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
