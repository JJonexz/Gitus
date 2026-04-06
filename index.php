<?php
session_start();
require_once "security/csrf.php";

$token   = generateCSRF();
$error   = $_GET['error']   ?? null;
$success = $_GET['success'] ?? null;


// Todos los mensajes del sistema centralizados
$errores = [
    // Autenticación
    'login'     => 'DNI o contraseña incorrectos.',
    'pendiente' => 'Tu cuenta está pendiente de aprobación. La institución te habilitará el acceso.',
    'bloqueado' => 'Tu cuenta fue suspendida. Comunicate con la institución.',
    'forbidden' => 'No tenés permiso para acceder a esa sección.',
    'csrf'      => 'Error de seguridad. Por favor intentá de nuevo.',
    // Registro
    'exists'    => 'El DNI o email ya está registrado en el sistema.',
    'rol'       => 'Seleccioná un rol válido para continuar.',
    'dni'       => 'El DNI debe contener entre 7 y 8 dígitos numéricos.',
    'nombre'    => 'El nombre contiene caracteres no permitidos.',
    'email'     => 'El email ingresado no es válido.',
    'password'  => 'La contraseña debe tener al menos 8 caracteres.',
    'passmatch' => 'Las contraseñas no coinciden.',
];

$mensajeError = $errores[$error] ?? null;

// Errores que vienen del formulario de registro → abrir ese panel automáticamente
$errores_registro = ['exists', 'rol', 'dni', 'nombre', 'email', 'password', 'passmatch'];
$abrir_registro   = in_array($error, $errores_registro, true);
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gitus — Portal Académico</title>

<link rel="stylesheet" href="addons/css/style.css">

<style>
.form-error {
    color: #dc2626;
    font-size: 13px;
    margin-bottom: 12px;
    padding: 9px 12px;
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: 6px;
}
.form-success {
    color: #166534;
    font-size: 13px;
    margin-bottom: 12px;
    padding: 9px 12px;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 6px;
}

/* Select con el mismo estilo que los inputs del CSS institucional */
.auth-form select {
    width: 100%;
    padding: 11px 13px;
    border-radius: 6px;
    border: 1.5px solid #d1d9e6;
    background: #f8fafc;
    color: #1a2744;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    cursor: pointer;
    transition: border-color .18s, box-shadow .18s;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%231a2744' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 13px center;
}
.auth-form select:focus {
    border-color: #1a2744;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(26,39,68,.1);
}
.auth-form select option[value=""] {
    color: #9ca3af;
}
</style>

</head>
<body>

<div class="auth-wrapper <?php echo $abrir_registro ? 'show-register' : ''; ?>">
<div class="auth-slider">

<!-- ===================== LOGIN ===================== -->
<div class="auth-card">

    <div class="auth-logo">Gitus</div>
    <div class="auth-title">Portal Académico</div>

    <?php if ($mensajeError && !$abrir_registro): ?>
        <div class="form-error"><?php echo htmlspecialchars($mensajeError); ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST" action="auth/login.php">

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token); ?>">

        <label>DNI</label>
        <input
            type="text"
            name="dni"
            maxlength="8"
            inputmode="numeric"
            pattern="[0-9]{7,8}"
            placeholder="Sin puntos ni espacios"
            required
        >

        <label>Contraseña</label>
        <input
            type="password"
            name="password"
            required
        >

        <button type="submit" class="auth-btn">Entrar</button>

    </form>

    <div class="auth-link">
        <button class="auth-switch" onclick="showRegister()">Crear cuenta</button>
    </div>

</div><!-- /auth-card login -->

<!-- ===================== REGISTER ===================== -->
<div class="auth-card">

    <div class="auth-logo">Gitus</div>
    <div class="auth-title">Crear cuenta</div>

    <?php if ($success === 'registered'): ?>
        <div class="form-success">¡Cuenta creada! Ya podés iniciar sesión.</div>

    <?php elseif ($success === 'pendiente'): ?>
        <div class="form-success">
            Solicitud enviada. La institución habilitará tu acceso.
        </div>

    <?php elseif ($mensajeError && $abrir_registro): ?>
        <div class="form-error"><?php echo htmlspecialchars($mensajeError); ?></div>
    <?php endif; ?>

    <form class="auth-form" id="registerForm" method="POST" action="auth/register.php">

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token); ?>">

        <!-- ROL — campo obligatorio, define estado y dashboard de destino -->
        <label>Rol</label>
        <select name="rol" required>
            <option value="" disabled selected>Seleccioná tu rol</option>
            <option value="alumno">Alumno</option>
            <option value="profesor">Profesor</option>
            <option value="directivo">Directivo</option>
        </select>

        <label>DNI</label>
        <input
            type="text"
            name="dni"
            maxlength="8"
            inputmode="numeric"
            pattern="[0-9]{7,8}"
            placeholder="Sin puntos ni espacios"
            required
        >

        <label>Nombre completo</label>
        <input
            type="text"
            name="nombre"
            maxlength="80"
            required
        >

        <label>Email institucional</label>
        <input
            type="email"
            name="email"
            required
        >

        <label>Contraseña</label>
        <input
            type="password"
            id="reg_password"
            name="password"
            required
        >

        <label>Confirmar contraseña</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            required
        >

        <div id="passError" class="form-error" style="display:none;"></div>

        <button type="submit" class="auth-btn">Crear cuenta</button>

    </form>

    <div class="auth-link">
        <button class="auth-switch" onclick="showLogin()">Ya tengo cuenta</button>
    </div>

</div><!-- /auth-card register -->

</div><!-- /auth-slider -->
</div><!-- /auth-wrapper -->

<script>

function showRegister() {
    document.querySelector(".auth-wrapper").classList.add("show-register");
}
function showLogin() {
    document.querySelector(".auth-wrapper").classList.remove("show-register");
}

// Validación de contraseñas del lado del cliente
const registerForm = document.getElementById("registerForm");
if (registerForm) {
    registerForm.addEventListener("submit", function(e) {
        const pass    = document.getElementById("reg_password").value;
        const confirm = document.getElementById("confirm_password").value;
        const errEl   = document.getElementById("passError");

        if (pass !== confirm) {
            e.preventDefault();
            errEl.textContent   = "Las contraseñas no coinciden.";
            errEl.style.display = "block";
        } else {
            errEl.style.display = "none";
        }
    });
}

// Solo dígitos en campos DNI
document.querySelectorAll("input[name='dni']").forEach(input => {
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^0-9]/g, '');
    });
});

</script>

</body>
</html>