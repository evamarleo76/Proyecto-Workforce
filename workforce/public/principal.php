<?php
session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=workforce", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysql_ok = true;
} catch (Exception $e) {
    $mysql_ok = false;
}

if (isset($_SESSION["usuario"])) {
    header("Location: secundaria.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $dni = $_POST["dni"];
    $clave = $_POST["clave"];

    if ($mysql_ok) {

        $sql = "SELECT * FROM usuarios WHERE dni = :dni AND clave = :clave";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':dni' => $dni,
            ':clave' => $clave
        ]);

        if ($stmt->rowCount() == 1) {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION["usuario"] = $fila["nombre"];
            $_SESSION["rol"] = $fila["roles"];
            $_SESSION["offline"] = false;

            header("Location: secundaria.php");
            exit();
        }
    }

    $json_path = __DIR__ . '/../data/usuarios.json';

    if (file_exists($json_path)) {
        $usuarios = json_decode(file_get_contents($json_path), true);

        foreach ($usuarios as $u) {
            if ($u['dni'] === $dni && $u['clave'] === $clave) {

                $_SESSION["usuario"] = $u['nombre'];
                $_SESSION["rol"] = $u['roles'];
                $_SESSION["offline"] = true;

                header("Location: secundaria.php");
                exit();
            }
        }
    }

    $error = "Usuario o clave incorrectos";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Workforce - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #ffffff;">

<div class="container text-center" style="margin-top: 120px;">

    <img src="img/logo.png"
         style="height: 350px; width: auto; display: block; margin: 0 auto; margin-bottom: 40px;">

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mt-3"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off" style="max-width: 300px; margin: 0 auto;">

        <div class="mb-3 text-start">
            <label class="form-label" style="font-size: 14px;">Usuario</label>
            <input type="text" name="dni" class="form-control"
                   autocomplete="off"
                   style="padding: 6px 10px; font-size: 14px;" required>
        </div>

        <div class="mb-3 text-start">
            <label class="form-label" style="font-size: 14px;">Clave</label>
            <input type="password" name="clave" class="form-control"
                   autocomplete="new-password"
                   style="padding: 6px 10px; font-size: 14px;" required>
        </div>

        <button type="submit" class="btn btn-success w-100"
                style="padding: 6px; font-size: 15px;">
            Entrar
        </button>
    </form>
</div>

</body>
</html>
