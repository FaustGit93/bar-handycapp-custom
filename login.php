<?php
session_start();

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

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_inserita = $_POST['password'];

    if (password_verify($password_inserita, ADMIN_PASSWORD)) {
        $_SESSION['admin_loggato'] = true;
        $_SESSION['lingua'] = $lang;
        header("Location: admin_v2.php");
        exit();
    } else {
        $errore = $t['errore_password'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar HandyCapp - Admin</title>
    <link rel="stylesheet" href="style/login.css">
</head>
<body>

<div class="login-box">

    <div class="top-selectors">
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

        <div class="theme-switcher">
            <input type="checkbox" id="theme-toggle" class="theme-toggle-input">
            <label for="theme-toggle" class="theme-selected">
                <span class="theme-icon">🖥️</span>
                <span class="theme-arrow">▾</span>
            </label>
            <div class="theme-options">
                <a href="#" data-theme="light">☀️ Light</a>
                <a href="#" data-theme="dark">🌙 Dark</a>
                <a href="#" data-theme="system">🖥️ System</a>
            </div>
        </div>
    </div>

    <h2><?php echo $t['titolo']; ?></h2>
    <p><?php echo $t['sottotitolo']; ?></p>

    <?php if (!empty($errore)) echo "<div class='error'>$errore</div>"; ?>

    <form action="login.php" method="POST">
        <input type="password" name="password" required placeholder="<?php echo $t['placeholder']; ?>">
        <button type="submit"><?php echo $t['bottone']; ?></button>
    </form>
</div>

<script src="js/theme.js"></script>

</body>
</html>