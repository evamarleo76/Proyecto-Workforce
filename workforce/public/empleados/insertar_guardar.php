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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: lista_empleados.php");
    exit();
}

$dni        = $_POST['dni'];
$nombre     = $_POST['nombre'];
$apellidos  = $_POST['apellidos'];
$puesto     = $_POST['puesto'];
$estado     = $_POST['estado'];
$jornada    = $_POST['jornada'];

$sql = "INSERT INTO empleados (dni, nombre, apellidos, puesto, estado, jornada)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([$dni, $nombre, $apellidos, $puesto, $estado, $jornada]);

header("Location: lista_empleados.php?insertado=1");
exit();
?>
