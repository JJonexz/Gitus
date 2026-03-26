<?php
session_start();
require_once "security/csrf.php";

$token = generateCSRF();

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Gitus</title>

<link rel="stylesheet" href="addons/css/style.css">

<style>

.form-error{
color:#ff6b6b;
font-size:13px;
margin-bottom:10px;
}

.form-success{
color:#4caf50;
font-size:13px;
margin-bottom:10px;
}

</style>

</head>

<body>

<div class="auth-wrapper">
<div class="auth-slider">

<!-- LOGIN -->

<div class="auth-card">

<div class="auth-logo">Gitus</div>
<div class="auth-title">Portal Académico</div>

<?php if($error === "login"): ?>
<div class="form-error">
DNI o contraseña incorrectos
</div>
<?php endif; ?>

<form class="auth-form" method="POST" action="auth/login.php">

<input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

<label>DNI</label>
<input
type="text"
name="dni"
maxlength="8"
inputmode="numeric"
pattern="[0-9]{8}"
required
>

<label>Contraseña</label>
<input
type="password"
name="password"
required
>

<button type="submit" class="auth-btn">
Entrar
</button>

</form>

<div class="auth-link">
<button class="auth-switch" onclick="showRegister()">
Crear cuenta
</button>
</div>

</div>


<!-- REGISTER -->

<div class="auth-card">

<div class="auth-logo">Gitus</div>
<div class="auth-title">Crear cuenta</div>

<?php if($success === "registered"): ?>
<div class="form-success">
Cuenta creada correctamente
</div>
<?php endif; ?>

<form class="auth-form" id="registerForm" method="POST" action="auth/register.php">

<input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

<label>DNI</label>
<input
type="text"
name="dni"
maxlength="8"
inputmode="numeric"
pattern="[0-9]{8}"
required
>

<label>Nombre completo</label>
<input
type="text"
name="nombre"
maxlength="80"
required
>

<label>Email</label>
<input
type="email"
name="email"
required
>

<label>Contraseña</label>
<input
type="password"
id="password"
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

<div id="passError" class="form-error"></div>

<button type="submit" class="auth-btn">
Crear cuenta
</button>

</form>

<div class="auth-link">
<button class="auth-switch" onclick="showLogin()">
Ya tengo cuenta
</button>
</div>

</div>

</div>
</div>

<script>

/* cambiar panel */

function showRegister(){
document.querySelector(".auth-wrapper").classList.add("show-register");
}

function showLogin(){
document.querySelector(".auth-wrapper").classList.remove("show-register");
}

/* validar contraseña */

const form = document.getElementById("registerForm");

if(form){

form.addEventListener("submit", function(e){

const pass = document.getElementById("password").value;
const confirm = document.getElementById("confirm_password").value;
const error = document.getElementById("passError");

if(pass !== confirm){

e.preventDefault();
error.textContent = "Las contraseñas no coinciden";

}

});

}

/* limitar DNI */

document.querySelectorAll("input[name='dni']").forEach(input => {

input.addEventListener("input", () => {
input.value = input.value.replace(/[^0-9]/g,'');
});

});

</script>

</body>
</html>