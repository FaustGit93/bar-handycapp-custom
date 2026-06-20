<?php
session_start();

if (!isset($_SESSION['admin_loggato']) || $_SESSION['admin_loggato'] !== true) {
    header("Location: login.php");
    exit();
}

include 'connessione.php';

$messaggio = "";
$messaggio_cat = "";

// --- LOGICA CATEGORIE: INSERIMENTO ---
if (isset($_POST['azione_cat']) && $_POST['azione_cat'] == 'aggiungi') {
    $nome_cat = trim($_POST['nome_categoria']);
    $res_ordine = $conn->query("SELECT COALESCE(MAX(ordine), 0) + 1 AS prossimo FROM categorie");
    $prossimo_ordine = $res_ordine->fetch_assoc()['prossimo'];

    $stmt = $conn->prepare("INSERT INTO categorie (nome, ordine) VALUES (?, ?)");
    $stmt->bind_param("si", $nome_cat, $prossimo_ordine);
    if ($stmt->execute()) {
        $messaggio_cat = "<div class='alert success'>✅ Categoria aggiunta!</div>";
    } else {
        $messaggio_cat = "<div class='alert error'>❌ Errore durante l'inserimento.</div>";
    }
    $stmt->close();
}

// --- LOGICA CATEGORIE: ELIMINAZIONE ---
if (isset($_GET['azione']) && $_GET['azione'] == 'elimina_cat' && isset($_GET['id'])) {
    $id_cat = intval($_GET['id']);
    $check = $conn->prepare("SELECT COUNT(*) as totale FROM piatti WHERE categoria_id = ?");
    $check->bind_param("i", $id_cat);
    $check->execute();
    $risultato = $check->get_result()->fetch_assoc();
    $check->close();

    if ($risultato['totale'] > 0) {
        $messaggio_cat = "<div class='alert error'>⚠️ Impossibile eliminare: ci sono <strong>" . $risultato['totale'] . " piatti</strong> in questa categoria. Eliminali prima.</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM categorie WHERE id = ?");
        $stmt->bind_param("i", $id_cat);
        if ($stmt->execute()) {
            $messaggio_cat = "<div class='alert success'>🗑️ Categoria eliminata!</div>";
        } else {
            $messaggio_cat = "<div class='alert error'>❌ Errore durante l'eliminazione.</div>";
        }
        $stmt->close();
    }
}

