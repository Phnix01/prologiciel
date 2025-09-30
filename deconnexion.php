<?php
session_start();

// Journalisation de la déconnexion
if(isset($_SESSION['agent_id'])) {
    require_once 'config/database.php';
    try {
        $sql = "INSERT INTO logs_deconnexion (id_agent, date_deconnexion, ip) 
                VALUES (?, NOW(), ?)";
        $db->prepare($sql)->execute([
            $_SESSION['agent_id'],
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch(PDOException $e) {
        // Ignorer les erreurs de journalisation
    }
}

// Destruction de la session
session_destroy();

// Redirection vers la page de connexion
header('Location: index.php?logout=1');
exit();
?>