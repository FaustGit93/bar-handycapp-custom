<?php
session_start();
// Distruggiamo tutte le variabili di sessione attiva
session_destroy();
// Riportiamo l'utente al login
header("Location: login.php");
exit();
?>
