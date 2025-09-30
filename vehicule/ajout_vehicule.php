<form action="traitement_vehicule.php" method="POST" enctype="multipart/form-data">
    <!-- Informations de base -->
    <input type="text" name="immatriculation" placeholder="Immatriculation" required>
    <input type="text" name="marque" placeholder="Marque" required>
    <input type="text" name="modele" placeholder="Modèle" required>
    
    <!-- Sélection du type avec validation -->
    <select name="type_vehicule" required>
        <option value="Berline">Berline</option>
        <option value="SUV">SUV</option>
        <option value="4x4">4x4</option>
        <option value="Utilitaire">Utilitaire</option>
    </select>
    
    <!-- Informations techniques -->
    <input type="number" name="puissance" placeholder="Puissance (CV)" min="1">
    <input type="number" name="nombre_places" placeholder="Nombre de places" min="1">
    <input type="number" name="nombre_portieres" placeholder="Nombre de portières" min="1">
    
    <!-- Dates importantes -->
    <input type="date" name="date_mise_circulation" required>
    
    <!-- Carburant -->
    <select name="carburant" required>
        <option value="Essence">Essence</option>
        <option value="Diesel">Diesel</option>
        <option value="Electrique">Électrique</option>
        <option value="Hybride">Hybride</option>
    </select>
    
    <!-- Prix et statut -->
    <input type="number" name="prix_vente" placeholder="Prix de vente" step="0.01" min="0">
    <input type="number" name="tarif_location" placeholder="Tarif location/jour" step="0.01" min="0">
    
    <!-- Sélection du parc -->
    <select name="id_parc" required>
        <?php
        // Récupération des parcs depuis la base
        $parcs = $db->query("SELECT IdParc, nom FROM PARCAUTO");
        while($parc = $parcs->fetch()) {
            echo "<option value='{$parc['IdParc']}'>{$parc['nom']}</option>";
        }
        ?>
    </select>
    
    <button type="submit">Ajouter le véhicule</button>
</form>