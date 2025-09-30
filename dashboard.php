<?php
session_start();
require_once 'config/database.php';

// Vérification de l'authentification
if(!isset($_SESSION['agent_connecte'])) {
    header('Location: index.php');
    exit();
}

// Récupération des statistiques pour le dashboard
try {
    // Nombre de véhicules disponibles
    $stmt = $db->query("SELECT COUNT(*) as total FROM VEHICULE WHERE statut = 'disponible'");
    $vehicules_disponibles = $stmt->fetch()['total'];
    
    // Locations en cours
    $stmt = $db->query("SELECT COUNT(*) as total FROM CONTRAT WHERE type_contrat = 'location' AND date_fin >= CURDATE()");
    $locations_cours = $stmt->fetch()['total'];
    
    // Ventes du mois
    $stmt = $db->query("SELECT COUNT(*) as total FROM CONTRAT WHERE type_contrat = 'vente' AND MONTH(date_contrat) = MONTH(CURDATE())");
    $ventes_mois = $stmt->fetch()['total'];
    
    // Clients actifs
    $stmt = $db->query("SELECT COUNT(DISTINCT id_client) as total FROM CONTRAT WHERE date_contrat >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $clients_actifs = $stmt->fetch()['total'];
    
} catch(PDOException $e) {
    // Gestion silencieuse des erreurs pour les stats
    $vehicules_disponibles = $locations_cours = $ventes_mois = $clients_actifs = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Parc Auto</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            background: #f5f7fb;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo h1 {
            color: var(--dark);
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-welcome {
            color: var(--dark);
        }

        .btn-logout {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-logout:hover {
            background: #c82333;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .section-title {
            margin-bottom: 20px;
            color: var(--dark);
            font-size: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: var(--light);
            border: 2px dashed #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: scale(1.05);
        }

        .action-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        /* Recent Activity */
        .recent-activity {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .activity-content {
            flex: 1;
        }

        .activity-time {
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <h1>🚗 Tableau de Bord</h1>
        </div>
        <div class="user-info">
            <span class="user-welcome">
                Bonjour, <strong><?php echo $_SESSION['agent_prenom'] . ' ' . $_SESSION['agent_nom']; ?></strong>
                (<?php echo $_SESSION['agent_role']; ?>)
            </span>
            <a href="deconnexion.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--success);">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-number"><?php echo $vehicules_disponibles; ?></div>
                <div class="stat-label">Véhicules Disponibles</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--primary);">
                    <i class="fas fa-key"></i>
                </div>
                <div class="stat-number"><?php echo $locations_cours; ?></div>
                <div class="stat-label">Locations en Cours</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--warning);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number"><?php echo $ventes_mois; ?></div>
                <div class="stat-label">Ventes ce Mois</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--secondary);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $clients_actifs; ?></div>
                <div class="stat-label">Clients Actifs</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="section-title">Actions Rapides</h2>
            <div class="actions-grid">
                <a href="nouvelle_location.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <span>Nouvelle Location</span>
                </a>
                
                <a href="nouvelle_vente.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <span>Nouvelle Vente</span>
                </a>
                
                <a href="ajout_vehicule.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <span>Ajouter Véhicule</span>
                </a>
                
                <a href="nouvel_employe.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <span>Nouvel Employé</span>
                </a>
                
                <a href="vehicules.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <span>Gestion Véhicules</span>
                </a>
                
                <a href="clients.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <span>Gestion Clients</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2 class="section-title">Activité Récente</h2>
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="activity-content">
                        <strong>Nouvelle location</strong> - Véhicule ABC-123
                    </div>
                    <div class="activity-time">Il y a 2 heures</div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="activity-content">
                        <strong>Vente finalisée</strong> - Client Dupont
                    </div>
                    <div class="activity-time">Il y a 5 heures</div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <div class="activity-content">
                        <strong>Nouveau véhicule ajouté</strong> - Tesla Model 3
                    </div>
                    <div class="activity-time">Il y a 1 jour</div>
                </li>
            </ul>
        </div>
    </div>

    <script>
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
</body>
</html>