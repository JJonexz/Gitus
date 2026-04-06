<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sin sesión activa → login
if (empty($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit();
}

/**
 * Verificar que el usuario tenga el rol requerido.
 *
 * Uso en cada página protegida:
 *
 *   require_once "../includes/auth_check.php";
 *
 *   require_auth('alumno');                      // solo alumnos
 *   require_auth('profesor');                    // solo profesores
 *   require_auth('directivo');                   // solo directivos
 *   require_auth(['profesor', 'directivo']);      // profesores o directivos
 *   require_auth();                              // cualquier rol autenticado
 *
 * Roles del sistema: alumno | profesor | directivo
 *
 * @param string|array|null $rol  Rol/roles permitidos. Null = solo verifica sesión.
 */
function require_auth(string|array $rol = null): void {

    // Doble chequeo por si se invoca la función directamente sin el include
    if (empty($_SESSION['user_id'])) {
        header("Location: /index.php");
        exit();
    }

    // Sin restricción de rol — solo verifica que haya sesión
    if ($rol === null) {
        return;
    }

    $rol_usuario   = $_SESSION['rol'] ?? '';
    $roles_permit  = is_array($rol) ? $rol : [$rol];

    if (!in_array($rol_usuario, $roles_permit, true)) {
        header("Location: /index.php?error=forbidden");
        exit();
    }
}