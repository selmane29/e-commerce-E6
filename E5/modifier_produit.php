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
$nom = "";
$description = "";
$prix = "";
$stock = "";
$image = "";
$script = "";
$message = "";
$errorMessage = "";

// Vérifier si un ID est fourni dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer les informations du produit
    $sql = "SELECT * FROM produit WHERE produit_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $nom = $product['nom'];
        $description = $product['description'];
        $prix = $product['prix'];
        $stock = $product['stock'];
        $image = $product['image'];
        $script = $product['script'];
    } else {
        $errorMessage = "Produit non trouvé";
    }
    $stmt->close();
}

// Traiter le formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $id = $_POST['produit_id'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $stock = $_POST['stock'];
    $script = $_POST['script'];
    
    // Vérifier si une nouvelle image a été téléchargée
    $image_path = $_POST['current_image']; // Par défaut, conserver l'image actuelle
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Vérifier le type de fichier
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
        
        // Vérifier l'extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (array_key_exists($ext, $allowed)) {
            // Vérifier la taille du fichier - max 5MB
            $maxsize = 5 * 1024 * 1024;
            if ($filesize < $maxsize) {
                // Créer un nom de fichier unique
                $new_filename = "img/" . uniqid() . "." . $ext;
                
                // Déplacer le fichier vers le dossier des images
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $new_filename)) {
                    $image_path = $new_filename;
                } else {
                    $errorMessage = "Erreur lors du téléchargement de l'image.";
                }
            } else {
                $errorMessage = "L'image est trop grande. Max 5MB.";
            }
        } else {
            $errorMessage = "Type de fichier non autorisé. Seuls JPG, JPEG, PNG & GIF sont acceptés.";
        }
    }
    
    // Si aucune erreur, mettre à jour le produit
    if (empty($errorMessage)) {
        $sql = "UPDATE produit SET nom = ?, description = ?, prix = ?, stock = ?, image = ?, script = ? WHERE produit_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdissi", $nom, $description, $prix, $stock, $image_path, $script, $id);
        
        if ($stmt->execute()) {
            $message = "Produit mis à jour avec succès";
            // Rediriger vers la page d'administration après 2 secondes
            header("refresh:2;url=admin_produits.php");
        } else {
            $errorMessage = "Erreur lors de la mise à jour du produit: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un produit</title>
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
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            height: 120px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
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
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #4CAF50;
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
        <h1>Modifier un produit</h1>
    </header>

    <div class="form-container">
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" enctype="multipart/form-data">
            <input type="hidden" name="produit_id" value="<?php echo $id; ?>">
            <input type="hidden" name="current_image" value="<?php echo $image; ?>">
            
            <div class="form-group">
                <label for="nom">Nom du produit</label>
                <input type="text" id="nom" name="nom" value="<?php echo $nom; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo $description; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="prix">Prix (€)</label>
                <input type="number" id="prix" name="prix" step="0.01" value="<?php echo $prix; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" value="<?php echo $stock; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="image">Image actuelle</label>
                <?php if (!empty($image)): ?>
                    <img src="<?php echo $image; ?>" alt="<?php echo $nom; ?>" class="preview-image">
                <?php else: ?>
                    <p>Aucune image</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image">Nouvelle image (laisser vide pour conserver l'actuelle)</label>
                <input type="file" id="image" name="image">
            </div>
            
            <div class="form-group">
                <label for="script">Script (description détaillée)</label>
                <textarea id="script" name="script"><?php echo $script; ?></textarea>
            </div>
            
            <div class="buttons">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="admin_produits.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>