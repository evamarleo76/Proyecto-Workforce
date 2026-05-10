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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: lista_turnos.php");
    exit();
}

$nombre      = $_POST["nombre"];
$hora_inicio = $_POST["hora_inicio"];
$hora_fin    = $_POST["hora_fin"];

$sql = "INSERT INTO turnos (nombre, hora_inicio, hora_fin)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([$nombre, $hora_inicio, $hora_fin]);

header("Location: lista_turnos.php?insertado=1");
exit();
?>
