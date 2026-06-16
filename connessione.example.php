<?php
// Esempio di configurazione del database. 
// Rinomina questo file in 'connessione.php' e inserisci i tuoi dati.
$host = "localhost";
$user = "IL_TUO_UTENTE";
$password = "LA_TUA_PASSWORD";
$dbname = "ristorante_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>
