<?php
/**
 * Funzione per tradurre un testo usando MyMemory API
 * 
 * @param string $testo     Il testo da tradurre
 * @param string $da        Lingua sorgente (es. 'it')
 * @param string $a         Lingua destinazione (es. 'en')
 * @return string|null      Il testo tradotto oppure null in caso di errore
 */
function traduci($testo, $da, $a) {
    if (empty(trim($testo))) return null;
    if ($da === $a) return $testo;

    $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($testo) . '&langpair=' . $da . '|' . $a;

    $risposta = @file_get_contents($url);
    if (!$risposta) return null;

    $json = json_decode($risposta, true);
    if (!$json || $json['responseStatus'] !== 200) return null;

    return $json['responseData']['translatedText'];
}

/**
 * Salva o aggiorna una traduzione nel database
 * 
 * @param mysqli $conn
 * @param string $tabella   'piatti' o 'categorie'
 * @param int    $riga_id   ID del piatto o categoria
 * @param string $campo     'nome' o 'descrizione'
 * @param string $lingua    'en', 'pt' ecc.
 * @param string $testo     Il testo tradotto
 * @param int    $verificata 0=automatica, 1=verificata dal gestore
 */
function salva_traduzione($conn, $tabella, $riga_id, $campo, $lingua, $testo, $verificata = 0) {
    if (empty(trim($testo))) return;

    $stmt = $conn->prepare("
        INSERT INTO traduzioni (tabella, riga_id, campo, lingua, testo, verificata)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE testo = VALUES(testo), verificata = VALUES(verificata), updated_at = NOW()
    ");
    $stmt->bind_param("siissi", $tabella, $riga_id, $campo, $lingua, $testo, $verificata);
    $stmt->execute();
    $stmt->close();
}

/**
 * Recupera una traduzione dal database
 * 
 * @param mysqli $conn
 * @param string $tabella
 * @param int    $riga_id
 * @param string $campo
 * @param string $lingua
 * @return string|null
 */
function get_traduzione($conn, $tabella, $riga_id, $campo, $lingua) {
    $stmt = $conn->prepare("
        SELECT testo FROM traduzioni 
        WHERE tabella = ? AND riga_id = ? AND campo = ? AND lingua = ?
    ");
    $stmt->bind_param("siss", $tabella, $riga_id, $campo, $lingua);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ? $result['testo'] : null;
}

/**
 * Traduce automaticamente un testo in tutte le lingue supportate
 * e salva nel database, saltando la lingua base
 * 
 * @param mysqli $conn
 * @param string $tabella
 * @param int    $riga_id
 * @param string $campo
 * @param string $testo
 */
function traduci_e_salva_tutto($conn, $tabella, $riga_id, $campo, $testo) {
    $lingua_base = LINGUA_BASE;
    $lingue = LINGUE_SUPPORTATE;

    foreach ($lingue as $lingua) {
        if ($lingua === $lingua_base) continue; // Salta la lingua base
        
        $traduzione = traduci($testo, $lingua_base, $lingua);
        if ($traduzione) {
            salva_traduzione($conn, $tabella, $riga_id, $campo, $lingua, $traduzione, 0);
        }
    }
}