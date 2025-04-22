<?php
// Configuration de la base de données
include 'bdd.php'; // s'assurer que ce fichier contient les variables $servername, $dbname, $dbusername, $dbpassword

// Initialisation des variables
$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nom = '';
$email = '';
$adresses = [];
$message = '';
$error = '';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données du formulaire
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        
        // Validation des données
        if (empty($nom)) {
            $error = 'Le nom est requis';
        } elseif (empty($email)) {
            $error = 'L\'email est requis';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'L\'email n\'est pas valide';
        } else {
            // Vérifier si l'email existe déjà pour un autre client
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM client WHERE email = :email AND client_id != :client_id");
            $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $checkStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = 'Cet email est déjà utilisé par un autre client';
            } else {
                // Début de la transaction
                $pdo->beginTransaction();
                
                // Mettre à jour les informations du client
                if (!empty($new_password)) {
                    // Si un nouveau mot de passe est fourni, le hasher et le mettre à jour
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $updateClient = $pdo->prepare("UPDATE client SET nom = :nom, email = :email, mot_de_passe = :mot_de_passe WHERE client_id = :client_id");
                    $updateClient->bindParam(':mot_de_passe', $hashed_password, PDO::PARAM_STR);
                } else {
                    // Sinon, mettre à jour uniquement le nom et l'email
                    $updateClient = $pdo->prepare("UPDATE client SET nom = :nom, email = :email WHERE client_id = :client_id");
                }
                
                $updateClient->bindParam(':nom', $nom, PDO::PARAM_STR);
                $updateClient->bindParam(':email', $email, PDO::PARAM_STR);
                $updateClient->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                $updateClient->execute();
                
                // Traiter les adresses existantes
                if (isset($_POST['adresse_id']) && is_array($_POST['adresse_id'])) {
                    foreach ($_POST['adresse_id'] as $key => $adresse_id) {
                        $ville = trim($_POST['ville'][$key] ?? '');
                        $code_postal = trim($_POST['code_postal'][$key] ?? '');
                        $pays = trim($_POST['pays'][$key] ?? '');
                        
                        if (!empty($ville) && !empty($code_postal)) {
                            // Mettre à jour l'adresse existante
                            $updateAdresse = $pdo->prepare("UPDATE client_adresse SET ville = :ville, code_postal = :code_postal, pays = :pays WHERE adresse_id = :adresse_id");
                            $updateAdresse->bindParam(':ville', $ville, PDO::PARAM_STR);
                            $updateAdresse->bindParam(':code_postal', $code_postal, PDO::PARAM_STR);
                            $updateAdresse->bindParam(':pays', $pays, PDO::PARAM_STR);
                            $updateAdresse->bindParam(':adresse_id', $adresse_id, PDO::PARAM_INT);
                            $updateAdresse->execute();
                        }
                    }
                }
                
                // Ajouter une nouvelle adresse si fournie
                $new_ville = trim($_POST['new_ville'] ?? '');
                $new_code_postal = trim($_POST['new_code_postal'] ?? '');
                $new_pays = trim($_POST['new_pays'] ?? '');
                
                if (!empty($new_ville) && !empty($new_code_postal)) {
                    $insertAdresse = $pdo->prepare("INSERT INTO client_adresse (client_id, ville, code_postal, pays) VALUES (:client_id, :ville, :code_postal, :pays)");
                    $insertAdresse->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                    $insertAdresse->bindParam(':ville', $new_ville, PDO::PARAM_STR);
                    $insertAdresse->bindParam(':code_postal', $new_code_postal, PDO::PARAM_STR);
                    $insertAdresse->bindParam(':pays', $new_pays, PDO::PARAM_STR);
                    $insertAdresse->execute();
                }
                
                // Validation de la transaction
                $pdo->commit();
                
                $message = 'Client mis à jour avec succès';
                // Redirection après 2 secondes
                header("refresh:2;url=admin_clients.php");
            }
        }
    }
    
    // Récupérer les informations du client
    $clientStmt = $pdo->prepare("SELECT * FROM client WHERE client_id = :client_id");
    $clientStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $clientStmt->execute();
    
    if ($clientStmt->rowCount() > 0) {
        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
        $nom = $client['nom'];
        $email = $client['email'];
        
        // Récupérer les adresses du client
        $adresseStmt = $pdo->prepare("SELECT * FROM client_adresse WHERE client_id = :client_id");
        $adresseStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
        $adresseStmt->execute();
        $adresses = $adresseStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = 'Client non trouvé';
    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = 'Erreur: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un client</title>
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
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        h1 {
            color: #333;
            margin: 0;
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .section-title {
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        .btn-danger {
            background-color: #F44336;
            color: white;
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
        .buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .adresse-container {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .adresse-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .adresse-col {
            flex: 1;
        }
        .add-adresse-btn {
            margin-top: 10px;
            background-color: #2196F3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Modifier le client</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (empty($error) || !empty($client)): ?>
            <form method="POST" action="">
                <div class="section-title">Informations du client</div>
                
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                
                <div class="section-title">Adresses existantes</div>
                
                <?php if (count($adresses) > 0): ?>
                    <?php foreach ($adresses as $index => $adresse): ?>
                        <div class="adresse-container">
                            <input type="hidden" name="adresse_id[<?= $index ?>]" value="<?= $adresse['adresse_id'] ?>">
                            
                            <div class="adresse-row">
                                <div class="adresse-col">
                                    <label for="ville<?= $index ?>">Ville</label>
                                    <input type="text" id="ville<?= $index ?>" name="ville[<?= $index ?>]" value="<?= htmlspecialchars($adresse['ville']) ?>">
                                </div>
                                
                                <div class="adresse-col">
                                    <label for="code_postal<?= $index ?>">Code postal</label>
                                    <input type="text" id="code_postal<?= $index ?>" name="code_postal[<?= $index ?>]" value="<?= htmlspecialchars($adresse['code_postal']) ?>">
                                </div>
                                
                                <div class="adresse-col">
                                    <label for="pays<?= $index ?>">Pays</label>
                                    <input type="text" id="pays<?= $index ?>" name="pays[<?= $index ?>]" value="<?= htmlspecialchars($adresse['pays']) ?>">
                                </div>
                            </div>
                            
                            <a href="supprimer_adresse.php?id=<?= $adresse['adresse_id'] ?>&client_id=<?= $client_id ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette adresse ?')">Supprimer l'adresse</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune adresse enregistrée</p>
                <?php endif; ?>
                
                <div class="section-title">Ajouter une nouvelle adresse</div>
                
                <div class="adresse-container">
                    <div class="adresse-row">
                        <div class="adresse-col">
                            <label for="new_ville">Ville</label>
                            <input type="text" id="new_ville" name="new_ville">
                        </div>
                        
                        <div class="adresse-col">
                            <label for="new_code_postal">Code postal</label>
                            <input type="text" id="new_code_postal" name="new_code_postal">
                        </div>
                        
                        <div class="adresse-col">
                            <label for="new_pays">Pays</label>
                            <input type="text" id="new_pays" name="new_pays">
                        </div>
                    </div>
                </div>
                
                <div class="buttons">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="admin_clients.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>