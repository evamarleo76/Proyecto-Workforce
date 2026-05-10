<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../principal.php");
    exit();
}

$rol = $_SESSION["rol"];

if ($rol != 'Admin' && $rol != 'Supervisor' && $rol != 'Responsable') {
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
        <title>Informe Semanal - Modo Offline</title>
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
            No hay datos disponibles para generar el informe semanal.<br>
            Activa MySQL para ver el informe real.
        </div>
    </body>
    </html>
    <?php
    exit();
}

$inicio_semana = new DateTime("monday this week");
$fin_semana    = new DateTime("saturday this week");

$dias_es = [
    "Monday" => "Lunes",
    "Tuesday" => "Martes",
    "Wednesday" => "Miércoles",
    "Thursday" => "Jueves",
    "Friday" => "Viernes",
    "Saturday" => "Sábado",
    "Sunday" => "Domingo"
];

$fin_semana_mas_un_dia = (clone $fin_semana)->modify("+1 day");
$periodo = new DatePeriod($inicio_semana, new DateInterval("P1D"), $fin_semana_mas_un_dia);

$sql = "
    SELECT 
        a.fecha,
        t.nombre AS turno_nombre,
        t.tipo   AS turno_tipo,
        e.nombre,
        e.apellidos
    FROM asignaciones_turnos a
    INNER JOIN empleados e ON a.empleado_id = e.id
    INNER JOIN turnos t ON a.turno_id = t.id
    WHERE a.fecha BETWEEN :ini AND :fin
    ORDER BY a.fecha, t.tipo
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':ini' => $inicio_semana->format("Y-m-d"),
    ':fin' => $fin_semana->format("Y-m-d")
]);

$asignaciones = [];
while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $asignaciones[$fila["fecha"]][] = $fila;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Semanal</title>

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

        .dia {
            background: white;
            padding: 15px;
            margin: 20px auto;
            width: 90%;
            border-radius: 8px;
        }

        .titulo-dia {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1b5e20;
        }

        .turno {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        .manana { background: #e0f2f1; }
        .tarde { background: #fff9c4; }

        .estado-ok { color: green; font-weight: bold; }
        .estado-bajo { color: orange; font-weight: bold; }
        .estado-descubierto { color: red; font-weight: bold; }
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

<h1>
    Informe Semanal 
    (<?= $inicio_semana->format("d") ?>–
     <?= $fin_semana->format("d") ?> 
     <?= $inicio_semana->format("F Y") ?>)
</h1>

<?php
foreach ($periodo as $dia) {

    $fecha = $dia->format("Y-m-d");
    $nombre_es = $dias_es[$dia->format("l")];

    echo "<div class='dia'>";
    echo "<div class='titulo-dia'>{$nombre_es} " . $dia->format("d/m/Y") . "</div>";

    if (!isset($asignaciones[$fecha])) {
        echo "<p class='estado-descubierto'>No hay asignaciones</p>";
        echo "</div>";
        continue;
    }

    $asignados = $asignaciones[$fecha];

    $manana = array_filter($asignados, fn($a) => $a["turno_tipo"] === "M");
    $tarde  = array_filter($asignados, fn($a) => $a["turno_tipo"] === "T");

    echo "<div class='turno manana'><strong>Mañana:</strong> ";
    $count = count($manana);
    echo $count >= 3 ? "<span class='estado-ok'>OK ($count)</span>" :
         ($count == 2 ? "<span class='estado-bajo'>BAJO ($count)</span>" :
                        "<span class='estado-descubierto'>DESCUBIERTO ($count)</span>");
    if ($count > 0) {
        echo "<ul>";
        foreach ($manana as $emp) echo "<li>{$emp['nombre']} {$emp['apellidos']}</li>";
        echo "</ul>";
    }
    echo "</div>";

    echo "<div class='turno tarde'><strong>Tarde:</strong> ";
    $count = count($tarde);
    echo $count >= 3 ? "<span class='estado-ok'>OK ($count)</span>" :
         ($count == 2 ? "<span class='estado-bajo'>BAJO ($count)</span>" :
                        "<span class='estado-descubierto'>DESCUBIERTO ($count)</span>");
    if ($count > 0) {
        echo "<ul>";
        foreach ($tarde as $emp) echo "<li>{$emp['nombre']} {$emp['apellidos']}</li>";
        echo "</ul>";
    }
    echo "</div>";

    echo "</div>";
}
?>

</body>
</html>
