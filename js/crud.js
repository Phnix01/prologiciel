// Gestion du CRUD clients
function openModal(clientId = null) {
    const modal = document.getElementById('clientModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('clientForm');
    
    if (clientId) {
        // Mode édition - Charger les données via AJAX
        modalTitle.textContent = 'Modifier le Client';
        loadClientData(clientId);
    } else {
        // Mode création - Réinitialiser le formulaire
        modalTitle.textContent = 'Nouveau Client';
        form.reset();
        document.getElementById('IdClient').value = '';
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('clientModal').style.display = 'none';
}

function loadClientData(clientId) {
    fetch(`api/get_client.php?id=${clientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('IdClient').value = data.client.IdClient;
                document.getElementById('nom').value = data.client.nom;
                document.getElementById('prenom').value = data.client.prenom;
                document.getElementById('telephone').value = data.client.telephone;
                document.getElementById('email').value = data.client.email;
                document.getElementById('ID_Carte').value = data.client.ID_Carte;
                document.getElementById('adresse').value = data.client.adresse;
            } else {
                alert('Erreur lors du chargement des données');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur de connexion');
        });
}

function confirmDelete(clientId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.')) {
        window.location.href = `clients.php?delete_id=${clientId}`;
    }
}

// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const table = document.getElementById('clientsTable');
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < td.length; j++) {
            if (td[j] && td[j].textContent.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
});

// Validation du formulaire
document.getElementById('clientForm').addEventListener('submit', function(e) {
    const telephone = document.getElementById('telephone').value;
    const email = document.getElementById('email').value;
    
    // Validation téléphone
    if (!/^[\+]?[0-9\s\-\(\)]{10,}$/.test(telephone)) {
        alert('Numéro de téléphone invalide');
        e.preventDefault();
        return;
    }
    
    // Validation email si renseigné
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Adresse email invalide');
        e.preventDefault();
        return;
    }
});

// Fermer la modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('clientModal');
    if (event.target === modal) {
        closeModal();
    }
}