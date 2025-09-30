<?php
// Validation et insertion respectant le modèle
if($_POST) {
    $data = [
        'immatriculation' => htmlspecialchars($_POST['immatriculation']),
        'marque' => htmlspecialchars($_POST['marque']),
        'type' => htmlspecialchars($_POST['type_vehicule']),
        'puissance' => intval($_POST['puissance']),
        'nombre_places' => intval($_POST['nombre_places']),
        'date_mise_circulation' => $_POST['date_mise_circulation'],
        'carburant' => $_POST['carburant'],
        'id_parc' => intval($_POST['id_parc']),
        'prix_vente' => floatval($_POST['prix_vente']),
        'tarif_location' => floatval($_POST['tarif_location'])
    ];
    
    // Insertion avec gestion d'erreurs
    try {
        $sql = "INSERT INTO VEHICULE (immatriculation, marque, type, puissance, nombre_places, date_mise_circulation, carburant, IdParc) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_values($data));
        
        header("Location: vehicules.php?success=1");
    } catch(PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>