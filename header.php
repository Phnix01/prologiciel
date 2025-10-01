<?php
// Vérification que l'utilisateur est connecté
if(!isset($_SESSION['agent_connecte'])) {
    header('Location: index.php');
    exit();
}
?>

<!-- Header -->
<div class="header">
    <div class="logo">
        <h1>🚗 Parc Auto Pro</h1>
    </div>
    <div class="user-info">
        <span class="user-welcome">
            Bonjour, <strong><?php echo $_SESSION['agent_prenom'] . ' ' . $_SESSION['agent_nom']; ?></strong>
            (<?php echo $_SESSION['agent_role']; ?>)
        </span>
        <a href="dashboard.php" class="btn-primary" style="margin-right: 10px;">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="deconnexion.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</div>