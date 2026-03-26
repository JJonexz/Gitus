<?php

session_start();

require_once "../config/db.php";
require_once "../security/csrf.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validateCSRF($_POST['csrf_token'])) {
        die("CSRF detectado");
    }

    $dni = trim($_POST["dni"]);
    $password = $_POST["password"];

    /* validación básica */

    if(empty($dni) || empty($password)){
        $errors[] = "Debes completar todos los campos";
    }

    if(!preg_match("/^[0-9]{8}$/", $dni)){
        $errors[] = "DNI inválido";
    }

    if(empty($errors)){

        $stmt = $pdo->prepare("
            SELECT id, dni, nombre, rol, password_hash
            FROM usuarios
            WHERE dni = ?
            LIMIT 1
        ");

        $stmt->execute([$dni]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user["password_hash"])){

            session_regenerate_id(true);

            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["user_name"] = $user["nombre"];
            $_SESSION["rol"]       = $user["rol"];

            /* redirección */

            switch($user["rol"]){

                case "admin":
                    header("Location: ../admin/dashboard.php");
                    break;

                case "usuario":
                    header("Location: ../page.php");
                    break;

                default:
                    header("Location: ../page.php");

            }

            exit();

        } else {

            header("Location: ../index.php?error=login");
            exit();

        }

    }

}