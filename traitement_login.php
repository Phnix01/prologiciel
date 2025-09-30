<?php
session_start();
require_once 'config/database.php';

// Vérification si le formulaire est soumis
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération et sécurisation des données
    $matricule = htmlspecialchars(trim($_POST['matricule']));
    $password = $_POST['password'];
    
    try {
        // Recherche de l'agent dans la base de données
        // Note: Vous devrez ajouter un champ "mot_de_passe" dans votre table AGENT/VENDEUR
        $sql = "SELECT a.IdVendeur as id, a.nom, a.prenom, a.matricule, a.role_agent, a.mot_de_passe 
                FROM VENDEUR a 
                WHERE a.matricule = ? AND a.actif = 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$matricule]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérification du mot de passe
        if($agent && password_verify($password, $agent['mot_de_passe'])) {
            // Authentification réussie
            $_SESSION['agent_connecte'] = true;
            $_SESSION['agent_id'] = $agent['id'];
            $_SESSION['agent_nom'] = $agent['nom'];
            $_SESSION['agent_prenom'] = $agent['prenom'];
            $_SESSION['agent_matricule'] = $agent['matricule'];
            $_SESSION['agent_role'] = $agent['role_agent'];
            
            // Journalisation de la connexion
            $sql_log = "INSERT INTO logs_connexion (id_agent, date_connexion, ip) 
                        VALUES (?, NOW(), ?)";
            $db->prepare($sql_log)->execute([
                $agent['id'], 
                $_SERVER['REMOTE_ADDR']
            ]);
            
            // Redirection vers le tableau de bord
            header('Location: dashboard.php');
            exit();
            
        } else {
            // Authentification échouée
            header('Location: index.php?error=1');
            exit();
        }
        
    } catch(PDOException $e) {
        // En cas d'erreur base de données
        error_log("Erreur connexion: " . $e->getMessage());
        header('Location: index.php?error=1');
        exit();
    }
    
} else {
    // Accès direct non autorisé
    header('Location: index.php');
    exit();
}
?>