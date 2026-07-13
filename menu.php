<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

include 'config.php';
include 'connessione.php';
require_once 'traduzione.php';

// Gestiamo il cambio lingua manuale tramite URL
if (isset($_GET['lang']) && in_array($_GET['lang'], LINGUE_SUPPORTATE)) {
    setcookie('lingua_menu', $_GET['lang'], time() + (60 * 60 * 24 * 365), '/');
    $_COOKIE['lingua_menu'] = $_GET['lang'];
}

// Determiniamo la lingua da usare
if (isset($_COOKIE['lingua_menu'])) {
    $lang = $_COOKIE['lingua_menu'];
} else {
    $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
    if (in_array($browser_lang, LINGUE_SUPPORTATE)) {
        $lang = $browser_lang;
    } else {
        $lang = 'en';
    }
}

// Carichiamo il dizionario della lingua corrente (serve per gli allergeni)
include "lang/$lang.php";

$categorie_query = $conn->query("SELECT * FROM categorie WHERE visibile = 1 ORDER BY ordine ASC");
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Bar HandyCapp</title>
    <link rel="stylesheet" href="style/menu.css">
</head>
<body>

<div class="sticky-header">
    <div class="menu-logo">
        <img src="img/logo.png" alt="Logo">
    </div>
    <nav class="sticky-nav">
        <?php
        while ($cat = $categorie_query->fetch_assoc()) {
            $nome_cat = get_traduzione($conn, 'categorie', $cat['id'], 'nome', $lang) ?? $cat['nome'];
            echo "<a href='#cat-" . $cat['id'] . "'>" . htmlspecialchars($nome_cat) . "</a>";
        }
        $categorie_query->data_seek(0);
        ?>
    </nav>
</div>

    <div class="menu-container">
        <?php
        while ($cat = $categorie_query->fetch_assoc()) {
            $categoria_id = $cat['id'];
            $piatti_query = $conn->query("SELECT * FROM piatti WHERE categoria_id = $categoria_id AND disponibile = 1");

            if ($piatti_query->num_rows > 0) {
                $nome_cat = get_traduzione($conn, 'categorie', $categoria_id, 'nome', $lang) ?? $cat['nome'];

                echo "<section class='category-section' id='cat-" . $categoria_id . "'>";
                echo "<h2 class='category-title'>" . htmlspecialchars($nome_cat) . "</h2>";

                while ($piatto = $piatti_query->fetch_assoc()) {
                    $id_piatto_corrente = $piatto['id'];

                    $nome_piatto = get_traduzione($conn, 'piatti', $id_piatto_corrente, 'nome', $lang) ?? $piatto['nome'];
                    $desc_piatto = get_traduzione($conn, 'piatti', $id_piatto_corrente, 'descrizione', $lang) ?? $piatto['descrizione'];

                    $allergeni_piatto_query = $conn->query("
                        SELECT a.nome, a.chiave FROM allergeni a
                        JOIN piatti_allergeni pa ON a.id = pa.allergene_id
                        WHERE pa.piatto_id = $id_piatto_corrente
                    ");
                    $nomi_allergeni = [];
                    while ($al = $allergeni_piatto_query->fetch_assoc()) {
                        $nomi_allergeni[] = $t[$al['chiave']] ?? $al['nome'];
                    }
                    $ha_note = !empty($piatto['note_allergeni']);
                    $ha_allergeni = count($nomi_allergeni) > 0 || $ha_note;

                    echo "<div class='menu-item'>";
                    echo "  <div class='item-main'>";
                    echo "    <span class='item-name'>" . htmlspecialchars($nome_piatto);
                    if ($ha_allergeni) {
                        echo " <a href='#' class='icona-allergeni' onclick=\"document.getElementById('allergeni-$id_piatto_corrente').classList.toggle('aperto'); return false;\">⚠️</a>";
                    }
                    echo "</span>";
                    echo "    <span class='item-price'>€" . number_format($piatto['prezzo'], 2, ',', '.') . "</span>";
                    echo "  </div>";

                    if (!empty($desc_piatto)) {
                        echo "  <p class='item-description'>" . htmlspecialchars($desc_piatto) . "</p>";
                    }

                    if ($ha_allergeni) {
                        echo "  <div class='dettaglio-allergeni' id='allergeni-$id_piatto_corrente'>";
                        echo "    <div class='dettaglio-allergeni-inner'>";
                        if (count($nomi_allergeni) > 0) {
                            echo "      <strong>Allergeni:</strong> " . htmlspecialchars(implode(', ', $nomi_allergeni));
                        }
                        if ($ha_note) {
                            echo "      <p>" . htmlspecialchars($piatto['note_allergeni']) . "</p>";
                        }
                        echo "    </div>";
                        echo "  </div>";
                    }

                    echo "</div>";
                }

                echo "</section>";
            }
        }
        ?>
    </div>


    <!-- Selettore tema floating -->
<!----- <div class="theme-fab">
    <input type="checkbox" id="theme-toggle-menu" class="theme-toggle-input">
    <label for="theme-toggle-menu" class="theme-selected-menu">
        <span class="theme-icon">🖥️</span>
    </label>
    <div class="theme-options-menu">
        <a href="#" data-theme="light">☀️ Light</a>
        <a href="#" data-theme="dark">🌙 Dark</a>
        <a href="#" data-theme="system">🖥️ System</a>
    </div>
</div>

------>

<!-- Selettore lingua floating -->
<div class="lang-fab">

    <!-- Selettore lingua floating -->
    <div class="lang-fab">
        <input type="checkbox" id="lang-toggle-menu" class="lang-toggle-input">
        <label for="lang-toggle-menu" class="lang-selected-menu">
            <img src="https://flagcdn.com/w40/<?php echo $lang === 'it' ? 'it' : ($lang === 'pt' ? 'br' : 'gb'); ?>.png" width="24" height="18">
        </label>
        <div class="lang-options-menu">
            <a href="?lang=it" class="<?php echo $lang === 'it' ? 'attiva' : ''; ?>">
                <img src="https://flagcdn.com/w40/it.png" width="20" height="15"> IT
            </a>
            <a href="?lang=en" class="<?php echo $lang === 'en' ? 'attiva' : ''; ?>">
                <img src="https://flagcdn.com/w40/gb.png" width="20" height="15"> EN
            </a>
            <a href="?lang=pt" class="<?php echo $lang === 'pt' ? 'attiva' : ''; ?>">
                <img src="https://flagcdn.com/w40/br.png" width="20" height="15"> PT
            </a>
        </div>
    </div>

    
    <script src="js/menu.js"></script>

</body>
</html>