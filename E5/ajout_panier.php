<?php
session_start();

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $productId = $_POST['id'];

    // Initialise le panier si ce n'est pas encore fait
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    // Ajoute le produit au panier avec la quantité
    if (isset($_SESSION['panier'][$productId])) {
        $_SESSION['panier'][$productId]++;
    } else {
        $_SESSION['panier'][$productId] = 1;
    }

    header('Location: panier.php');
    exit();
} else {
    echo "ID du produit non valide.";
}
?>
