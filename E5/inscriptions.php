<?php
session_start();
$registrationSuccess = false; // Initialisation de la variable de succès

// Définir le fuseau horaire sur la France
date_default_timezone_set('Europe/Paris');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Custom button style */
        .btn-primary {
            background-color: #028a0f;
            border-color: #028a0f;
        }

        .btn-primary:hover {
            background-color: #026f0c;
            border-color: #026f0c;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1>Inscription</h1>

        <?php
        // Traitement du formulaire lorsqu'il est soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupération des données du formulaire
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm-password'] ?? '';

            // Initialisation des messages d'erreur
            $errors = [];

            // Vérification de la complétion de tous les champs
            if (empty($username)) {
                $errors[] = "Le nom d'utilisateur est requis.";
            }
            if (empty($email)) {
                $errors[] = "L'email est requis.";
            }
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis.";
            }
            if (empty($confirmPassword) || $password !== $confirmPassword) {
                $errors[] = "Les mots de passe doivent être identiques.";
            }

            // Vérification des règles de complexité du mot de passe
            if (!empty($password) && (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[@$%*]/', $password))) {
                $errors[] = "Le mot de passe doit comporter au moins 8 caractères, une majuscule et un caractère spécial (@, $, %, *).";
            }

            // Vérification du format de l'email
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email n'est pas valide.";
            }

            // Si aucun message d'erreur, continuer avec l'enregistrement
            if (empty($errors)) {
                // Connexion à la base de données
                $servername = "localhost";
                $dbUsername = "root";
                $dbPassword = "";
                $dbname = "informatique";

                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Hacher le mot de passe
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Générer un reset_token et une date d'expiration (par exemple 24h après l'inscription)
                    $reset_token = bin2hex(random_bytes(16)); // Génération d'un token aléatoire
                    $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours')); // Date

                    // Préparation de la requête SQL
                    $stmt = $conn->prepare("INSERT INTO client (nom, email, mot_de_passe, reset_token, reset_token_expiry) VALUES (:nom, :email, :password, :reset_token, :reset_token_expiry)");

                    // Liaison des paramètres
                    $stmt->bindParam(':nom', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $password_hash);
                    $stmt->bindParam(':reset_token', $reset_token);
                    $stmt->bindParam(':reset_token_expiry', $reset_token_expiry);

                    // Exécution de la requête
                    $stmt->execute();

                    // Définir la variable de succès
                    $registrationSuccess = true;

                    // Redirection vers la page connexion.php après l'inscription réussie
                    header("Location: connection.php");
                    exit();
                    
                } catch(PDOException $e) {
                    $errors[] = "Erreur : " . $e->getMessage();
                }
                $conn = null;
            }

            // Affichage des messages d'erreur
            if (!empty($errors)) {
                echo '<div class="alert alert-danger"><ul>';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul></div>';
            }
        }
        ?>

        <form action="inscriptions.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Nom :</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm-password" class="form-label">Confirmer le mot de passe :</label>
                <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
            </div>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        <p id="error-msg"></p>
    </div>

    <script>
        function validateForm() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm-password").value;
            var errorMsg = document.getElementById("error-msg");

            if (password !== confirmPassword) {
                errorMsg.textContent = "Les mots de passe ne correspondent pas.";
                errorMsg.style.color = "red";
                return false; 
            }
            return true;
        }
    </script>

    <?php include('footers.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>