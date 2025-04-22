<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
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
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1>Connexion</h1>

        <?php
        // Traitement du formulaire lorsqu'il est soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Initialisation des messages d'erreur
            $errors = [];

            // Vérification de la complétion de tous les champs
            if (empty($username)) {
                $errors[] = "Le nom d'utilisateur est requis.";
            }
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis.";
            }

            // Si aucun message d'erreur, continuer avec la vérification
            if (empty($errors)) {
                // Connexion à la base de données
                $servername = "localhost";
                $dbUsername = "root";
                $dbPassword = "";
                $dbname = "informatique";

                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Requête SQL pour vérifier les informations de connexion
                    $stmt = $conn->prepare("SELECT * FROM client WHERE nom = :nom");
                    $stmt->bindParam(':nom', $username);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Vérification du mot de passe
                    if ($user && password_verify($password, $user['mot_de_passe'])) {
                        $_SESSION['username'] = $username; // Stocker le nom d'utilisateur dans la session
                        header("Location: Stage.php"); // Redirection vers la page d'accueil après connexion
                        exit();
                    } else {
                        $errors[] = "Nom d'utilisateur ou mot de passe incorrect.";
                    }
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

        <form action="connection.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur :</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>

    <?php include('footers.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>