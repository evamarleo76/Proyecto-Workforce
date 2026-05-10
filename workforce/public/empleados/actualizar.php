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

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["id"])) {
    header("Location: lista_empleados.php");
    exit();
}

$id         = intval($_POST["id"]);
$dni        = $_POST["dni"];
$nombre     = $_POST["nombre"];
$apellidos  = $_POST["apellidos"];
$puesto     = $_POST["puesto"];
$jornada    = $_POST["jornada"];
$estado     = $_POST["estado"];

$sql = "UPDATE empleados 
        SET dni = ?, nombre = ?, apellidos = ?, puesto = ?, jornada = ?, estado = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$dni, $nombre, $apellidos, $puesto, $jornada, $estado, $id]);

header("Location: lista_empleados.php?ok=1");
exit();
?>
