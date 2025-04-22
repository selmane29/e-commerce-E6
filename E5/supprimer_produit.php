<?php
// Connexion à la base de données
$host = "localhost";
$user = "root";  // À modifier selon vos identifiants
$password = "";  // À modifier selon votre mot de passe
$dbname = "informatique";

$conn = new mysqli($host, $user, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}

// Initialiser les variables
$id = "";
$message = "";
$errorMessage = "";

// Vérifier si un ID est fourni dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Vérifier si le produit existe
    $sql = "SELECT * FROM produit WHERE produit_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Vérifier si la confirmation est requise ou si la suppression est confirmée
        if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
            // Vérifier s'il y a des références dans d'autres tables
            $check_panier = "SELECT COUNT(*) as count FROM panier WHERE produit_id = ?";
            $check_commande = "SELECT COUNT(*) as count FROM commande_produit WHERE produit_id = ?";
            
            $stmt_panier = $conn->prepare($check_panier);
            $stmt_panier->bind_param("i", $id);
            $stmt_panier->execute();
            $result_panier = $stmt_panier->get_result();
            $panier_count = $result_panier->fetch_assoc()['count'];
            
            $stmt_commande = $conn->prepare($check_commande);
            $stmt_commande->bind_param("i", $id);
            $stmt_commande->execute();
            $result_commande = $stmt_commande->get_result();
            $commande_count = $result_commande->fetch_assoc()['count'];
            
            // Si le produit est référencé dans d'autres tables, afficher un avertissement
            if ($panier_count > 0 || $commande_count > 0) {
                $errorMessage = "Ce produit est présent dans des paniers ou des commandes. La suppression pourrait causer des erreurs.";
            } else {
                // Supprimer le produit
                $delete_sql = "DELETE FROM produit WHERE produit_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $id);
                
                if ($delete_stmt->execute()) {
                    // Supprimer l'image associée si elle existe et n'est pas utilisée par d'autres produits
                    $image_path = $product['image'];
                    if (!empty($image_path) && file_exists($image_path)) {
                        // Vérifier si l'image est utilisée par d'autres produits
                        $check_image = "SELECT COUNT(*) as count FROM produit WHERE image = ? AND produit_id != ?";
                        $stmt_image = $conn->prepare($check_image);
                        $stmt_image->bind_param("si", $image_path, $id);
                        $stmt_image->execute();
                        $result_image = $stmt_image->get_result();
                        $image_count = $result_image->fetch_assoc()['count'];
                        
                        if ($image_count == 0) {
                            unlink($image_path); // Supprimer le fichier image
                        }
                    }
                    
                    $message = "Produit supprimé avec succès";
                    // Rediriger vers la page d'administration après 2 secondes
                    header("refresh:2;url=admin_produits.php");
                } else {
                    $errorMessage = "Erreur lors de la suppression du produit: " . $conn->error;
                }
            }
        }
    } else {
        $errorMessage = "Produit non trouvé";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un produit</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        h1 {
            color: #333;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .product-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            text-align: left;
        }
        .product-image {
            max-width: 150px;
            max-height: 150px;
            margin: 10px auto;
            display: block;
        }
        .buttons {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 5px;
            display: inline-block;
            text-decoration: none;
        }
        .btn-danger {
            background-color: #F44336;
            color: white;
        }
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        .message {
            padding: 10px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <h1>Supprimer un produit</h1>
    </header>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if (isset($product) && !isset($_GET['confirm'])): ?>
            <h2>Êtes-vous sûr de vouloir supprimer ce produit ?</h2>
            
            <div class="product-info">
                <p><strong>ID:</strong> <?php echo $product['produit_id']; ?></p>
                <p><strong>Nom:</strong> <?php echo $product['nom']; ?></p>
                <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
                <p><strong>Prix:</strong> <?php echo $product['prix']; ?> €</p>
                
                <?php if (!empty($product['image'])): ?>
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['nom']; ?>" class="product-image">
                <?php endif; ?>
            </div>
            
            <div class="buttons">
                <a href="supprimer_produit.php?id=<?php echo $id; ?>&confirm=yes" class="btn btn-danger">Confirmer la suppression</a>
                <a href="admin_produits.php" class="btn btn-secondary">Annuler</a>
            </div>
        <?php elseif (empty($errorMessage) && empty($product)): ?>
            <p>Redirection vers la page d'administration...</p>
            <div class="buttons">
                <a href="admin_produits.php" class="btn btn-secondary">Retour à la liste</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>