<?php
include 'connessione.php';

$messaggio = "";

// --- 1. LOGICA DI ELIMINAZIONE (Se arriva una richiesta di cancellazione) ---
if (isset($_GET['azione']) && $_GET['azione'] == 'elimina' && isset($_GET['id'])) {
    $id_da_eliminare = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM piatti WHERE id = ?");
    $stmt->bind_param("i", $id_da_eliminare);
    
    if ($stmt->execute()) {
        $messaggio = "<div class='alert success'>🗑️ Piatto eliminato con successo!</div>";
    } else {
        $messaggio = "<div class='alert error'>❌ Errore durante l'eliminazione.</div>";
    }
    $stmt->close();
}

// --- 2. LOGICA DI CAMBIO DISPONIBILITÀ VELOCE ---
if (isset($_GET['azione']) && $_GET['azione'] == 'switch_Stato' && isset($_GET['id'])) {
    $id_piatto = intval($_GET['id']);
    $nuovo_stato = intval($_GET['stato']);
    
    $stmt = $conn->prepare("UPDATE piatti SET disponibile = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuovo_stato, $id_piatto);
    $stmt->execute();
    $stmt->close();
    // Reindirizziamo alla stessa pagina per pulire l'URL
    header("Location: admin.php");
    exit();
}

// --- 3. LOGICA DI INSERIMENTO NUOVO PIATTO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria_id = intval($_POST['categoria_id']);
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $prezzo = floatval($_POST['prezzo']);
    $disponibile = isset($_POST['disponibile']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO piatti (categoria_id, nome, descrizione, prezzo, disponibile) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issdi", $categoria_id, $nome, $descrizione, $prezzo, $disponibile);

    if ($stmt->execute()) {
        $messaggio = "<div class='alert success'>✅ Piatto inserito con successo!</div>";
    } else {
        $messaggio = "<div class='alert error'>❌ Errore durante l'inserimento.</div>";
    }
    $stmt->close();
}

// --- 4. LETTURA DATI PER IL FORM E PER LA TABELLA ---
$categorie_query = $conn->query("SELECT * FROM categorie ORDER BY ordine ASC");

// Questa query prende i piatti unendo le informazioni della categoria per mostrare il nome della categoria nella tabella (JOIN)
$piatti_query = $conn->query("SELECT p.*, c.nome AS nome_categoria FROM piatti p JOIN categorie c ON p.categoria_id = c.id ORDER BY c.ordine ASC, p.nome ASC");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Admin - Bar HandyCapp</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; color: #333; padding: 20px; }
        .container { max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1, h2 { color: #111; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        button:hover { background-color: #0056b3; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        
        /* Stili per la tabella di gestione */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; }
        .btn-elimina { color: #dc3545; text-decoration: none; font-weight: bold; }
        .btn-elimina:hover { text-decoration: underline; }
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; text-decoration: none; font-weight: bold; }
        .badge-attivo { background-color: #d4edda; color: #155724; }
        .badge-disattivato { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>

<div class="container">
    <h1>Nuovo Piatto / Drink</h1>
    <?php echo $messaggio; ?>

    <!-- FORM DI INSERIMENTO (Inalterato) -->
    <form action="admin.php" method="POST">
        <div class="form-group">
            <label for="categoria_id">Categoria del Menu</label>
            <select name="categoria_id" id="categoria_id" required>
                <option value="">-- Seleziona una categoria --</option>
                <?php 
                while($cat = $categorie_query->fetch_assoc()) {
                    echo "<option value='" . $cat['id'] . "'>" . htmlspecialchars($cat['nome']) . "</option>";
                }
                $categorie_query->data_seek(0); // Resettiamo per usi futuri
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="nome">Nome del Prodotto</label>
            <input type="text" name="nome" id="nome" required placeholder="Es. Spritz Aperol">
        </div>
        <div class="form-group">
            <label for="descrizione">Descrizione / Ingredienti</label>
            <textarea name="descrizione" id="descrizione" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label for="prezzo">Prezzo (€)</label>
            <input type="number" name="prezzo" id="prezzo" step="0.01" required>
        </div>
        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="disponibile" id="disponibile" value="1" checked>
            <label for="disponibile" style="margin:0;">Disponibile subito sul sito</label>
        </div>
        <button type="submit">Aggiungi al Menu</button>
    </form>

    <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eee;">

    <!-- NUOVA SEZIONE: TABELLA DI GESTIONE E RIMOZIONE -->
    <h2>Piatti in Menu</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Prezzo</th>
                <th>Stato (Clicca per cambiare)</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($piatti_query->num_rows > 0) {
                while($piatto = $piatti_query->fetch_assoc()) {
                    echo "<tr>";
                    echo "  <td><strong>" . htmlspecialchars($piatto['nome']) . "</strong></td>";
                    echo "  <td>" . htmlspecialchars($piatto['nome_categoria']) . "</td>";
                    echo "  <td>€" . number_format($piatto['prezzo'], 2, ',', '.') . "</td>";
                    
                    // Gestione del badge Disponibile/Non Disponibile dinamico
                    if ($piatto['disponibile'] == 1) {
                        echo "  <td><a href='admin.php?azione=switch_Stato&id=" . $piatto['id'] . "&stato=0' class='badge badge-attivo'>Disponibile</a></td>";
                    } else {
                        echo "  <td><a href='admin.php?azione=switch_Stato&id=" . $piatto['id'] . "&stato=1' class='badge badge-disattivato'>Esaurito</a></td>";
                    }
                    
                    // Link di eliminazione che passa l'ID tramite metodo GET nell'URL
                    echo "  <td><a href='admin.php?azione=elimina&id=" . $piatto['id'] . "' class='btn-elimina' onclick=\"return confirm('Sei sicuro di voler eliminare definitivamente questo piatto?');\">Elimina</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Nessun piatto presente nel database.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
