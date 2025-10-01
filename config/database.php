<?php
// Configuration de base pour la base de données
$host = 'localhost';
$dbname = 'CARSELLANDRENT';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // En cas d'erreur, on continue sans base de données pour les démos
    // die("Erreur de connexion : " . $e->getMessage());
    $db = null;
}
?>