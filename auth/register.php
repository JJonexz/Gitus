<?php

session_start();

require_once "../config/db.php";
require_once "../security/csrf.php";

// Solo acepta POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

if (!validateCSRF($_POST['csrf_token'] ?? '')) {
    header("Location: ../index.php?error=csrf");
    exit();
}

$dni      = trim($_POST["dni"] ?? '');
$nombre   = trim($_POST["nombre"] ?? '');
$email    = trim($_POST["email"] ?? '');
$password = $_POST["password"] ?? '';
$confirm  = $_POST["confirm_password"] ?? '';
$rol      = trim($_POST["rol"] ?? '');

// Roles válidos según el brief del cliente
$roles_validos = ['alumno', 'profesor', 'directivo'];

if (!in_array($rol, $roles_validos, true)) {
    header("Location: ../index.php?error=rol");
    exit();
}

// Validar DNI — 7 u 8 dígitos (misma regla que login.php)
if (!preg_match("/^[0-9]{7,8}$/", $dni)) {
    header("Location: ../index.php?error=dni");
    exit();
}

// Validar nombre — letras, espacios y caracteres del español
if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,60}$/u", $nombre)) {
    header("Location: ../index.php?error=nombre");
    exit();
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../index.php?error=email");
    exit();
}

// Validar contraseña
if (strlen($password) < 8) {
    header("Location: ../index.php?error=password");
    exit();
}

if ($password !== $confirm) {
    header("Location: ../index.php?error=passmatch");
    exit();
}

// Verificar duplicados en una sola consulta
$stmt = $pdo->prepare("
    SELECT id FROM usuarios
    WHERE email = ? OR dni = ?
    LIMIT 1
");
$stmt->execute([$email, $dni]);

if ($stmt->fetch()) {
    header("Location: ../index.php?error=exists");
    exit();
}

// Estado según rol:
// - alumno    → activo de inmediato (se autogestiona)
// - profesor  → pendiente (debe ser aprobado por la institución)
// - directivo → pendiente (debe ser aprobado por la institución)
$estado = ($rol === 'alumno') ? 'activo' : 'pendiente';

$hash = password_hash($password, PASSWORD_DEFAULT);

// ✅ Versión correcta — toma el rol del formulario
$stmt = $pdo->prepare("
    INSERT INTO usuarios (dni, nombre, email, password_hash, rol, estado)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$dni, $nombre, $email, $hash, $rol, $estado]);

// Mensaje diferenciado: alumno entra directo, los demás esperan aprobación
if ($estado === 'pendiente') {
    header("Location: ../index.php?success=pendiente");
} else {
    header("Location: ../index.php?success=registered");
}

exit();