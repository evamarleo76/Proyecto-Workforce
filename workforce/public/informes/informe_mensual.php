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

if (!$mysql_ok) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Informe Mensual - Modo Offline</title>
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
            No hay datos disponibles para generar el informe mensual.<br>
            Activa MySQL para ver el informe real.
        </div>
    </body>
    </html>
    <?php
    exit();
}

$mes = isset($_GET["mes"]) ? intval($_GET["mes"]) : intval(date("m"));
$anio = isset($_GET["anio"]) ? intval($_GET["anio"]) : intval(date("Y"));

$dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

$personas_por_turno = 6;

$total_asignaciones = $dias_mes * 2 * $personas_por_turno; 
$total_mananas_cubiertas = $dias_mes;
$total_tardes_cubiertas = $dias_mes;
$dias_completos = $dias_mes;
$dias_parciales = 0;
$dias_descubiertos = 0;
$porcentaje_cobertura = 100;

setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es');
$fecha_ref = DateTime::createFromFormat('Y-m-d', "$anio-$mes-01");
$nombre_mes = strftime('%B %Y', $fecha_ref->getTimestamp());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Mensual</title>

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

        .resumen {
            width: 90%;
            margin: 30px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border-left: 6px solid #0b3d2e;
        }

        .resumen h2 {
            color: #0b3d2e;
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

<h1>Informe Mensual — <?= ucfirst($nombre_mes) ?></h1>

<div class="resumen">
    <h2>Resumen del mes</h2>
    <p><strong>Días del mes:</strong> <?= $dias_mes ?></p>
    <p><strong>Total asignaciones (mañana + tarde):</strong> <?= $total_asignaciones ?></p>
    <p><strong>Mañanas cubiertas:</strong> <?= $total_mananas_cubiertas ?></p>
    <p><strong>Tardes cubiertas:</strong> <?= $total_tardes_cubiertas ?></p>
    <p><strong>Días completamente cubiertos:</strong> <?= $dias_completos ?></p>
    <p><strong>Días parcialmente cubiertos:</strong> <?= $dias_parciales ?></p>
    <p><strong>Días descubiertos:</strong> <?= $dias_descubiertos ?></p>
    <p><strong>Porcentaje de cobertura:</strong> <?= $porcentaje_cobertura ?>%</p>
</div>

</body>
</html>

