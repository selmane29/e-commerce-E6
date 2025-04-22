<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "informatique";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname",$username, $password);
    // Configuration de PDO pour générer une exception en cas d'erreur
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des données du formulaire
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hacher le mot de passe
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Préparation de la requête SQL
    $stmt = $conn->prepare("INSERT INTO client (nom, email, mot_de_passe) VALUES
    (:nom, :email, :password)");

    // Liaison des paramètres
    $stmt->bindParam(':nom', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password_hash); // Utilisation du mot de passe haché

    // Exécution de la requête
    $stmt->execute();

    // Redirection vers la page connexion.php
    header("Location: connection.php");
    exit(); // Assure que le script s'arrête ici pour éviter toute exécution supplémentaire

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

// Fermeture de la connexion à la base de données
$conn = null;
?>
