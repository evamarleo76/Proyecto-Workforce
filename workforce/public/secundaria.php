<?php
session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=workforce", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysql_ok = true;
} catch (Exception $e) {
    $mysql_ok = false;
}

if (!isset($_SESSION["usuario"])) {
    header("Location: principal.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$rol = $_SESSION["rol"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Workforce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .barra-superior {
            background-color: #0b3d2e;
            padding: 12px 20px;
        }

        .barra-superior span,
        .barra-superior .navbar-brand {
            color: white;
            font-weight: 800;
            font-size: 18px;
        }

        .btn-salir {
            background-color: #7CFF9E;
            color: black;
            font-weight: 800;
            border: none;
        }

        .btn-salir:hover {
            opacity: 0.85;
        }

        .logo-grande {
            margin-top: 45px;
            width: 480px;
            height: auto;
        }

        .card {
            border-radius: 16px;
            transition: transform 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 240px;
            width: 240px;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            background-color: white;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card img {
            width: 130px;
            height: 130px;
            object-fit: contain;
            margin-bottom: 8px;
        }

        h5 {
            font-weight: 700;
            color: #0b3d2e;
            margin-top: 5px;
        }

        .bloque-tarjetas {
            margin-top: 45px;
        }

        .col-md-4 {
            display: flex;
            justify-content: center;
        }

        a {
            display: block;
        }
    </style>
</head>

<body style="background-color: #ffffff;">

    <nav class="barra-superior d-flex justify-content-between align-items-center">
        <span class="navbar-brand mb-0 h1">Workforce</span>
        <span>Hola, <?= htmlspecialchars($usuario) ?></span>
        <a href="salir.php" class="btn btn-salir btn-sm">Salir</a>
    </nav>

    <div class="container text-center">

        <?php if (!$mysql_ok): ?>
            <div class="alert alert-warning mt-4">
                 Modo offline activado — funcionando con datos locales
            </div>
        <?php endif; ?>

        <img src="img/logo.png" class="logo-grande">

        <div class="row justify-content-center g-5 bloque-tarjetas">

            <div class="col-md-4">
                <a href="empleados/lista_empleados.php" class="text-decoration-none">
                    <div class="card">
                        <img src="img/empleados.png">
                        <h5>Empleados</h5>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="turnos/lista_turnos.php" class="text-decoration-none">
                    <div class="card">
                        <img src="img/turnos.png">
                        <h5>Turnos</h5>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="informes/lista_informes.php" class="text-decoration-none">
                    <div class="card">
                        <img src="img/informes.png">
                        <h5>Informes</h5>
                    </div>
                </a>
            </div>

        </div>

    </div>

</body>
</html>
