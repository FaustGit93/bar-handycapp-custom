<?php
// 1. Includiamo il file segreto di connessione al database
include 'connessione.php';

$messaggio = "";

// 2. LOGICA DI INSERIMENTO: Controlliamo se il ristoratore ha premuto il tasto "Salva Piatto"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperiamo i dati inviati dal modulo HTML
    $categoria_id = intval($_POST['categoria_id']); // Trasformiamo in numero per sicurezza
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $prezzo = floatval($_POST['prezzo']); // Trasformiamo in numero decimale
    $disponibile = isset($_POST['disponibile']) ? 1 : 0; // Se la casella è spuntata è 1, altrimenti 0

    // Prepariamo la query SQL sicura (Prepared Statement) per evitare attacchi informatici
    $stmt = $conn->prepare("INSERT INTO piatti (categoria_id, nome, descrizione, prezzo, disponibile) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issdi", $categoria_id, $nome, $descrizione, $prezzo, $disponibile);

    if ($stmt->execute()) {
        $messaggio = "<div class='alert success'>✅ Piatto inserito con successo nel database!</div>";
    } else {
        $messaggio = "<div class='alert error'>❌ Errore durante l'inserimento del piatto.</div>";
    }
    $stmt->close();
}

// 3. LOGICA DI LETTURA: Prendiamo tutte le categorie per popolare il menu a tendina del modulo
// Le prendiamo in base al campo 'ordine' che abbiamo stabilito nel database
$categorie_query = $conn->query("SELECT * FROM categorie ORDER BY ordine ASC");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Admin - Bar HandyCapp</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; padding: 20px; }
        .container { max-width: 500px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { font-size: 24px; margin-bottom: 20px; text-align: center; color: #111; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .checkbox-group input { width: 18px; height: 18px; cursor: pointer; }
        button { background-color: #007bff; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 10px; }
        button:hover { background-color: #0056b3; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h1>Nuovo Piatto / Drink</h1>

    <!-- Qui mostriamo il messaggio di successo o errore se presente -->
    <?php echo $messaggio; ?>

    <form action="admin.php" method="POST">
        
        <div class="form-group">
            <label for="categoria_id">Categoria del Menu</label>
            <select name="categoria_id" id="categoria_id" required>
                <option value="">-- Seleziona una categoria --</option>
                <?php 
                // Ciclo automatico: per ogni categoria nel database creiamo una opzione nel menu a tendina
                while($cat = $categorie_query->fetch_assoc()) {
                    echo "<option value='" . $cat['id'] . "'>" . htmlspecialchars($cat['nome']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="nome">Nome del Prodotto</label>
            <input type="text" name="nome" id="nome" required placeholder="Es. Spritz Aperol, Tiramisù...">
        </div>

        <div class="form-group">
            <label for="descrizione">Descrizione / Ingredienti (Opzionale)</label>
            <textarea name="descrizione" id="descrizione" rows="3" placeholder="Es. Prosecco, aperol, soda..."></textarea>
        </div>

        <div class="form-group">
            <label for="prezzo">Prezzo (€)</label>
            <input type="number" name="prezzo" id="prezzo" step="0.01" required placeholder="Es. 5.00">
        </div>

        <div class="form-group checkbox-group">
            <input type="checkbox" name="disponibile" id="disponibile" value="1" checked>
            <label for="disponibile">Disponibile immediatamente sul sito</label>
        </div>

        <button type="submit">Aggiungi al Menu</button>
    </form>
</div>

</body>
</html>
