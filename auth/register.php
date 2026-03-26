<?php

session_start();

require_once "../config/db.php";
require_once "../security/csrf.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validateCSRF($_POST['csrf_token'])) {
        die("CSRF detectado");
    }

    $dni = trim($_POST["dni"]);
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password !== $confirm) {
        die("Las contraseñas no coinciden");
    }

    if (strlen($password) < 8) {
        die("La contraseña debe tener al menos 8 caracteres");
    }

    // verificar email existente
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        die("El email ya está registrado");
    }

    // verificar DNI existente
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE dni = ?");
    $stmt->execute([$dni]);

    if ($stmt->fetch()) {
        die("El DNI ya está registrado");
    }

    // hash contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (dni,nombre,email,password_hash,rol)
        VALUES (?,?,?,?,?)
    ");

    $stmt->execute([$dni,$nombre,$email,$hash,$rol]);

    echo "Cuenta creada correctamente";

}