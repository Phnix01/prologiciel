<?php
session_start();
require_once 'config/database.php';

// Vérification authentification
if(!isset($_SESSION['agent_connecte'])) {
    header('Location: index.php');
    exit();
}

// Récupération des véhicules avec filtres
$filtre_statut = $_GET['statut'] ?? 'tous';
$filtre_type = $_GET['type'] ?? 'tous';
$recherche = $_GET['recherche'] ?? '';

$sql = "SELECT v.*, p.nom as parc_nom 
        FROM VEHICULE v 
        LEFT JOIN PARCAUTO p ON v.IdParc = p.IdParc 
        WHERE 1=1";
$params = [];

if($filtre_statut !== 'tous') {
    $sql .= " AND v.statut = ?";
    $params[] = $filtre_statut;
}

if($filtre_type !== 'tous') {
    $sql .= " AND v.type = ?";
    $params[] = $filtre_type;
}

if(!empty($recherche)) {
    $sql .= " AND (v.immatriculation LIKE ? OR v.marque LIKE ? OR v.modele LIKE ?)";
    $search_term = "%$recherche%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY v.IdVehicule DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $vehicules = [];
}

// Statistiques
$stats = [
    'total' => 0,
    'disponibles' => 0,
    'loues' => 0,
    'vendu' => 0
];

foreach($vehicules as $v) {
    $stats['total']++;
    $stats[$v['statut']]++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules - Parc Auto</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-vehicules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-vehicule {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid var(--primary);
        }
        
        .stat-vehicule.disponible { border-left-color: var(--success); }
        .stat-vehicule.loue { border-left-color: var(--warning); }
        .stat-vehicule.vendu { border-left-color: var(--danger); }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .vehicule-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .vehicule-image {
            width: 120px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #666;
        }
        
        .vehicule-info {
            flex: 1;
        }
        
        .vehicule-titre {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .vehicule-details {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .vehicule-prix {
            font-weight: 600;
            color: var(--success);
        }
        
        .vehicule-actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 20px;">
            <h1>Gestion des Véhicules</h1>
            <a href="ajout_vehicule.php" class="btn-primary">
                <i class="fas fa-plus"></i> Ajouter Véhicule
            </a>
        </div>
        
        <!-- Statistiques -->
        <div class="stats-vehicules">
            <div class="stat-vehicule">
                <div style="font-size: 24px; font-weight: bold;"><?php echo $stats['total']; ?></div>
                <div>Total</div>
            </div>
            <div class="stat-vehicule disponible">
                <div style="font-size: 24px; font-weight: bold; color: var(--success);"><?php echo $stats['disponibles']; ?></div>
                <div>Disponibles</div>
            </div>
            <div class="stat-vehicule loue">
                <div style="font-size: 24px; font-weight: bold; color: var(--warning);"><?php echo $stats['loues']; ?></div>
                <div>Loués</div>
            </div>
            <div class="stat-vehicule vendu">
                <div style="font-size: 24px; font-weight: bold; color: var(--danger);"><?php echo $stats['vendu']; ?></div>
                <div>Vendus</div>
            </div>
        </div>
        
        <!-- Recherche et filtres -->
        <div class="search-box">
            <form method="GET" class="search-form">
                <input type="text" name="recherche" value="<?php echo htmlspecialchars($recherche); ?>" 
                       placeholder="Rechercher par immatriculation, marque, modèle..." class="search-input">
                <select name="statut" onchange="this.form.submit()">
                    <option value="tous" <?php echo $filtre_statut === 'tous' ? 'selected' : ''; ?>>Tous statuts</option>
                    <option value="disponible" <?php echo $filtre_statut === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="loué" <?php echo $filtre_statut === 'loué' ? 'selected' : ''; ?>>Loué</option>
                    <option value="vendu" <?php echo $filtre_statut === 'vendu' ? 'selected' : ''; ?>>Vendu</option>
                </select>
                <select name="type" onchange="this.form.submit()">
                    <option value="tous" <?php echo $filtre_type === 'tous' ? 'selected' : ''; ?>>Tous types</option>
                    <option value="Berline" <?php echo $filtre_type === 'Berline' ? 'selected' : ''; ?>>Berline</option>
                    <option value="SUV" <?php echo $filtre_type === 'SUV' ? 'selected' : ''; ?>>SUV</option>
                    <option value="4x4" <?php echo $filtre_type === '4x4' ? 'selected' : ''; ?>>4x4</option>
                </select>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </form>
        </div>
        
        <!-- Liste des véhicules -->
        <div>
            <?php if(empty($vehicules)): ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                    <i class="fas fa-car" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucun véhicule trouvé</p>
                </div>
            <?php else: ?>
                <?php foreach($vehicules as $vehicule): ?>
                    <div class="vehicule-card">
                        <div class="vehicule-image">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="vehicule-info">
                            <div class="vehicule-titre">
                                <?php echo $vehicule['marque'] . ' ' . $vehicule['modele']; ?>
                                <span class="statut-badge <?php echo 'statut-' . $vehicule['statut']; ?>" 
                                      style="margin-left: 10px; font-size: 12px;">
                                    <?php echo ucfirst($vehicule['statut']); ?>
                                </span>
                            </div>
                            <div class="vehicule-details">
                                <strong>Immatriculation:</strong> <?php echo $vehicule['immatriculation']; ?> |
                                <strong>Type:</strong> <?php echo $vehicule['type']; ?> |
                                <strong>Carburant:</strong> <?php echo $vehicule['carburant']; ?> |
                                <strong>Parc:</strong> <?php echo $vehicule['parc_nom']; ?>
                            </div>
                            <div class="vehicule-details">
                                <strong>Mise en circulation:</strong> <?php echo date('d/m/Y', strtotime($vehicule['date_mise_circulation'])); ?> |
                                <strong>Puissance:</strong> <?php echo $vehicule['puissance']; ?> CV
                            </div>
                            <div>
                                <?php if($vehicule['prix_vente'] > 0): ?>
                                    <span class="vehicule-prix">Vente: <?php echo number_format($vehicule['prix_vente'], 2, ',', ' '); ?> €</span>
                                <?php endif; ?>
                                <?php if($vehicule['tarif_location'] > 0): ?>
                                    <span class="vehicule-prix" style="margin-left: 15px;">Location: <?php echo number_format($vehicule['tarif_location'], 2, ',', ' '); ?> €/jour</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="vehicule-actions">
                            <button class="btn-action btn-view" onclick="voirVehicule(<?php echo $vehicule['IdVehicule']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-action btn-edit" onclick="editerVehicule(<?php echo $vehicule['IdVehicule']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="supprimerVehicule(<?php echo $vehicule['IdVehicule']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function voirVehicule(id) {
            window.location.href = 'details_vehicule.php?id=' + id;
        }
        
        function editerVehicule(id) {
            window.location.href = 'modifier_vehicule.php?id=' + id;
        }
        
        function supprimerVehicule(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ? Cette action est irréversible.')) {
                window.location.href = 'supprimer_vehicule.php?id=' + id;
            }
        }
    </script>
</body>
</html>