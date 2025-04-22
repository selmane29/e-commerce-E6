<?php
session_start();

include 'bdd.php';

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gestion de l'ajout au panier
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
        $produitId = intval($_POST['produit_id']);

        // Ajouter le produit au panier avec une quantité par défaut de 1
        if (!isset($_SESSION['panier'][$produitId])) {
            $_SESSION['panier'][$produitId] = 1;
        } else {
            $_SESSION['panier'][$produitId]++;
        }

        header('Location: mon_panier.php');
        exit();
    }

    // Récupération des produits dans le panier
    if (!empty($_SESSION['panier'])) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['panier']), '?'));
        $stmt = $pdo->prepare("SELECT * FROM produit WHERE produit_id IN ($placeholders)");
        $stmt->execute(array_keys($_SESSION['panier']));
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $produits = [];
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

// Mise à jour de la quantité via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $produitId = intval($_POST['produit_id']);
    $quantite = intval($_POST['quantite']);
    
    if ($quantite > 0) {
        $_SESSION['panier'][$produitId] = $quantite;
    } else {
        unset($_SESSION['panier'][$produitId]);
    }
    echo json_encode(['success' => true]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .navbar {
            background-color: #028a0f;
        }

        .navbar .nav-link {
            color: white !important;
        }

        .container {
            margin-top: 50px;
        }

        .table th, .table td {
            vertical-align: middle;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
    <h1 class="text-center mb-4">Votre Panier</h1>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Prix (€)</th>
                <th>Quantité</th>
                <th>Total</th>
                <th>Image</th>
                <th>Supprimer</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalGeneral = 0;
            if (!empty($produits)): ?>
                <?php foreach ($produits as $produit): 
                    $produitId = $produit['produit_id'];
                    $quantite = $_SESSION['panier'][$produitId];
                    $total = $produit['prix'] * $quantite;
                    $totalGeneral += $total;
                ?>
                    <tr id="row-<?php echo $produitId; ?>">
                        <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                        <td><?php echo htmlspecialchars($produit['description']); ?></td>
                        <td><?php echo htmlspecialchars($produit['prix']); ?> €</td>
                        <td>
                            <input 
                                type="number" 
                                class="form-control update-quantity" 
                                data-id="<?php echo $produitId; ?>" 
                                value="<?php echo htmlspecialchars($quantite); ?>" 
                                min="1" 
                                style="width: 80px;">
                        </td>
                        <td class="total-item"><?php echo number_format($total, 2); ?> €</td>
                        <td>
                            <img src="<?php echo htmlspecialchars($produit['image']); ?>" width="80px" height="80px" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-item" data-id="<?php echo $produitId; ?>">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Votre panier est vide.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="text-end mt-4">
        <h3>Total général : <span id="total-general"><?php echo number_format($totalGeneral, 2); ?></span> €</h3>
        <a href="paiement.php" class="btn">Passer au paiement</a>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mise à jour de la quantité via AJAX
    $('.update-quantity').on('change', function() {
        const produitId = $(this).data('id');
        const quantite = $(this).val();

        $.post('mon_panier.php', {
            update_quantity: true,
            produit_id: produitId,
            quantite: quantite
        }, function(response) {
            if (response.success) {
                location.reload(); // Recharger la page pour mettre à jour le total
            }
        }, 'json');
    });

    // Suppression d'un article via AJAX
    $('.remove-item').on('click', function() {
        const produitId = $(this).data('id');

        $.post('mon_panier.php', {
            update_quantity: true,
            produit_id: produitId,
            quantite: 0 // Définir la quantité à 0 pour supprimer
        }, function(response) {
            if (response.success) {
                $('#row-' + produitId).fadeOut('slow', function() {
                    $(this).remove();
                });
            }
        }, 'json');
    });
});
</script>

</body>
</html>
