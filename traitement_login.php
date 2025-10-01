<?php
session_start();

// Vérification si le formulaire est soumis
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération des données
    $matricule = trim($_POST['matricule']);
    $password = $_POST['password'];
    
    // Authentification simplifiée - identifiants codés en dur
    $matricule_valide = "ADMIN001";
    $password_valide = "password001";
    
    // Vérification des identifiants
    if($matricule === $matricule_valide && $password === $password_valide) {
        // Authentification réussie
        $_SESSION['agent_connecte'] = true;
        $_SESSION['agent_id'] = 1;
        $_SESSION['agent_nom'] = "Admin";
        $_SESSION['agent_prenom'] = "System";
        $_SESSION['agent_matricule'] = "ADMIN001";
        $_SESSION['agent_role'] = "Administrateur";
        
        // Redirection vers le tableau de bord
        header('Location: dashboard.php');
        exit();
        
    } else {
        // Authentification échouée
        header('Location: index.php?error=1');
        exit();
    }
    
} else {
    // Accès direct non autorisé
    header('Location: index.php');
    exit();
}
?>