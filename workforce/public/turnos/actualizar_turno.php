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

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["id"])) {
    header("Location: lista_turnos.php");
    exit();
}

$id          = intval($_POST["id"]);
$nombre      = $_POST["nombre"];
$hora_inicio = $_POST["hora_inicio"];
$hora_fin    = $_POST["hora_fin"];

$sql = "UPDATE turnos 
        SET nombre = ?, hora_inicio = ?, hora_fin = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$nombre, $hora_inicio, $hora_fin, $id]);

header("Location: lista_turnos.php?actualizado=1");
exit();
?>
