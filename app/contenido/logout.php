<?php
    session_start();
    session_unset();
    session_destroy();
    header("Refresh:5; url=../../index.php", true);
    echo "Usuario desconectado. Redirigiendo...";
?>