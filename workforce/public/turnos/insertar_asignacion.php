<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../principal.php");
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
    header("Location: asignar_turno.php?offline=1");
    exit();
}

$semana = intval($_POST["semana"]);
$tipo_turno = $_POST["tipo_turno"]; 

$turnos_manana = [
    "completa" => 1,
    "36h"      => 5,
    "30h"      => 9
];

$turnos_tarde = [
    "completa" => 2,
    "36h"      => 7,
    "30h"      => 8
];

$turnos = ($tipo_turno === "mañana") ? $turnos_manana : $turnos_tarde;

$sql = "SELECT id, jornada 
        FROM empleados 
        ORDER BY 
            CASE jornada 
                WHEN 'completa' THEN 1
                WHEN '36h' THEN 2
                WHEN '30h' THEN 3
            END,
            id";

$stmt = $conn->query($sql);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grupo_completa = [];
$grupo_36h = [];
$grupo_30h = [];

foreach ($empleados as $e) {
    if ($e['jornada'] === "completa") $grupo_completa[] = $e['id'];
    if ($e['jornada'] === "36h")      $grupo_36h[] = $e['id'];
    if ($e['jornada'] === "30h")      $grupo_30h[] = $e['id'];
}

$iC = $i36 = $i30 = 0;

$insert = $conn->prepare(
    "INSERT INTO asignaciones_turnos (id_empleado, id_turno)
     VALUES (?, ?)"
);

while ($iC < count($grupo_completa) ||
       $i36 < count($grupo_36h) ||
       $i30 < count($grupo_30h)) {

    if ($iC < count($grupo_completa)) {
        $id_empleado = $grupo_completa[$iC++];
        $id_turno = $turnos["completa"];
        $insert->execute([$id_empleado, $id_turno]);
    }

    if ($i36 < count($grupo_36h)) {
        $id_empleado = $grupo_36h[$i36++];
        $id_turno = $turnos["36h"];
        $insert->execute([$id_empleado, $id_turno]);
    }

    if ($i30 < count($grupo_30h)) {
        $id_empleado = $grupo_30h[$i30++];
        $id_turno = $turnos["30h"];
        $insert->execute([$id_empleado, $id_turno]);
    }
}

header("Location: asignar_turno.php?ok=1");
exit();
?>
