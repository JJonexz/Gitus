<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}

?>

<h1>Bienvenido a Gitus</h1>

<p>Rol: <?php echo $_SESSION["rol"]; ?></p>

<a href="../logout.php">Cerrar sesión</a>