// --- LOGICA CATEGORIE: MOSTRA/NASCONDI ---
if (isset($_GET['azione']) && $_GET['azione'] == 'switch_visibilita_cat' && isset($_GET['id'])) {
    $id_cat = intval($_GET['id']);
    $nuovo_stato = intval($_GET['stato']);
    $stmt = $conn->prepare("UPDATE categorie SET visibile = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuovo_stato, $id_cat);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// --- LOGICA CATEGORIE: SPOSTA SU / GIÙ ---
if (isset($_GET['azione']) && in_array($_GET['azione'], ['cat_su', 'cat_giu']) && isset($_GET['id'])) {
    $id_corrente = intval($_GET['id']);
    $direzione = $_GET['azione'];

    $res = $conn->prepare("SELECT ordine FROM categorie WHERE id = ?");
    $res->bind_param("i", $id_corrente);
    $res->execute();
    $ordine_corrente = $res->get_result()->fetch_assoc()['ordine'];
    $res->close();

    if ($direzione == 'cat_su') {
        $stmt_vicina = $conn->prepare("SELECT id, ordine FROM categorie WHERE ordine < ? ORDER BY ordine DESC LIMIT 1");
    } else {
        $stmt_vicina = $conn->prepare("SELECT id, ordine FROM categorie WHERE ordine > ? ORDER BY ordine ASC LIMIT 1");
    }
    $stmt_vicina->bind_param("i", $ordine_corrente);
    $stmt_vicina->execute();
    $res_vicina = $stmt_vicina->get_result();
    $stmt_vicina->close();

    if ($res_vicina->num_rows > 0) {
        $vicina = $res_vicina->fetch_assoc();

        $stmt1 = $conn->prepare("UPDATE categorie SET ordine = ? WHERE id = ?");
        $stmt1->bind_param("ii", $vicina['ordine'], $id_corrente);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("UPDATE categorie SET ordine = ? WHERE id = ?");
        $stmt2->bind_param("ii", $ordine_corrente, $vicina['id']);
        $stmt2->execute();
        $stmt2->close();
    }

    header("Location: admin.php");
    exit();
}

// --- LOGICA PIATTI: ELIMINAZIONE ---
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

// --- LOGICA PIATTI: CAMBIO DISPONIBILITÀ ---
if (isset($_GET['azione']) && $_GET['azione'] == 'switch_Stato' && isset($_GET['id'])) {
    $id_piatto = intval($_GET['id']);
    $nuovo_stato = intval($_GET['stato']);
    $stmt = $conn->prepare("UPDATE piatti SET disponibile = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuovo_stato, $id_piatto);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// --- LOGICA PIATTI: MODIFICA ---
if (isset($_POST['azione_piatto']) && $_POST['azione_piatto'] == 'modifica') {
    $id_piatto = intval($_POST['id_piatto']);
    $categoria_id = intval($_POST['categoria_id']);
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $note_allergeni = trim($_POST['note_allergeni']);
    $prezzo = floatval($_POST['prezzo']);
    $disponibile = isset($_POST['disponibile']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE piatti SET categoria_id = ?, nome = ?, descrizione = ?, note_allergeni = ?, prezzo = ?, disponibile = ? WHERE id = ?");
    $stmt->bind_param("isssdii", $categoria_id, $nome, $descrizione, $note_allergeni, $prezzo, $disponibile, $id_piatto);

    if ($stmt->execute()) {
        $stmt_del = $conn->prepare("DELETE FROM piatti_allergeni WHERE piatto_id = ?");
        $stmt_del->bind_param("i", $id_piatto);
        $stmt_del->execute();
        $stmt_del->close();

        if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
            $stmt_all = $conn->prepare("INSERT INTO piatti_allergeni (piatto_id, allergene_id) VALUES (?, ?)");
            foreach ($_POST['allergeni'] as $id_allergene) {
                $id_allergene = intval($id_allergene);
                $stmt_all->bind_param("ii", $id_piatto, $id_allergene);
                $stmt_all->execute();
            }
            $stmt_all->close();
        }

        $messaggio = "<div class='alert success'>✅ Piatto aggiornato con successo!</div>";
    } else {
        $messaggio = "<div class='alert error'>❌ Errore durante l'aggiornamento.</div>";
    }
    $stmt->close();
}

// --- LOGICA PIATTI: INSERIMENTO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['azione_cat']) && !isset($_POST['azione_piatto'])) {
    $categoria_id = intval($_POST['categoria_id']);
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $note_allergeni = trim($_POST['note_allergeni']);
    $prezzo = floatval($_POST['prezzo']);
    $disponibile = isset($_POST['disponibile']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO piatti (categoria_id, nome, descrizione, note_allergeni, prezzo, disponibile) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $categoria_id, $nome, $descrizione, $note_allergeni, $prezzo, $disponibile);
    if ($stmt->execute()) {
        $nuovo_id_piatto = $stmt->insert_id;

        if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
            $stmt_all = $conn->prepare("INSERT INTO piatti_allergeni (piatto_id, allergene_id) VALUES (?, ?)");
            foreach ($_POST['allergeni'] as $id_allergene) {
                $id_allergene = intval($id_allergene);
                $stmt_all->bind_param("ii", $nuovo_id_piatto, $id_allergene);
                $stmt_all->execute();
            }
            $stmt_all->close();
        }

        $messaggio = "<div class='alert success'>✅ Piatto inserito con successo!</div>";
    } else {
        $messaggio = "<div class='alert error'>❌ Errore durante l'inserimento.</div>";
    }
    $stmt->close();
}

// --- LETTURA DATI ---
$cat_lista = $conn->query("SELECT * FROM categorie ORDER BY ordine ASC");
$tutte_categorie = [];
while ($c = $cat_lista->fetch_assoc()) {
    $tutte_categorie[] = $c;
}
$totale_cat = count($tutte_categorie);

$categorie_per_form = $conn->query("SELECT * FROM categorie ORDER BY ordine ASC");
$lista_allergeni = $conn->query("SELECT * FROM allergeni ORDER BY id ASC");
$tutti_allergeni = [];
while ($a = $lista_allergeni->fetch_assoc()) {
    $tutti_allergeni[] = $a;
}
$piatti_query = $conn->query("SELECT p.*, c.nome AS nome_categoria FROM piatti p JOIN categorie c ON p.categoria_id = c.id ORDER BY c.ordine ASC, p.nome ASC");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Admin - Bar HandyCapp</title>
    <link rel="stylesheet" href="style/admin.css?v=<?php echo filemtime('style/admin.css'); ?>">
</head>
<body>

<div class="container">

    <a href="logout.php" style="color: #dc3545; float: right; font-weight: bold; text-decoration: none;">Esci (Logout)</a>
    <h1>Pannello Admin</h1>

    <!-- ===== SEZIONE CATEGORIE ===== -->
    <h2>📂 Gestione Categorie</h2>
    <?php echo $messaggio_cat; ?>

    <form action="admin.php" method="POST">
        <input type="hidden" name="azione_cat" value="aggiungi">
        <div class="form-inline">
            <div class="form-group">
                <label>Nome Categoria</label>
                <input type="text" name="nome_categoria" required placeholder="Es. Cocktail, Panini...">
            </div>
            <button type="submit" class="btn-verde">+ Aggiungi</button>
        </div>
    </form>

    <table>
        <thead>
           <tr>
    <th>Ordine</th>
    <th>Nome</th>
    <th style="text-align:center;">Sposta</th>
    <th style="text-align:center;">Visibilità</th>
    <th>Azioni</th>
</tr>
        </thead>
        <tbody>
            <?php foreach ($tutte_categorie as $i => $cat): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><strong><?php echo htmlspecialchars($cat['nome']); ?></strong></td>
                <td style="text-align:center;">
                    <?php if ($i > 0): ?>
                        <a href="admin.php?azione=cat_su&id=<?php echo $cat['id']; ?>" class="btn-freccia" title="Sposta su">▲</a>
                    <?php else: ?>
                        <span class="freccia-disabilitata">▲</span>
                    <?php endif; ?>
                    <?php if ($i < $totale_cat - 1): ?>
                        <a href="admin.php?azione=cat_giu&id=<?php echo $cat['id']; ?>" class="btn-freccia" title="Sposta giù">▼</a>
                    <?php else: ?>
                        <span class="freccia-disabilitata">▼</span>
                    <?php endif; ?>
                </td>
                     <td style="text-align:center;">
                    <?php if ($cat['visibile'] == 1): ?>
                        <a href="admin.php?azione=switch_visibilita_cat&id=<?php echo $cat['id']; ?>&stato=0" title="Visibile — clicca per nascondere" style="text-decoration:none;">👁️</a>
                    <?php else: ?>
                        <a href="admin.php?azione=switch_visibilita_cat&id=<?php echo $cat['id']; ?>&stato=1" title="Nascosta — clicca per mostrare" style="opacity:0.4; text-decoration: none;">🚫</a>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="admin.php?azione=elimina_cat&id=<?php echo $cat['id']; ?>" class="btn-elimina" onclick="return confirm('Eliminare questa categoria? Assicurati che non contenga piatti.');">Elimina</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if ($totale_cat === 0): ?>
                <tr><td colspan="4" style="text-align:center;">Nessuna categoria presente.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>

    <!-- ===== FORM NUOVO PIATTO ===== -->
    <h2>🍽️ Nuovo Piatto / Drink</h2>
    <?php echo $messaggio; ?>

    <form action="admin.php" method="POST">
        <div class="form-group">
            <label for="categoria_id">Categoria del Menu</label>
            <select name="categoria_id" id="categoria_id" required>
                <option value="">-- Seleziona una categoria --</option>
                <?php while ($cat = $categorie_per_form->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                <?php endwhile; ?>
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
            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                <input type="checkbox" id="toggle-allergeni" onclick="document.getElementById('blocco-allergeni').classList.toggle('aperto');" style="width:auto;">
                Questo piatto contiene allergeni?
            </label>
        </div>
        <div class="form-group blocco-allergeni-toggle" id="blocco-allergeni">
            <label>Allergeni</label>
            <div class="allergeni-grid">
                <?php foreach ($tutti_allergeni as $allergene): ?>
                    <label class="allergene-checkbox">
                        <input type="checkbox" name="allergeni[]" value="<?php echo $allergene['id']; ?>">
                        <?php echo htmlspecialchars($allergene['nome']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <label for="note_allergeni" style="margin-top:10px;">Note allergeni (facoltativo)</label>
            <input type="text" name="note_allergeni" id="note_allergeni" placeholder="Es. tracce di frutta a guscio">
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

    <hr>

    <!-- ===== LISTA PIATTI A CARD ===== -->
    <h2>📋 Piatti in Menu</h2>

    <?php if ($piatti_query->num_rows > 0):
        $categoria_corrente = null;
        $categorie_per_edit = $conn->query("SELECT * FROM categorie ORDER BY ordine ASC");
        $lista_categorie_edit = [];
        while ($c = $categorie_per_edit->fetch_assoc()) {
            $lista_categorie_edit[] = $c;
        }
    ?>
    <div class="piatti-lista">
        <?php while ($piatto = $piatti_query->fetch_assoc()):
            // Recuperiamo gli allergeni già assegnati a questo piatto
            $id_piatto_loop = $piatto['id'];
            $res_allergeni_piatto = $conn->query("SELECT allergene_id FROM piatti_allergeni WHERE piatto_id = $id_piatto_loop");
            $allergeni_selezionati = [];
            while ($ra = $res_allergeni_piatto->fetch_assoc()) {
                $allergeni_selezionati[] = $ra['allergene_id'];
            }
        ?>

        <?php if ($piatto['nome_categoria'] !== $categoria_corrente): ?>
            <div class="categoria-header"><?php echo htmlspecialchars($piatto['nome_categoria']); ?></div>
            <?php $categoria_corrente = $piatto['nome_categoria']; ?>
        <?php endif; ?>

            <div class="piatto-card">
                <div class="piatto-row">
                    <div class="piatto-info">
                        <div class="nome"><?php echo htmlspecialchars($piatto['nome']); ?></div>
                        <?php if (!empty($piatto['descrizione'])): ?>
                            <div class="descrizione"><?php echo htmlspecialchars($piatto['descrizione']); ?></div>
                        <?php endif; ?>
                        <div class="prezzo">€<?php echo number_format($piatto['prezzo'], 2, ',', '.'); ?></div>
                    </div>
                    <div class="piatto-azioni">
                        <?php if ($piatto['disponibile'] == 1): ?>
                            <a href="admin.php?azione=switch_Stato&id=<?php echo $piatto['id']; ?>&stato=0"
                               class="badge badge-attivo" title="Disponibile — clicca per segnare come esaurito">✅</a>
                        <?php else: ?>
                            <a href="admin.php?azione=switch_Stato&id=<?php echo $piatto['id']; ?>&stato=1"
                               class="badge badge-disattivato" title="Esaurito — clicca per rendere disponibile">🚫</a>
                        <?php endif; ?>

                        <a href="#" class="btn-modifica-icon" title="Modifica piatto"
                           onclick="document.getElementById('edit-<?php echo $piatto['id']; ?>').classList.toggle('aperto'); return false;">✏️</a>

                        <a href="admin.php?azione=elimina&id=<?php echo $piatto['id']; ?>"
                           class="btn-elimina-icon"
                           title="Elimina piatto"
                           onclick="return confirm('Eliminare definitivamente <?php echo htmlspecialchars($piatto['nome'], ENT_QUOTES); ?>?');">🗑️</a>
                    </div>
                </div>

                <!-- Form di modifica inline, nascosto finché non si clicca la matita -->
                <div class="form-modifica" id="edit-<?php echo $piatto['id']; ?>">
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="azione_piatto" value="modifica">
                        <input type="hidden" name="id_piatto" value="<?php echo $piatto['id']; ?>">

                        <div class="form-group">
                            <label>Categoria</label>
                            <select name="categoria_id" required>
                                <?php foreach ($lista_categorie_edit as $cat_opt): ?>
                                    <option value="<?php echo $cat_opt['id']; ?>" <?php echo ($cat_opt['id'] == $piatto['categoria_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat_opt['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="nome" required value="<?php echo htmlspecialchars($piatto['nome']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Descrizione</label>
                            <textarea name="descrizione" rows="2"><?php echo htmlspecialchars($piatto['descrizione']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Allergeni</label>
                            <div class="allergeni-grid">
                                <?php foreach ($tutti_allergeni as $allergene): ?>
                                    <label class="allergene-checkbox">
                                        <input type="checkbox" name="allergeni[]" value="<?php echo $allergene['id']; ?>"
                                            <?php echo in_array($allergene['id'], $allergeni_selezionati) ? 'checked' : ''; ?>>
                                        <?php echo htmlspecialchars($allergene['nome']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Note allergeni (facoltativo)</label>
                            <input type="text" name="note_allergeni" value="<?php echo htmlspecialchars($piatto['note_allergeni'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Prezzo (€)</label>
                            <input type="number" name="prezzo" step="0.01" required value="<?php echo $piatto['prezzo']; ?>">
                        </div>

                        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="disponibile" value="1" <?php echo ($piatto['disponibile'] == 1) ? 'checked' : ''; ?>>
                            <label style="margin:0;">Disponibile</label>
                        </div>

                        <button type="submit">Salva modifiche</button>
                    </form>
                </div>
            </div>

        <?php endwhile; ?>
    </div>
    <?php else: ?>
        <p style="color:#888; text-align:center; margin-top:20px;">Nessun piatto presente nel database.</p>
    <?php endif; ?>

</div>

</body>
</html>