<?php
header('Content-Type: application/json');
include '../config/database.php';
include '../models/Client.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    $client = new Client($db);
    
    $client->IdClient = $_GET['id'];
    
    if ($client->readOne()) {
        echo json_encode([
            'success' => true,
            'client' => [
                'IdClient' => $client->IdClient,
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'adresse' => $client->adresse,
                'telephone' => $client->telephone,
                'email' => $client->email,
                'ID_Carte' => $client->ID_Carte
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Client non trouvé']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
}
?>