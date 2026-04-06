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
$password = $_POST["password"] ?? '';

// Validación de formato — 7 u 8 dígitos
if (empty($dni) || empty($password) || !preg_match("/^[0-9]{7,8}$/", $dni)) {
    header("Location: ../index.php?error=login");
    exit();
}

$stmt = $pdo->prepare("
    SELECT id, nombre, rol, estado, password_hash
    FROM usuarios
    WHERE dni = ?
    LIMIT 1
");
$stmt->execute([$dni]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Credenciales incorrectas
if (!$user || !password_verify($password, $user["password_hash"])) {
    header("Location: ../index.php?error=login");
    exit();
}

// Cuenta esperando aprobación (profesor / directivo recién registrados)
if ($user["estado"] === "pendiente") {
    header("Location: ../index.php?error=pendiente");
    exit();
}

// Cuenta suspendida por la institución
if ($user["estado"] === "bloqueado") {
    header("Location: ../index.php?error=bloqueado");
    exit();
}

// ── Todo OK — crear sesión ──────────────────────────────────────────────────

session_regenerate_id(true);

$_SESSION["user_id"]   = $user["id"];
$_SESSION["user_name"] = $user["nombre"];
$_SESSION["rol"]       = $user["rol"];

// Registrar último acceso ANTES del switch — posición correcta
// Bug anterior: estaba dentro del case default, se ejecutaba solo en forbidden
$pdo->prepare("
    UPDATE usuarios SET last_login = NOW() WHERE id = ?
")->execute([$user["id"]]);

// Redirección por rol — cada case tiene su break explícito
switch ($user["rol"]) {

    case "alumno":
        // Alumnos: crear y gestionar sus proyectos
        header("Location: ../alumno/dashboard.php");
        break;

    case "profesor":
        // Profesores: supervisar, evaluar y descargar proyectos
        header("Location: ../profesor/dashboard.php");
        break;

    case "directivo":
        // Directivos: seguimiento general + comentarios institucionales
        header("Location: ../directivo/dashboard.php");
        break;

    default:
        // Rol desconocido — no debería ocurrir con la BD actualizada
        session_unset();
        session_destroy();
        header("Location: ../index.php?error=forbidden");
        break; // break explícito aunque sea el último case
}

exit();