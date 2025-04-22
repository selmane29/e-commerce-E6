<?php
session_start();

$produit = [
    ["id" => 1, "nom" => "produit 1", "image" => "image/produit1.jpg", "prix" => 20],
    ["id" => 2, "nom" => "produit 2", "image" => "image/produit1.jpg", "prix" => 25],
    ["id" => 3, "nom" => "produit 3", "image" => "image/produit1.jpg", "prix" => 30],
];

// Initialisation du panier et du total
$cart_items = isset($_SESSION['cart_items']) ? $_SESSION['cart_items'] : [];
$total = 0;

if (isset($_POST['clear_cart'])) {
    // Si on clique sur "Vider le Panier", on vide le panier
    $_SESSION['cart_items'] = [];
    $cart_items = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbars.php'; ?>

    <h1>Votre Panier</h1>

    <?php if (!empty($cart_items)): ?>
        <ul>
            <?php foreach ($cart_items as $item_id): ?>
                <?php
                // Recherche du produit correspondant
                $product = array_filter($products, function($prod) use ($item_id) {
                    return $prod['id'] == $item_id;
                });
                // Récupérer le premier produit trouvé
                $product = array_values($product)[0];
                $total += $product['price'];
                ?>
                <li>
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="100">
                    <?php echo $product['name']; ?> - €<?php echo $product['price']; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p>Total: €<?php echo $total; ?></p>

        <form method="post">
            <button type="submit" name="clear_cart">Vider le Panier</button>
        </form>
    <?php else: ?>
        <p>Votre panier est vide.</p>
    <?php endif; ?>

    <a href="catalogue.php">Retourner au Catalogue</a>

    <?php include 'footers.php'; ?>
</body>
</html>
