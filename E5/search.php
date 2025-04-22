<?php
include 'bdd.php';

// Initialisation de $search et des résultats
$search = '';
$results = [];

// Vérifier si une recherche est soumise
if (isset($_GET['query'])) {
    $search = htmlspecialchars($_GET['query']);

    try {
        // Connexion à la base de données
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbusername, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }

    // Préparer et exécuter la requête pour rechercher dans les produits
    $stmt = $pdo->prepare("SELECT produit_id, nom, image, prix FROM produit WHERE nom LIKE :query");
    $stmt->execute(['query' => '%' . $search . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de Recherche</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        h1 {
            color: #028a0f;
            margin-bottom: 20px;
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .product-item {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .product-item img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .product-item h2 {
            font-size: 1.2rem;
            color: #028a0f;
            margin-bottom: 10px;
        }

        .product-item p {
            color: #555;
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .product-item .btn {
            background-color: #028a0f;
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            text-decoration: none;
            font-size: 0.9rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .product-item .btn:hover {
            background-color: #026f0c;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Résultats pour "<?= htmlspecialchars($search) ?>"</h1>
        <div class="product-list">
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $product): ?>
                    <div class="product-item">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['nom']) ?>" width="150" height="150">
                        <h2><?= htmlspecialchars($product['nom']) ?></h2>
                        <p>Prix: €<?= htmlspecialchars($product['prix']) ?></p>
                        <a href="product_detail.php?id=<?= htmlspecialchars($product['produit_id']) ?>" class="btn">Détails du Produit</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun résultat trouvé pour votre recherche.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footers.php'; ?>
</body>
</html>
