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
    header("Location: lista_turnos.php?offline=1");
    exit();
}

$semana_actual = date("W");

$sql = "SELECT id, nombre, apellidos, puesto, jornada 
        FROM empleados 
        WHERE estado = 'Activo'
        AND jornada IN ('completa','36h','30h')
        ORDER BY nombre, apellidos";

$stmt = $conn->query($sql);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$completas = [];
$j36 = [];
$j30 = [];

foreach ($empleados as $e) {
    if ($e['jornada'] === 'completa') $completas[] = $e;
    if ($e['jornada'] === '36h')      $j36[] = $e;
    if ($e['jornada'] === '30h')      $j30[] = $e;
}

$bloques = [];

while (!empty($completas) && !empty($j36) && !empty($j30)) {
    $bloques[] = [
        'completa' => array_shift($completas),
        '36h'      => array_shift($j36),
        '30h'      => array_shift($j30)
    ];
}

$sql = "SELECT id, nombre FROM turnos";
$stmt = $conn->query($sql);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$turnos_ids = [
    'manana' => ['completa'=>null,'36h'=>null,'30h'=>null],
    'tarde'  => ['completa'=>null,'36h'=>null,'30h'=>null]
];

foreach ($turnos as $t) {
    switch ($t['nombre']) {
        case 'Mañana Completa': $turnos_ids['manana']['completa'] = $t['id']; break;
        case 'Mañana 36h':      $turnos_ids['manana']['36h']      = $t['id']; break;
        case 'Mañana 30h':      $turnos_ids['manana']['30h']      = $t['id']; break;
        case 'Tarde Completa':  $turnos_ids['tarde']['completa']  = $t['id']; break;
        case 'Tarde 36h':       $turnos_ids['tarde']['36h']       = $t['id']; break;
        case 'Tarde 30h':       $turnos_ids['tarde']['30h']       = $t['id']; break;
    }
}

$semana_par = ($semana_actual % 2 == 0);

$conn->query("DELETE FROM asignaciones_turnos WHERE semana = $semana_actual");

$lunes = date("Y-m-d", strtotime("monday this week"));

foreach ($bloques as $index => $bloque) {

    $franja = $semana_par
        ? (($index % 2 == 0) ? 'manana' : 'tarde')
        : (($index % 2 == 0) ? 'tarde' : 'manana');

    foreach (['completa','36h','30h'] as $j) {

        $id_empleado = $bloque[$j]['id'];
        $id_turno    = $turnos_ids[$franja][$j];

        if (!$id_turno) continue;

        for ($i = 0; $i < 6; $i++) {
            $fecha = date("Y-m-d", strtotime("$lunes +$i days"));

            $sql = "INSERT INTO asignaciones_turnos (empleado_id, turno_id, semana, fecha)
                    VALUES (:emp, :turno, :semana, :fecha)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':emp'    => $id_empleado,
                ':turno'  => $id_turno,
                ':semana' => $semana_actual,
                ':fecha'  => $fecha
            ]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Turno Rotativo</title>
    <style>
        body { font-family: Arial; background:#f4f7f6; margin:0; padding:0; }
        .topbar { background:#0b3d2e; padding:15px 20px; color:white; display:flex; justify-content:space-between; }
        .menu a { color:white; margin-left:20px; text-decoration:none; }
        h1 { text-align:center; margin-top:25px; color:#0b3d2e; }
        .contenedor { width:70%; margin:30px auto; background:white; padding:25px; border-radius:10px; }
        .bloque { background:#eef5f0; padding:10px; margin-bottom:10px; border-radius:6px; }
        .franja { font-weight:bold; color:#0b3d2e; }
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

<h1>Asignación realizada correctamente</h1>

<div class="contenedor">

    <p><strong>Semana:</strong> <?= $semana_actual ?></p>
    <p>Asignación aplicada a lunes–sábado.</p>

    <?php foreach ($bloques as $index => $bloque): ?>
        <?php
            $franja = $semana_par
                ? (($index % 2 == 0) ? 'mañana' : 'tarde')
                : (($index % 2 == 0) ? 'tarde' : 'mañana');
        ?>
        <div class="bloque">
            <p class="franja">Bloque <?= $index+1 ?> → <?= $franja ?></p>
            <ul>
                <li><?= $bloque['completa']['nombre'] ?> <?= $bloque['completa']['apellidos'] ?> — Completa</li>
                <li><?= $bloque['36h']['nombre'] ?> <?= $bloque['36h']['apellidos'] ?> — 36h</li>
                <li><?= $bloque['30h']['nombre'] ?> <?= $bloque['30h']['apellidos'] ?> — 30h</li>
            </ul>
        </div>
    <?php endforeach; ?>

</div>

</body>
</html>
