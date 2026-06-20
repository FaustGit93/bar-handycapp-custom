<?php
session_start();

include 'config.php';

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_inserita = $_POST['password'];

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
    <link rel="stylesheet" href="style/login.css?v=<?php echo filemtime('style/login.css'); ?>">
</head>
<body>

<div class="login-box">
    <h2>Area Riservata</h2>
    <p>Inserisci la password amministratore</p>

    <?php if (!empty($errore)) echo "<div class='error'>$errore</div>"; ?>

    <form action="login.php" method="POST">
        <input type="password" name="password" required placeholder="Password">
        <button type="submit">Accedi</button>
    </form>
</div>

</body>
</html>