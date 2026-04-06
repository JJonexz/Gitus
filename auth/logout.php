<?php

session_start();
session_unset();
session_destroy();

// Ruta relativa desde /auth/logout.php → raíz
header("Location: ../index.php");
exit();