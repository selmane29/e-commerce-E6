<?php
session_start();

include 'bdd.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'])) {
        $produit_id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM produit WHERE produit_id = :id");
        $stmt->execute(['id' => $produit_id]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produit) {
            die("Produit introuvable.");
        }
    } else {
        die("ID de produit non spécifié.");
    }

    if (isset($_POST['add_to_cart'])) {
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }

        if (isset($_SESSION['panier'][$produit_id])) {
            $_SESSION['panier'][$produit_id] += 1;
        } else {
            $_SESSION['panier'][$produit_id] = 1;
        }

        header("Location: mon_panier.php");
        exit();
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produit['nom']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .product-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            flex: 1;
            text-align: center;
        }

        .product-image img {
            max-width: 100%;
            border-radius: 10px;
        }

        .product-details {
            flex: 1;
            padding: 20px;
        }

        .product-details h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .product-details p {
            font-size: 16px;
            line-height: 1.6;
        }

        .product-details .price {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }

        .btn-container {
            margin-top: 20px;
        }

        .btn-container a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="product-container">
    <div class="product-image">
        <img src="<?= htmlspecialchars($produit['image']); ?>" alt="<?= htmlspecialchars($produit['nom']); ?>">
    </div>
    <div class="product-details">
        <h1><?= htmlspecialchars($produit['nom']); ?></h1>
        
        <p><?= nl2br(htmlspecialchars($produit['script'])); ?></p>
        <div class="price">Prix : €<?= htmlspecialchars($produit['prix']); ?></div>

        <form method="post" class="btn-container">
            <input type="hidden" name="produit_id" value="<?= htmlspecialchars($produit['produit_id']); ?>">
            <button type="submit" name="add_to_cart" class="btn btn-success">Ajouter au Panier</button>
            <a href="catalogue.php" class="btn btn-secondary">Retour au Catalogue</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
