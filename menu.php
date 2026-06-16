<?php
// Includiamo il file di connessione al database
include 'connessione.php';

// 1. Prendiamo tutte le categorie dal database in base all'ordine stabilito
$categorie_query = $conn->query("SELECT * FROM categorie ORDER BY ordine ASC");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Bar HandyCapp</title>
    <style>
        /* BASE MOBILE-FIRST CSS */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #111; color: #fff; }
        
        /* 1. BARRA DI NAVIGAZIONE STICKY (Orizzontale e scorrevole col pollice) */
        .sticky-nav { position: sticky; top: 0; background-color: #222; display: flex; overflow-x: auto; white-space: nowrap; padding: 10px; border-bottom: 1px solid #333; z-index: 1000; }
        .sticky-nav a { color: #ccc; text-decoration: none; padding: 8px 16px; font-weight: bold; font-size: 14px; display: inline-block; }
        .sticky-nav a:hover { color: #fff; }

        /* 2. STRUTTURA DEL LAYOUT DEI PIATTI */
        .menu-container { padding: 20px; max-width: 600px; margin: 0 auto; }
        .category-section { margin-bottom: 40px; scroll-margin-top: 60px; } /* Evita che la barra sticky copra il titolo quando ci clicchi */
        .category-title { font-size: 24px; color: #ffc107; border-bottom: 2px solid #ffc107; padding-bottom: 5px; margin-bottom: 20px; }
        
        /* 3. IL SINGOLO PIATTO (Mobile-First: Nome e prezzo sulla stessa riga) */
        .menu-item { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #333; }
        .item-main { display: flex; justify-content: space-between; align-items: baseline; font-size: 18px; font-weight: bold; }
        .item-price { color: #ffc107; margin-left: 10px; }
        .item-description { font-size: 14px; color: #aaa; margin: 5px 0 0 0; font-weight: normal; }
    </style>
</head>
<body>

    <!-- BARRA DINAMICA DI NAVIGAZIONE: Genera i link da sola leggendo il database -->
    <nav class="sticky-nav">
        <?php 
        // Generiamo i link in alto per saltare rapidamente alle sezioni
        while($cat = $categorie_query->fetch_assoc()) {
            echo "<a href='#cat-" . $cat['id'] . "'>" . htmlspecialchars($cat['nome']) . "</a>";
        }
        // Resettiamo il puntatore della query per poter riutilizzare i dati delle categorie sotto
        $categorie_query->data_seek(0);
        ?>
    </nav>

    <div class="menu-container">
        <?php 
        // CICLO DELLE CATEGORIE: Stampiamo una sezione per ogni macrocategoria presente nel database
        while($cat = $categorie_query->fetch_assoc()) {
            $categoria_id = $cat['id'];
            
            // Per ogni categoria, andiamo a cercare solo i piatti associati ad essa E che sono DISPONIBILI (disponibile = 1)
            $piatti_query = $conn->query("SELECT * FROM piatti WHERE categoria_id = $categoria_id AND disponibile = 1");
            
            // Mostriamo la categoria solo se contiene almeno un piatto disponibile
            if($piatti_query->num_rows > 0) {
                echo "<section class='category-section' id='cat-" . $categoria_id . "'>";
                echo "<h2 class='category-title'>" . htmlspecialchars($cat['nome']) . "</h2>";
                
                // CICLO DEI PIATTI: Stampiamo tutti i piatti di questa categoria
                while($piatto = $piatti_query->fetch_assoc()) {
                    echo "<div class='menu-item'>";
                    echo "  <div class='item-main'>";
                    echo "    <span class='item-name'>" . htmlspecialchars($piatto['nome']) . "</span>";
                    echo "    <span class='item-price'>€" . number_format($piatto['prezzo'], 2, ',', '.') . "</span>";
                    echo "  </div>";
                    
                    // Se c'è una descrizione la stampiamo, altrimenti il codice non lascia spazi vuoti
                    if(!empty($piatto['descrizione'])) {
                        echo "  <p class='item-description'>" . htmlspecialchars($piatto['descrizione']) . "</p>";
                    }
                    
                    echo "</div>";
                }
                
                echo "</section>";
            }
        }
        ?>
    </div>

</body>
</html>
