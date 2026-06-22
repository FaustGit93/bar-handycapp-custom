<?php
include 'config.php';

$hash = ADMIN_PASSWORD;
$password = $_GET['p'] ?? '';

if (password_verify($password, $hash)) {
    echo "✅ Password corretta!";
} else {
    echo "❌ Password errata.";
}