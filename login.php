<?php
session_start();

// Includiamo il file di configurazione segreto
include 'config.php';

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_inserita = $_POST['password'];

    // Confrontiamo la password inserita con la costante ADMIN_PASSWORD definita in config.php
    if ($password_inserita === ADMIN_PASSWORD) {
        $_SESSION['admin_loggato'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $errore = "❌ Password errata. Riprova.";
    }
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Bar HandyCapp</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #111; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #222; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); width: 100%; max-width: 350px; text-align: center; }
        input[type="password"] { width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #444; background: #333; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #ffc107; color: #000; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; font-size: 16px; }
        button:hover { background-color: #e0a800; }
        .error { color: #dc3545; font-size: 14px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Area Riservata</h2>
    <p>Inserisci la password amministratore</p>
    
    <?php if(!empty($errore)) echo "<div class='error'>$errore</div>"; ?>

    <form action="login.php" method="POST">
        <input type="password" name="password" required placeholder="Password">
        <button type="submit">Accedi</button>
    </form>
</div>

</body>
</html>
