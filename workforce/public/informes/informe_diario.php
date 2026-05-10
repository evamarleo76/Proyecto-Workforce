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
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Informe Diario - Modo Offline</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f7f6;
                padding: 40px;
                text-align: center;
            }
            .msg {
                background: #f8d7da;
                color: #a30000;
                padding: 20px;
                border-radius: 8px;
                display: inline-block;
                font-size: 18px;
                box-shadow: 0 0 8px #ccc;
            }
        </style>
    </head>
    <body>
        <div class="msg">
            <strong>Modo offline activado</strong><br><br>
            No hay datos disponibles para generar el informe diario.<br>
            Activa MySQL para ver el informe real.
        </div>
    </body>
    </html>
    <?php
    exit();
}

$fecha = date("Y-m-d");

$sql = "
    SELECT a.*, e.nombre, e.apellidos,
           t.nombre AS turno_nombre,
           t.tipo   AS turno_tipo
    FROM asignaciones_turnos a
    INNER JOIN empleados e ON a.empleado_id = e.id
    INNER JOIN turnos t    ON a.turno_id   = t.id
    WHERE a.fecha = :fecha
";

$stmt = $conn->prepare($sql);
$stmt->execute([':fecha' => $fecha]);
$asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$franjas = [
    'mañana' => [],
    'tarde'  => []
];

foreach ($asignaciones as $fila) {

    $tipo_raw = strtolower(trim($fila["turno_tipo"] ?? ''));

    if ($tipo_raw === 'mañana' || $tipo_raw === 'manana') {
        $tipo = 'mañana';
    } elseif ($tipo_raw === 'tarde') {
        $tipo = 'tarde';
    } else {
        continue;
    }

    $franjas[$tipo][] = $fila;
}

$minimo_total = 6;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Diario de Cobertura</title>

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
            margin: 20px auto;
        }

        .turno-box {
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 8px #cccccc;
        }

        .ok {
            background-color: #d4f8d4;
            color: #0b3d2e;
            font-weight: bold;
            padding: 6px;
            border-radius: 6px;
        }

        .bajo {
            background-color: #fff3cd;
            color: #8a6d00;
            font-weight: bold;
            padding: 6px;
            border-radius: 6px;
        }

        .descubierto {
            background-color: #f8d7da;
            color: #a30000;
            font-weight: bold;
            padding: 6px;
            border-radius: 6px;
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
        <a href="../logout.php">Salir</a>
    </div>
</div>

<h1>Informe Diario de Cobertura (<?= $fecha ?>)</h1>

<div class="contenedor">

<?php
foreach (['mañana', 'tarde'] as $franja) {

    $empleados = $franjas[$franja];
    $total = count($empleados);

    if ($total == 0) {
        $estado = "descubierto";
        $texto_estado = "DESCUBIERTO ($total/$minimo_total)";
    } elseif ($total < $minimo_total) {
        $estado = "bajo";
        $texto_estado = "BAJO ($total/$minimo_total)";
    } else {
        $estado = "ok";
        $texto_estado = "OK ($total/$minimo_total)";
    }
?>

    <div class="turno-box">
        <h2>Franja de <?= strtoupper($franja) ?></h2>
        <p class="<?= $estado ?>"><strong>Cobertura:</strong> <?= $texto_estado ?></p>

        <?php if ($total > 0): ?>
            <ul>
                <?php foreach ($empleados as $emp): ?>
                    <li><?= $emp["nombre"] . " " . $emp["apellidos"] ?> — <?= $emp["turno_nombre"] ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay empleados asignados a esta franja.</p>
        <?php endif; ?>
    </div>

<?php } ?>

</div>

</body>
</html>
