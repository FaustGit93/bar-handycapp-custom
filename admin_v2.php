<?php
session_start();

if (!isset($_SESSION['admin_loggato']) || $_SESSION['admin_loggato'] !== true) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Gestiamo il cambio lingua tramite URL
if (isset($_GET['lang']) && in_array($_GET['lang'], ['it', 'en', 'pt'])) {
    setcookie('lingua', $_GET['lang'], time() + (60 * 60 * 24 * 365), '/');
    $_COOKIE['lingua'] = $_GET['lang'];
}

// Leggiamo la lingua dal cookie, default inglese
$lang = $_COOKIE['lingua'] ?? 'en';
include "lang/$lang.php";

include 'connessione.php';
require_once 'traduzione.php';

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
        $nuova_id_cat = $stmt->insert_id;
        traduci_e_salva_tutto($conn, 'categorie', $nuova_id_cat, 'nome', $nome_cat);
        $messaggio_cat = "<div class='alert success'>" . $t['cat_aggiunta'] . "</div>";
    } else {
        $messaggio_cat = "<div class='alert error'>" . $t['cat_errore_ins'] . "</div>";
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
        $messaggio_cat = "<div class='alert error'>⚠️ " . $t['cat_impossibile_el'] . " <strong>" . $risultato['totale'] . "</strong> " . $t['cat_impossibile_el2'] . "</div>";
    } else {
        $stmt_tr = $conn->prepare("DELETE FROM traduzioni WHERE tabella = 'categorie' AND riga_id = ?");
        $stmt_tr->bind_param("i", $id_cat);
        $stmt_tr->execute();
        $stmt_tr->close();

        $stmt = $conn->prepare("DELETE FROM categorie WHERE id = ?");
        $stmt->bind_param("i", $id_cat);
        if ($stmt->execute()) {
            $messaggio_cat = "<div class='alert success'>" . $t['cat_eliminata'] . "</div>";
        } else {
            $messaggio_cat = "<div class='alert error'>" . $t['cat_errore_el'] . "</div>";
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
    header("Location: admin_v2.php");
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

    header("Location: admin_v2.php");
    exit();
}

// --- LOGICA PIATTI: ELIMINAZIONE ---
if (isset($_GET['azione']) && $_GET['azione'] == 'elimina' && isset($_GET['id'])) {
    $id_da_eliminare = intval($_GET['id']);

    $stmt_tr = $conn->prepare("DELETE FROM traduzioni WHERE tabella = 'piatti' AND riga_id = ?");
    $stmt_tr->bind_param("i", $id_da_eliminare);
    $stmt_tr->execute();
    $stmt_tr->close();

    $stmt = $conn->prepare("DELETE FROM piatti WHERE id = ?");
    $stmt->bind_param("i", $id_da_eliminare);
    if ($stmt->execute()) {
        $messaggio = "<div class='alert success'>" . $t['piatto_eliminato'] . "</div>";
    } else {
        $messaggio = "<div class='alert error'>" . $t['piatto_errore_el'] . "</div>";
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
    header("Location: admin_v2.php");
    exit();
}

// --- LOGICA PIATTI: SALVA TRADUZIONE MANUALE ---
if (isset($_POST['azione_traduzione']) && $_POST['azione_traduzione'] == 'salva_piatto') {
    $id_piatto_trad = intval($_POST['id_piatto_trad']);
    $lingua_trad = $_POST['lingua_trad'];
    $nome_trad = trim($_POST['nome_trad']);
    $descrizione_trad = trim($_POST['descrizione_trad']);

    if (in_array($lingua_trad, LINGUE_SUPPORTATE) && $lingua_trad !== LINGUA_BASE) {
        if (!empty($nome_trad)) {
            salva_traduzione($conn, 'piatti', $id_piatto_trad, 'nome', $lingua_trad, $nome_trad, 1);
        }
        if (!empty($descrizione_trad)) {
            salva_traduzione($conn, 'piatti', $id_piatto_trad, 'descrizione', $lingua_trad, $descrizione_trad, 1);
        }
        $messaggio = "<div class='alert success'>✅ " . ($t['traduzione_salvata'] ?? 'Traduzione salvata!') . "</div>";
    }
}

// --- LOGICA CATEGORIE: SALVA TRADUZIONE MANUALE ---
if (isset($_POST['azione_traduzione']) && $_POST['azione_traduzione'] == 'salva_categoria') {
    $id_cat_trad = intval($_POST['id_cat_trad']);
    $lingua_trad = $_POST['lingua_trad'];
    $nome_trad = trim($_POST['nome_trad']);

    if (in_array($lingua_trad, LINGUE_SUPPORTATE) && $lingua_trad !== LINGUA_BASE) {
        if (!empty($nome_trad)) {
            salva_traduzione($conn, 'categorie', $id_cat_trad, 'nome', $lingua_trad, $nome_trad, 1);
        }
        $messaggio_cat = "<div class='alert success'>✅ " . ($t['traduzione_salvata'] ?? 'Traduzione salvata!') . "</div>";
    }
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
    $traduci_nome = isset($_POST['traduci_nome']) ? 1 : 0;

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

        if ($traduci_nome) {
            traduci_e_salva_tutto($conn, 'piatti', $id_piatto, 'nome', $nome);
        } else {
            $stmt_del_tr = $conn->prepare("DELETE FROM traduzioni WHERE tabella = 'piatti' AND riga_id = ? AND campo = 'nome'");
            $stmt_del_tr->bind_param("i", $id_piatto);
            $stmt_del_tr->execute();
            $stmt_del_tr->close();
        }
        if (!empty($descrizione)) {
            traduci_e_salva_tutto($conn, 'piatti', $id_piatto, 'descrizione', $descrizione);
        }

        $messaggio = "<div class='alert success'>" . $t['piatto_aggiornato'] . "</div>";
    } else {
        $messaggio = "<div class='alert error'>" . $t['piatto_errore_agg'] . "</div>";
    }
    $stmt->close();
}

// --- LOGICA PIATTI: INSERIMENTO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['azione_cat']) && !isset($_POST['azione_piatto']) && !isset($_POST['azione_traduzione'])) {
    $categoria_id = intval($_POST['categoria_id']);
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $note_allergeni = trim($_POST['note_allergeni']);
    $prezzo = floatval($_POST['prezzo']);
    $disponibile = isset($_POST['disponibile']) ? 1 : 0;
    $traduci_nome = isset($_POST['traduci_nome']) ? 1 : 0;

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

        if ($traduci_nome) {
            traduci_e_salva_tutto($conn, 'piatti', $nuovo_id_piatto, 'nome', $nome);
        }
        if (!empty($descrizione)) {
            traduci_e_salva_tutto($conn, 'piatti', $nuovo_id_piatto, 'descrizione', $descrizione);
        }

        $messaggio = "<div class='alert success'>" . $t['piatto_inserito'] . "</div>";
    } else {
        $messaggio = "<div class='alert error'>" . $t['piatto_errore_ins'] . "</div>";
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

$lingue_da_tradurre = array_values(array_diff(LINGUE_SUPPORTATE, [$lang]));
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar HandyCapp - Admin</title>
    <link rel="stylesheet" href="style/admin.css">
</head>
<body>

<div class="container">

    <div class="admin-topbar">
        <div class="admin-topbar-left">
            <a href="logout.php" class="btn-logout"><?php echo $t['esci']; ?></a>
        </div>
        <div class="lang-switcher">
            <input type="checkbox" id="lang-toggle" class="lang-toggle-input">
            <label for="lang-toggle" class="lang-selected">
                <img src="https://flagcdn.com/w40/<?php echo $lang === 'it' ? 'it' : ($lang === 'pt' ? 'br' : 'gb'); ?>.png" width="24" height="18">
                <?php echo $lang === 'it' ? 'Italiano' : ($lang === 'pt' ? 'Português' : 'English'); ?>
                <span class="lang-arrow">▾</span>
            </label>
            <div class="lang-options">
                <a href="?lang=it" class="<?php echo $lang === 'it' ? 'attiva' : ''; ?>">
                    <img src="https://flagcdn.com/w40/it.png" width="24" height="18"> Italiano
                </a>
                <a href="?lang=en" class="<?php echo $lang === 'en' ? 'attiva' : ''; ?>">
                    <img src="https://flagcdn.com/w40/gb.png" width="24" height="18"> English
                </a>
                <a href="?lang=pt" class="<?php echo $lang === 'pt' ? 'attiva' : ''; ?>">
                    <img src="https://flagcdn.com/w40/br.png" width="24" height="18"> Português
                </a>
            </div>
        </div>
    </div>

    <h1><?php echo $t['pannello_admin']; ?></h1>

    <!-- ===== SEZIONE CATEGORIE ===== -->
    <h2><?php echo $t['gestione_categorie']; ?></h2>
    <?php echo $messaggio_cat; ?>

    <form action="admin_v2.php" method="POST">
        <input type="hidden" name="azione_cat" value="aggiungi">
        <div class="form-inline">
            <div class="form-group">
                <label><?php echo $t['nome_categoria']; ?></label>
                <input type="text" name="nome_categoria" required placeholder="<?php echo $t['placeholder_categoria']; ?>">
            </div>
            <button type="submit" class="btn-verde"><?php echo $t['aggiungi']; ?></button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th><?php echo $t['col_nome']; ?></th>
                <th style="text-align:center;"><?php echo $t['col_sposta']; ?></th>
                <th style="text-align:center;"><?php echo $t['col_visibilita']; ?></th>
                <th style="text-align:center;">🌐</th>
                <th><?php echo $t['col_azioni']; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tutte_categorie as $i => $cat): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($cat['nome']); ?></strong></td>
                <td style="text-align:center;">
                    <?php if ($i > 0): ?>
                        <a href="admin_v2.php?azione=cat_su&id=<?php echo $cat['id']; ?>" class="btn-freccia">▲</a>
                    <?php else: ?>
                        <span class="freccia-disabilitata">▲</span>
                    <?php endif; ?>
                    <?php if ($i < $totale_cat - 1): ?>
                        <a href="admin_v2.php?azione=cat_giu&id=<?php echo $cat['id']; ?>" class="btn-freccia">▼</a>
                    <?php else: ?>
                        <span class="freccia-disabilitata">▼</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($cat['visibile'] == 1): ?>
                        <a href="admin_v2.php?azione=switch_visibilita_cat&id=<?php echo $cat['id']; ?>&stato=0" title="<?php echo $t['title_visibile']; ?>" style="text-decoration:none;">👁️</a>
                    <?php else: ?>
                        <a href="admin_v2.php?azione=switch_visibilita_cat&id=<?php echo $cat['id']; ?>&stato=1" title="<?php echo $t['title_nascosta']; ?>" style="opacity:0.4; text-decoration:none;">🚫</a>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <a href="#" class="btn-traduzioni-icon" title="Traduzioni"
                       onclick="document.getElementById('trad-cat-<?php echo $cat['id']; ?>').classList.toggle('aperto'); return false;">🌐</a>
                </td>
                <td>
                    <a href="admin_v2.php?azione=elimina_cat&id=<?php echo $cat['id']; ?>" class="btn-elimina" onclick="return confirm('<?php echo $t['confirm_elimina_cat']; ?>');"><?php echo $t['elimina']; ?></a>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="padding:0; border:0;">
                    <div class="form-modifica" id="trad-cat-<?php echo $cat['id']; ?>" style="margin:0;">
                        <?php
                        $traduzioni_cat = get_tutte_traduzioni($conn, 'categorie', $cat['id']);
                        ?>
                        <div class="trad-lang-selector">
                            <?php foreach ($lingue_da_tradurre as $idx => $lng): ?>
                                <input type="radio" name="trad_lang_select_cat_<?php echo $cat['id']; ?>"
                                       id="trad_lang_cat_<?php echo $cat['id']; ?>_<?php echo $lng; ?>"
                                       class="trad-lang-radio"
                                       onclick="mostraTraduzioneCat(<?php echo $cat['id']; ?>, '<?php echo $lng; ?>')"
                                       <?php echo $idx === 0 ? 'checked' : ''; ?>>
                                <label for="trad_lang_cat_<?php echo $cat['id']; ?>_<?php echo $lng; ?>" class="trad-lang-btn">
                                    <img src="https://flagcdn.com/w40/<?php echo $lng === 'pt' ? 'br' : ($lng === 'en' ? 'gb' : $lng); ?>.png" width="20" height="15">
                                    <?php echo strtoupper($lng); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <?php foreach ($lingue_da_tradurre as $idx => $lng): ?>
                            <form action="admin_v2.php" method="POST" class="trad-form" id="trad-form-cat-<?php echo $cat['id']; ?>-<?php echo $lng; ?>"
                                  style="<?php echo $idx === 0 ? '' : 'display:none;'; ?>">
                                <input type="hidden" name="azione_traduzione" value="salva_categoria">
                                <input type="hidden" name="id_cat_trad" value="<?php echo $cat['id']; ?>">
                                <input type="hidden" name="lingua_trad" value="<?php echo $lng; ?>">

                                <div class="form-group">
                                    <label>Nome (<?php echo strtoupper($lng); ?>)</label>
                                    <input type="text" name="nome_trad" value="<?php echo htmlspecialchars($traduzioni_cat[$lng]['nome'] ?? ''); ?>">
                                </div>
                                <button type="submit">Salva traduzione</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if ($totale_cat === 0): ?>
                <tr><td colspan="6" style="text-align:center;"><?php echo $t['nessuna_categoria']; ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>

    <!-- ===== FORM NUOVO PIATTO ===== -->
    <h2><?php echo $t['nuovo_piatto']; ?></h2>
    <?php echo $messaggio; ?>

    <form action="admin_v2.php" method="POST">
        <div class="form-group">
            <label for="categoria_id"><?php echo $t['categoria_menu']; ?></label>
            <select name="categoria_id" id="categoria_id" required>
                <option value=""><?php echo $t['seleziona_categoria']; ?></option>
                <?php while ($cat = $categorie_per_form->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="nome"><?php echo $t['nome_prodotto']; ?></label>
            <input type="text" name="nome" id="nome" required placeholder="<?php echo $t['placeholder_prodotto']; ?>">
        </div>
        <div class="form-group">
            <label for="descrizione"><?php echo $t['descrizione']; ?></label>
            <textarea name="descrizione" id="descrizione" rows="2"></textarea>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="traduci_nome" id="traduci_nome" value="1" checked style="width:auto;">
            <label for="traduci_nome" style="margin:0;"><?php echo $t['traduci_nome'] ?? 'Traduci il nome automaticamente'; ?></label>
        </div>

        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                <input type="checkbox" id="toggle-allergeni" onclick="document.getElementById('blocco-allergeni').classList.toggle('aperto');" style="width:auto;">
                <?php echo $t['contiene_allergeni']; ?>
            </label>
        </div>
        <div class="form-group blocco-allergeni-toggle" id="blocco-allergeni">
            <label><?php echo $t['allergeni']; ?></label>
            <div class="allergeni-grid">
                <?php foreach ($tutti_allergeni as $allergene): ?>
                    <label class="allergene-checkbox">
                        <input type="checkbox" name="allergeni[]" value="<?php echo $allergene['id']; ?>">
                        <?php echo htmlspecialchars($t[$allergene['chiave']] ?? $allergene['nome']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <label for="note_allergeni" style="margin-top:10px;"><?php echo $t['note_allergeni']; ?></label>
            <input type="text" name="note_allergeni" id="note_allergeni" placeholder="<?php echo $t['placeholder_note']; ?>">
        </div>

        <div class="form-group">
            <label for="prezzo"><?php echo $t['prezzo']; ?></label>
            <input type="number" name="prezzo" id="prezzo" step="0.01" required>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="disponibile" id="disponibile" value="1" checked style="width:auto;">
            <label for="disponibile" style="margin:0;"><?php echo $t['disponibile_subito']; ?></label>
        </div>
        <button type="submit"><?php echo $t['aggiungi_menu']; ?></button>
    </form>

    <hr>

    <!-- ===== LISTA PIATTI A CARD ===== -->
    <h2><?php echo $t['piatti_in_menu']; ?></h2>

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
            $id_piatto_loop = $piatto['id'];
            $res_allergeni_piatto = $conn->query("SELECT allergene_id FROM piatti_allergeni WHERE piatto_id = $id_piatto_loop");
            $allergeni_selezionati = [];
            while ($ra = $res_allergeni_piatto->fetch_assoc()) {
                $allergeni_selezionati[] = $ra['allergene_id'];
            }
            $ha_allergeni_salvati = count($allergeni_selezionati) > 0 || !empty($piatto['note_allergeni']);

            $ha_traduzione_nome = get_traduzione($conn, 'piatti', $piatto['id'], 'nome', 'en') !== null;
            $traduzioni_piatto = get_tutte_traduzioni($conn, 'piatti', $piatto['id']);
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
                            <a href="admin_v2.php?azione=switch_Stato&id=<?php echo $piatto['id']; ?>&stato=0"
                               class="badge badge-attivo" title="<?php echo $t['title_disponibile']; ?>">✅</a>
                        <?php else: ?>
                            <a href="admin_v2.php?azione=switch_Stato&id=<?php echo $piatto['id']; ?>&stato=1"
                               class="badge badge-disattivato" title="<?php echo $t['title_esaurito']; ?>">🚫</a>
                        <?php endif; ?>

                        <a href="#" class="btn-modifica-icon" title="<?php echo $t['title_modifica']; ?>"
                           onclick="document.getElementById('edit-<?php echo $piatto['id']; ?>').classList.toggle('aperto'); return false;">✏️</a>

                        <a href="#" class="btn-traduzioni-icon" title="Traduzioni"
                           onclick="document.getElementById('trad-<?php echo $piatto['id']; ?>').classList.toggle('aperto'); return false;">🌐</a>

                        <a href="admin_v2.php?azione=elimina&id=<?php echo $piatto['id']; ?>"
                           class="btn-elimina-icon"
                           title="<?php echo $t['title_elimina']; ?>"
                           onclick="return confirm('<?php echo $t['confirm_elimina_piatto']; ?> <?php echo htmlspecialchars($piatto['nome'], ENT_QUOTES); ?>?');">🗑️</a>
                    </div>
                </div>

                <div class="form-modifica" id="edit-<?php echo $piatto['id']; ?>">
                    <form action="admin_v2.php" method="POST">
                        <input type="hidden" name="azione_piatto" value="modifica">
                        <input type="hidden" name="id_piatto" value="<?php echo $piatto['id']; ?>">

                        <div class="form-group">
                            <label><?php echo $t['categoria_label']; ?></label>
                            <select name="categoria_id" required>
                                <?php foreach ($lista_categorie_edit as $cat_opt): ?>
                                    <option value="<?php echo $cat_opt['id']; ?>" <?php echo ($cat_opt['id'] == $piatto['categoria_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat_opt['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><?php echo $t['nome_label']; ?></label>
                            <input type="text" name="nome" required value="<?php echo htmlspecialchars($piatto['nome']); ?>">
                        </div>

                        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="traduci_nome" value="1" style="width:auto;" <?php echo $ha_traduzione_nome ? 'checked' : ''; ?>>
                            <label style="margin:0;"><?php echo $t['traduci_nome'] ?? 'Traduci il nome automaticamente'; ?></label>
                        </div>

                        <div class="form-group">
                            <label><?php echo $t['descrizione_label']; ?></label>
                            <textarea name="descrizione" rows="2"><?php echo htmlspecialchars($piatto['descrizione']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                                <input type="checkbox"
                                       id="toggle-allergeni-<?php echo $piatto['id']; ?>"
                                       onclick="document.getElementById('blocco-allergeni-<?php echo $piatto['id']; ?>').classList.toggle('aperto');"
                                       style="width:auto;"
                                       <?php echo $ha_allergeni_salvati ? 'checked' : ''; ?>>
                                <?php echo $t['contiene_allergeni']; ?>
                            </label>
                        </div>
                        <div class="form-group blocco-allergeni-toggle <?php echo $ha_allergeni_salvati ? 'aperto' : ''; ?>"
                             id="blocco-allergeni-<?php echo $piatto['id']; ?>">
                            <label><?php echo $t['allergeni']; ?></label>
                            <div class="allergeni-grid">
                                <?php foreach ($tutti_allergeni as $allergene): ?>
                                    <label class="allergene-checkbox">
                                        <input type="checkbox" name="allergeni[]" value="<?php echo $allergene['id']; ?>"
                                            <?php echo in_array($allergene['id'], $allergeni_selezionati) ? 'checked' : ''; ?>>
                                        <?php echo htmlspecialchars($t[$allergene['chiave']] ?? $allergene['nome']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <label style="margin-top:10px;"><?php echo $t['note_allergeni']; ?></label>
                            <input type="text" name="note_allergeni" value="<?php echo htmlspecialchars($piatto['note_allergeni'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label><?php echo $t['prezzo']; ?></label>
                            <input type="number" name="prezzo" step="0.01" required value="<?php echo $piatto['prezzo']; ?>">
                        </div>

                        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="disponibile" value="1" style="width:auto;" <?php echo ($piatto['disponibile'] == 1) ? 'checked' : ''; ?>>
                            <label style="margin:0;"><?php echo $t['disponibile_label']; ?></label>
                        </div>

                        <button type="submit"><?php echo $t['salva_modifiche']; ?></button>
                    </form>
                </div>

                <!-- Pannello traduzioni -->
                <div class="form-modifica" id="trad-<?php echo $piatto['id']; ?>">
                    <div class="trad-lang-selector">
                        <?php foreach ($lingue_da_tradurre as $idx => $lng): ?>
                            <input type="radio" name="trad_lang_select_<?php echo $piatto['id']; ?>"
                                   id="trad_lang_<?php echo $piatto['id']; ?>_<?php echo $lng; ?>"
                                   class="trad-lang-radio"
                                   onclick="mostraTraduzione(<?php echo $piatto['id']; ?>, '<?php echo $lng; ?>')"
                                   <?php echo $idx === 0 ? 'checked' : ''; ?>>
                            <label for="trad_lang_<?php echo $piatto['id']; ?>_<?php echo $lng; ?>" class="trad-lang-btn">
                                <img src="https://flagcdn.com/w40/<?php echo $lng === 'pt' ? 'br' : ($lng === 'en' ? 'gb' : $lng); ?>.png" width="20" height="15">
                                <?php echo strtoupper($lng); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach ($lingue_da_tradurre as $idx => $lng): ?>
                        <form action="admin_v2.php" method="POST" class="trad-form" id="trad-form-<?php echo $piatto['id']; ?>-<?php echo $lng; ?>"
                              style="<?php echo $idx === 0 ? '' : 'display:none;'; ?>">
                            <input type="hidden" name="azione_traduzione" value="salva_piatto">
                            <input type="hidden" name="id_piatto_trad" value="<?php echo $piatto['id']; ?>">
                            <input type="hidden" name="lingua_trad" value="<?php echo $lng; ?>">

                            <div class="form-group">
                                <label>Nome (<?php echo strtoupper($lng); ?>)</label>
                                <input type="text" name="nome_trad" value="<?php echo htmlspecialchars($traduzioni_piatto[$lng]['nome'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Descrizione (<?php echo strtoupper($lng); ?>)</label>
                                <textarea name="descrizione_trad" rows="2"><?php echo htmlspecialchars($traduzioni_piatto[$lng]['descrizione'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit">Salva traduzione</button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endwhile; ?>
    </div>
    <?php else: ?>
        <p style="color:#888; text-align:center; margin-top:20px;"><?php echo $t['nessun_piatto']; ?></p>
    <?php endif; ?>

</div>

<script>
function mostraTraduzione(idPiatto, lingua) {
    var forms = document.querySelectorAll('[id^="trad-form-' + idPiatto + '-"]');
    forms.forEach(function(f) { f.style.display = 'none'; });
    var target = document.getElementById('trad-form-' + idPiatto + '-' + lingua);
    if (target) target.style.display = 'block';
}

function mostraTraduzioneCat(idCat, lingua) {
    var forms = document.querySelectorAll('[id^="trad-form-cat-' + idCat + '-"]');
    forms.forEach(function(f) { f.style.display = 'none'; });
    var target = document.getElementById('trad-form-cat-' + idCat + '-' + lingua);
    if (target) target.style.display = 'block';
}
</script>

</body>
</html>
