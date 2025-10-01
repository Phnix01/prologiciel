<?php
session_start();
require_once 'config/database.php';

// Vérification authentification
if(!isset($_SESSION['agent_connecte'])) {
    header('Location: index.php');
    exit();
}

// Récupération des employés par type
$type_employe = $_GET['type'] ?? 'tous';

$sql = "SELECT 'vendeur' as type, IdVendeur as id, nom, prenom, matricule, telephone, salaire_fixe, 'Vendeur' as role 
        FROM VENDEUR 
        WHERE 1=1";
        
if($type_employe === 'chauffeurs') {
    $sql = "SELECT 'chauffeur' as type, IdChauffeur as id, nom, prenom, matricule, telephone, '' as salaire_fixe, 'Chauffeur' as role 
            FROM CHAUFFEUR 
            WHERE 1=1";
} elseif($type_employe === 'huissiers') {
    $sql = "SELECT 'huissier' as type, IdHuissier as id, nom, prenom, '' as matricule, '' as telephone, '' as salaire_fixe, 'Huissier' as role 
            FROM HUISSIER 
            WHERE 1=1";
} elseif($type_employe === 'tous') {
    $sql = "SELECT 'vendeur' as type, IdVendeur as id, nom, prenom, matricule, telephone, salaire_fixe, 'Vendeur' as role 
            FROM VENDEUR 
            UNION ALL
            SELECT 'chauffeur' as type, IdChauffeur as id, nom, prenom, matricule, telephone, '' as salaire_fixe, 'Chauffeur' as role 
            FROM CHAUFFEUR 
            UNION ALL
            SELECT 'huissier' as type, IdHuissier as id, nom, prenom, '' as matricule, '' as telephone, '' as salaire_fixe, 'Huissier' as role 
            FROM HUISSIER";
}

$sql .= " ORDER BY nom, prenom";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $employes = [];
}

// Statistiques
$stats = [
    'total' => 0,
    'vendeurs' => 0,
    'chauffeurs' => 0,
    'huissiers' => 0
];

foreach($employes as $e) {
    $stats['total']++;
    $stats[$e['type'] . 's']++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Employés - Parc Auto</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-employes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-employe {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .stat-employe:hover {
            transform: translateY(-2px);
        }
        
        .stat-employe.active {
            border: 2px solid var(--primary);
        }
        
        .employe-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .employe-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .employe-info {
            flex: 1;
        }
        
        .employe-nom {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .employe-details {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }
        
        .role-vendeur { background: #d4edda; color: #155724; }
        .role-chauffeur { background: #cce7ff; color: #004085; }
        .role-huissier { background: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 20px;">
            <h1>Gestion des Employés</h1>
            <a href="nouvel_employe.php" class="btn-primary">
                <i class="fas fa-user-plus"></i> Nouvel Employé
            </a>
        </div>
        
        <!-- Statistiques -->
        <div class="stats-employes">
            <div class="stat-employe <?php echo $type_employe === 'tous' ? 'active' : ''; ?>" 
                 onclick="window.location.href='?type=tous'">
                <div style="font-size: 24px; font-weight: bold;"><?php echo $stats['total']; ?></div>
                <div>Tous</div>
            </div>
            <div class="stat-employe <?php echo $type_employe === 'vendeurs' ? 'active' : ''; ?>" 
                 onclick="window.location.href='?type=vendeurs'">
                <div style="font-size: 24px; font-weight: bold; color: var(--success);"><?php echo $stats['vendeurs']; ?></div>
                <div>Vendeurs</div>
            </div>
            <div class="stat-employe <?php echo $type_employe === 'chauffeurs' ? 'active' : ''; ?>" 
                 onclick="window.location.href='?type=chauffeurs'">
                <div style="font-size: 24px; font-weight: bold; color: var(--primary);"><?php echo $stats['chauffeurs']; ?></div>
                <div>Chauffeurs</div>
            </div>
            <div class="stat-employe <?php echo $type_employe === 'huissiers' ? 'active' : ''; ?>" 
                 onclick="window.location.href='?type=huissiers'">
                <div style="font-size: 24px; font-weight: bold; color: #666;"><?php echo $stats['huissiers']; ?></div>
                <div>Huissiers</div>
            </div>
        </div>
        
        <!-- Liste des employés -->
        <div>
            <?php if(empty($employes)): ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                    <i class="fas fa-users" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucun employé trouvé</p>
                </div>
            <?php else: ?>
                <?php foreach($employes as $employe): ?>
                    <div class="employe-card">
                        <div class="employe-avatar">
                            <?php echo strtoupper(substr($employe['prenom'], 0, 1) . substr($employe['nom'], 0, 1)); ?>
                        </div>
                        <div class="employe-info">
                            <div class="employe-nom">
                                <?php echo $employe['prenom'] . ' ' . $employe['nom']; ?>
                                <span class="role-badge role-<?php echo $employe['type']; ?>">
                                    <?php echo $employe['role']; ?>
                                </span>
                            </div>
                            <div class="employe-details">
                                <?php if(!empty($employe['matricule'])): ?>
                                    <strong>Matricule:</strong> <?php echo $employe['matricule']; ?> |
                                <?php endif; ?>
                                <?php if(!empty($employe['telephone'])): ?>
                                    <strong>Téléphone:</strong> <?php echo $employe['telephone']; ?> |
                                <?php endif; ?>
                                <?php if(!empty($employe['salaire_fixe'])): ?>
                                    <strong>Salaire:</strong> <?php echo number_format($employe['salaire_fixe'], 2, ',', ' '); ?> €
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="vehicule-actions">
                            <button class="btn-action btn-view" onclick="voirEmploye('<?php echo $employe['type']; ?>', <?php echo $employe['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-action btn-edit" onclick="editerEmploye('<?php echo $employe['type']; ?>', <?php echo $employe['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="supprimerEmploye('<?php echo $employe['type']; ?>', <?php echo $employe['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function voirEmploye(type, id) {
            window.location.href = 'details_employe.php?type=' + type + '&id=' + id;
        }
        
        function editerEmploye(type, id) {
            window.location.href = 'modifier_employe.php?type=' + type + '&id=' + id;
        }
        
        function supprimerEmploye(type, id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer cet employé ?')) {
                window.location.href = 'supprimer_employe.php?type=' + type + '&id=' + id;
            }
        }
    </script>
</body>
</html>