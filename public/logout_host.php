<?php
    session_start();
    session_destroy();
    $_SESSION = [];
    header("Location: /tournament-monitor/public/home.php");
?>
