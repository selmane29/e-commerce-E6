<?php
// bdd.php
$servername = "localhost";
$dbname = "informatique";
$dbusername = "root";
$dbpassword = "";
?>
<?php
session_start();
include 'bdd.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des prix min et max
    $priceQuery = $pdo->query("SELECT MIN(prix) AS min_price, MAX(prix) AS max_price FROM produit");
    $priceResult = $priceQuery->fetch(PDO::FETCH_ASSOC);
    $minPrice = $priceResult['min_price'] ?? 0;
    $maxPrice = $priceResult['max_price'] ?? 500;

    // Construction de la requête SQL
    $sql = "SELECT * FROM produit WHERE 1=1";
    $filterConditions = [];

    // Gestion des filtres de type
    if (isset($_GET['filter_souris'])) {
        $filterConditions[] = "description = 'Souris'";
    }
    if (isset($_GET['filter_clavier'])) {
        $filterConditions[] = "description = 'Clavier'";
    }

    // Gestion des filtres de prix
    if (isset($_GET['price_min']) && isset($_GET['price_max'])) {
        $min_price = (float)$_GET['price_min'];
        $max_price = (float)$_GET['price_max'];
        $filterConditions[] = "prix BETWEEN :min_price AND :max_price";
    }

    if (!empty($filterConditions)) {
        $sql .= " AND " . implode(" AND ", $filterConditions);
    }

    $stmt = $pdo->prepare($sql);

    if (isset($min_price) && isset($max_price)) {
        $stmt->bindParam(':min_price', $min_price, PDO::PARAM_STR);
        $stmt->bindParam(':max_price', $max_price, PDO::PARAM_STR);
    }

    $stmt->execute();
    $produit = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Vente de Souris et Claviers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .btn-primary, .price-slider input[type="range"]::-webkit-slider-thumb, .card .btn-primary {
            background-color: #028a0f;
            border-color: #028a0f;
        }
        .btn-primary:hover {
            background-color: #026f0c;
            border-color: #026f0c;
        }
        .price-slider input[type="range"] {
            width: 200px;
            height: 5px;
            appearance: none;
            background: #ddd;
            border-radius: 5px;
            outline: none;
            cursor: pointer;
        }
        .price-slider input[type="range"]::-webkit-slider-thumb {
            appearance: none;
            width: 15px;
            height: 15px;
            background: #028a0f;
            border-radius: 50%;
            cursor: pointer;
        }
        .price-values span {
            font-weight: bold;
            color: #028a0f;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <h1 class="text-center">Boutique Souris et Claviers</h1>

    <!-- Formulaire de filtres -->
    <form method="get" id="filterForm" class="mb-4">
        <div>
            <label><input type="checkbox" name="filter_souris" <?php echo isset($_GET['filter_souris']) ? 'checked' : ''; ?>> Souris</label>
            <label><input type="checkbox" name="filter_clavier" <?php echo isset($_GET['filter_clavier']) ? 'checked' : ''; ?>> Claviers</label>
        </div>

        <div class="mt-3 price-slider">
            <input type="range" name="price_min" min="<?php echo $minPrice; ?>" max="<?php echo $maxPrice; ?>" value="<?php echo isset($_GET['price_min']) ? $_GET['price_min'] : $minPrice; ?>" step="1" id="minPrice">
            <input type="range" name="price_max" min="<?php echo $minPrice; ?>" max="<?php echo $maxPrice; ?>" value="<?php echo isset($_GET['price_max']) ? $_GET['price_max'] : $maxPrice; ?>" step="1" id="maxPrice">
        </div>
        <div class="price-values">
            <span>Prix min : <span id="price-min"><?php echo isset($_GET['price_min']) ? $_GET['price_min'] : $minPrice; ?></span> €</span>
            <span>Prix max : <span id="price-max"><?php echo isset($_GET['price_max']) ? $_GET['price_max'] : $maxPrice; ?></span> €</span>
        </div>
    </form>

    <!-- Affichage des produits -->
    <div class="row">
        <?php foreach ($produit as $produit): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="<?php echo htmlspecialchars($produit['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($produit['nom']); ?></h5>
                        <p class="card-text">€<?php echo htmlspecialchars($produit['prix']); ?></p>
                        <a href="product_detail.php?id=<?php echo $produit['produit_id']; ?>" class="btn btn-primary">Voir Détails</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footers.php'; ?>

<script>
    const minSlider = document.getElementById('minPrice');
    const maxSlider = document.getElementById('maxPrice');
    const minPriceLabel = document.getElementById('price-min');
    const maxPriceLabel = document.getElementById('price-max');
    const filterForm = document.getElementById('filterForm');

    // Fonction pour mettre à jour et soumettre le formulaire
    function updateFilters() {
        filterForm.submit();
    }

    // Mise à jour des prix affichés et soumission automatique
    minSlider.addEventListener('input', function () {
        if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        minPriceLabel.textContent = minSlider.value;
        updateFilters();
    });

    maxSlider.addEventListener('input', function () {
        if (parseInt(maxSlider.value) < parseInt(minSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        maxPriceLabel.textContent = maxSlider.value;
        updateFilters();
    });

    // Soumission automatique des filtres lors du changement des cases à cocher
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateFilters();
        });
    });
</script>
</body>
</html>
