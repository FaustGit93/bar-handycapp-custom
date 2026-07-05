<?php
include 'config.php';
include 'connessione.php';
require_once 'traduzione.php';

$risultato = traduci('Ciao mondo', 'it', 'en');

if ($risultato) {
    echo "✅ Traduzione riuscita: " . $risultato;
} else {
    echo "❌ Traduzione fallita";
    
    // Proviamo a vedere l'errore esatto
    $url = 'https://api.mymemory.translated.net/get?q=' . urlencode('Ciao mondo') . '&langpair=it|en';
    $risposta = @file_get_contents($url);
    
    if ($risposta === false) {
        echo "<br>Impossibile contattare l'API (file_get_contents fallito)";
        $error = error_get_last();
        echo "<br>Errore PHP: " . print_r($error, true);
    } else {
        echo "<br>Risposta ricevuta: " . htmlspecialchars($risposta);
    }
}