<div class="location-form">
    <!-- Étape 1: Sélection du client -->
    <div class="step" id="step-client">
        <h3>1. Sélection du Client</h3>
        <input type="text" id="search-client" placeholder="Rechercher un client...">
        <div id="results-client"></div>
        
        <!-- Ou création rapide -->
        <button type="button" onclick="showNewClientForm()">Nouveau Client</button>
        
        <div id="new-client-form" style="display:none;">
            <input type="text" name="new_nom" placeholder="Nom">
            <input type="text" name="new_prenom" placeholder="Prénom">
            <input type="tel" name="new_telephone" placeholder="Téléphone">
            <input type="email" name="new_email" placeholder="Email">
        </div>
    </div>
    
    <!-- Étape 2: Sélection du véhicule -->
    <div class="step" id="step-vehicule">
        <h3>2. Choix du Véhicule</h3>
        <select name="id_vehicule" required onchange="updatePrixLocation(this.value)">
            <option value="">Sélectionnez un véhicule</option>
            <?php
            $vehicules = $db->query("SELECT v.IdVehicule, v.marque, v.modele, v.tarif_location 
                                   FROM VEHICULE v 
                                   WHERE v.statut = 'disponible'");
            while($v = $vehicules->fetch()) {
                echo "<option value='{$v['IdVehicule']}' data-prix='{$v['tarif_location']}'>
                        {$v['marque']} {$v['modele']} - {$v['tarif_location']}€/jour
                      </option>";
            }
            ?>
        </select>
    </div>
    
    <!-- Étape 3: Période de location -->
    <div class="step" id="step-dates">
        <h3>3. Période de Location</h3>
        <input type="date" name="date_debut" required onchange="calculerPrixTotal()">
        <input type="date" name="date_fin" required onchange="calculerPrixTotal()">
        <input type="time" name="heure_debut" value="08:00">
        <input type="time" name="heure_fin" value="18:00">
        
        <div id="prix-total">
            <strong>Prix total: <span id="montant-total">0</span>€</strong>
        </div>
    </div>
    
    <!-- Étape 4: Sélection chauffeur -->
    <div class="step" id="step-chauffeur">
        <h3>4. Attribution d'un Chauffeur</h3>
        <select name="id_chauffeur" required>
            <option value="">Choisir un chauffeur</option>
            <?php
            $chauffeurs = $db->query("SELECT IdChauffeur, nom, prenom FROM CHAUFFEUR WHERE disponible = 1");
            while($ch = $chauffeurs->fetch()) {
                echo "<option value='{$ch['IdChauffeur']}'>{$ch['prenom']} {$ch['nom']}</option>";
            }
            ?>
        </select>
    </div>
    
    <button type="button" onclick="validerLocation()">Créer le Contrat de Location</button>
</div>

<script>
function calculerPrixTotal() {
    const dateDebut = new Date(document.querySelector('[name="date_debut"]').value);
    const dateFin = new Date(document.querySelector('[name="date_fin"]').value);
    const prixJournalier = document.querySelector('[name="id_vehicule"]').selectedOptions[0]?.dataset.prix || 0;
    
    if(dateDebut && dateFin && prixJournalier) {
        const diffTime = Math.abs(dateFin - dateDebut);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        const total = diffDays * prixJournalier;
        
        document.getElementById('montant-total').textContent = total;
    }
}

function validerLocation() {
    // Validation complète avant soumission
    const formData = new FormData();
    // Récupération de toutes les données...
    
    fetch('traitement_location.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            window.location.href = 'contrat_location.php?id=' + data.contrat_id;
        }
    });
}
</script>