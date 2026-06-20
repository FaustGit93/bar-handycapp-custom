<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
include 'connessione.php';



$categorie_query = $conn->query("SELECT * FROM categorie WHERE visibile = 1 ORDER BY ordine ASC");

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Bar HandyCapp</title>
    <link rel="stylesheet" href="style/menu.css?v=<?php echo filemtime('style/menu.css'); ?>">

</head>
<body>

    <nav class="sticky-nav">
        <?php
        while ($cat = $categorie_query->fetch_assoc()) {
            echo "<a href='#cat-" . $cat['id'] . "'>" . htmlspecialchars($cat['nome']) . "</a>";
        }
        $categorie_query->data_seek(0);
        ?>
    </nav>

    <div class="menu-container">
        <?php
        while ($cat = $categorie_query->fetch_assoc()) {
            $categoria_id = $cat['id'];
            $piatti_query = $conn->query("SELECT * FROM piatti WHERE categoria_id = $categoria_id AND disponibile = 1");

            if ($piatti_query->num_rows > 0) {
                echo "<section class='category-section' id='cat-" . $categoria_id . "'>";
                echo "<h2 class='category-title'>" . htmlspecialchars($cat['nome']) . "</h2>";

                while ($piatto = $piatti_query->fetch_assoc()) {
                    echo "<div class='menu-item'>";
                    echo "  <div class='item-main'>";
                    echo "    <span class='item-name'>" . htmlspecialchars($piatto['nome']) . "</span>";
                    echo "    <span class='item-price'>€" . number_format($piatto['prezzo'], 2, ',', '.') . "</span>";
                    echo "  </div>";

                    if (!empty($piatto['descrizione'])) {
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