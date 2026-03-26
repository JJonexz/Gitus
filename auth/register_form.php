<?php

session_start();

require_once "../config/db.php";
require_once "../security/csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

if (!validateCSRF($_POST['csrf_token'])) {
    die("CSRF detectado");
}
/* limpiar datos */

$dni = trim($_POST["dni"]);
$nombre = trim($_POST["nombre"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];
$confirm = $_POST["confirm_password"];

/* validaciones */

if(!preg_match("/^[0-9]{8}$/",$dni)){
    header("Location: ../index.php?error=dni");
    exit();
}

if(strlen($nombre) < 3){
    header("Location: ../index.php?error=nombre");
    exit();
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    header("Location: ../index.php?error=email");
    exit();
}

if(strlen($password) < 8){
    header("Location: ../index.php?error=password");
    exit();
}

if($password !== $confirm){
    header("Location: ../index.php?error=passmatch");
    exit();
}

/* verificar usuario existente */

$stmt = $pdo->prepare("
SELECT id FROM usuarios 
WHERE email = ? OR dni = ?
LIMIT 1
");

$stmt->execute([$email,$dni]);

if($stmt->fetch()){
    header("Location: ../index.php?error=exists");
    exit();
}

/* hash seguro */

$hash = password_hash($password, PASSWORD_DEFAULT);

/* rol por defecto */

$rol = "usuario";

/* insertar usuario */

$stmt = $pdo->prepare("
INSERT INTO usuarios
(dni,nombre,email,password_hash,rol)
VALUES (?,?,?,?,?)
");

$stmt->execute([
$dni,
$nombre,
$email,
$hash,
$rol
]);

/* redireccion */

header("Location: ../index.php?success=registered");
exit();