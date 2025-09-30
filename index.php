<?php
session_start();
// Redirection si déjà connecté
if(isset($_SESSION['agent_connecte'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Parc Auto</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #fcc;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🚗 Parc Auto Pro</h1>
            <p>Gestion des locations et ventes</p>
        </div>

        <!-- Message d'erreur -->
        <div id="errorMessage" class="error-message <?php echo isset($_GET['error']) ? 'show' : ''; ?>">
            ❌ Veuillez entrer des informations correctes
        </div>

        <form action="traitement_login.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="matricule">Matricule Agent</label>
                <input type="text" id="matricule" name="matricule" required 
                       placeholder="Entrez votre matricule">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Entrez votre mot de passe">
            </div>

            <button type="submit" class="btn-login">Se connecter</button>
        </form>

        <div class="footer-links">
            <p>Version 1.0 • © 2024 Parc Auto</p>
        </div>
    </div>

    <script>
        // Animation du message d'erreur
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const matricule = document.getElementById('matricule').value;
            const password = document.getElementById('password').value;
            
            if(!matricule || !password) {
                e.preventDefault();
                showError('Veuillez remplir tous les champs');
            }
        });

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = '❌ ' + message;
            errorDiv.classList.add('show');
            
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }

        // Effacer l'erreur quand l'utilisateur commence à taper
        document.getElementById('matricule').addEventListener('input', hideError);
        document.getElementById('password').addEventListener('input', hideError);

        function hideError() {
            document.getElementById('errorMessage').classList.remove('show');
        }
    </script>
</body>
</html>