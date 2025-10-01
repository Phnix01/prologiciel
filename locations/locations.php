<?php
session_start();
require_once 'config/database.php';

// Vérification authentification
if(!isset($_SESSION['agent_connecte'])) {
    header('Location: index.php');
    exit();
}

// Récupération des locations avec filtres
$filtre_statut = $_GET['statut'] ?? 'tous';
$filtre_type = $_GET['type'] ?? 'tous';

$sql = "SELECT c.*, cl.nom as client_nom, cl.prenom as client_prenom, 
               v.marque, v.modele, v.immatriculation,
               ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom,
               ve.nom as vendeur_nom, ve.prenom as vendeur_prenom
        FROM CONTRAT c
        LEFT JOIN CLIENT cl ON c.IdClient = cl.IdClient
        LEFT JOIN VEHICULE v ON c.IdVehicule = v.IdVehicule
        LEFT JOIN CHAUFFEUR ch ON c.IdChauffeur = ch.IdChauffeur
        LEFT JOIN VENDEUR ve ON c.IdVendeur = ve.IdVendeur
        WHERE c.type_contrat = 'location'";

$params = [];

if($filtre_statut !== 'tous') {
    if($filtre_statut === 'actives') {
        $sql .= " AND c.date_fin >= CURDATE()";
    } elseif($filtre_statut === 'terminees') {
        $sql .= " AND c.date_fin < CURDATE()";
    }
}

if($filtre_type !== 'tous') {
    $sql .= " AND v.type = ?";
    $params[] = $filtre_type;
}

$sql .= " ORDER BY c.date_contrat DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $locations = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Locations - Parc Auto</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles communs déjà définis dans dashboard.php */
        .header, .container { /* Reprendre les styles du dashboard */ }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 12px;
            color: #666;
        }
        
        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .statut-badge {
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .statut-active {
            background: #d4edda;
            color: #155724;
        }
        
        .statut-termine {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-view { background: #17a2b8; color: white; }
        .btn-edit { background: #ffc107; color: black; }
        .btn-delete { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 20px;">
            <h1>Gestion des Locations</h1>
            <a href="nouvelle_location.php" class="btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Location
            </a>
        </div>
        
        <!-- Filtres -->
        <div class="filters">
            <div class="filter-group">
                <label>Statut</label>
                <select onchange="window.location.href='?statut='+this.value">
                    <option value="tous" <?php echo $filtre_statut === 'tous' ? 'selected' : ''; ?>>Toutes les locations</option>
                    <option value="actives" <?php echo $filtre_statut === 'actives' ? 'selected' : ''; ?>>Locations actives</option>
                    <option value="terminees" <?php echo $filtre_statut === 'terminees' ? 'selected' : ''; ?>>Locations terminées</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Type véhicule</label>
                <select onchange="window.location.href='?type='+this.value">
                    <option value="tous" <?php echo $filtre_type === 'tous' ? 'selected' : ''; ?>>Tous types</option>
                    <option value="Berline" <?php echo $filtre_type === 'Berline' ? 'selected' : ''; ?>>Berline</option>
                    <option value="SUV" <?php echo $filtre_type === 'SUV' ? 'selected' : ''; ?>>SUV</option>
                    <option value="4x4" <?php echo $filtre_type === '4x4' ? 'selected' : ''; ?>>4x4</option>
                </select>
            </div>
        </div>
        
        <!-- Tableau des locations -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Période</th>
                        <th>Prix</th>
                        <th>Chauffeur</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($locations)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                Aucune location trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($locations as $location): ?>
                            <?php 
                            $est_active = strtotime($location['date_fin']) >= strtotime(date('Y-m-d'));
                            $statut_class = $est_active ? 'statut-active' : 'statut-termine';
                            $statut_text = $est_active ? 'Active' : 'Terminée';
                            ?>
                            <tr>
                                <td>#<?php echo $location['IdContrat']; ?></td>
                                <td><?php echo $location['client_prenom'] . ' ' . $location['client_nom']; ?></td>
                                <td>
                                    <?php echo $location['marque'] . ' ' . $location['modele']; ?>
                                    <br><small><?php echo $location['immatriculation']; ?></small>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($location['date_debut'])); ?>
                                    <br>au <?php echo date('d/m/Y', strtotime($location['date_fin'])); ?>
                                </td>
                                <td><?php echo number_format($location['prix'], 2, ',', ' '); ?> €</td>
                                <td><?php echo $location['chauffeur_prenom'] . ' ' . $location['chauffeur_nom']; ?></td>
                                <td>
                                    <span class="statut-badge <?php echo $statut_class; ?>">
                                        <?php echo $statut_text; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn-action btn-view" onclick="voirLocation(<?php echo $location['IdContrat']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editerLocation(<?php echo $location['IdContrat']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="supprimerLocation(<?php echo $location['IdContrat']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function voirLocation(id) {
            window.location.href = 'details_location.php?id=' + id;
        }
        
        function editerLocation(id) {
            if(confirm('Modifier cette location ?')) {
                window.location.href = 'modifier_location.php?id=' + id;
            }
        }
        
        function supprimerLocation(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer cette location ?')) {
                window.location.href = 'supprimer_location.php?id=' + id;
            }
        }
    </script>
</body>
</html>