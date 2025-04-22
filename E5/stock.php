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

// Récupérer tous les produits
$sql = "SELECT * FROM produit ORDER BY produit_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Gestion Produits</title>
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
    .btn-add {
      background-color: #4CAF50;
      color: white;
      padding: 10px 15px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: bold;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #f2f2f2;
      font-weight: bold;
    }
    tr:hover {
      background-color: #f5f5f5;
    }
    .thumb {
      max-width: 80px;
      max-height: 80px;
      object-fit: contain;
    }
    .btn-edit, .btn-delete {
      display: inline-block;
      padding: 6px 10px;
      margin: 2px;
      border-radius: 3px;
      text-decoration: none;
      color: white;
      font-size: 14px;
    }
    .btn-edit {
      background-color: #2196F3;
    }
    .btn-delete {
      background-color: #F44336;
    }
    .description {
      max-width: 300px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>
<body>

  <header>
    <h1>Gestion des produits</h1>
    <a href="ajouter_produit.php" class="btn-add">+ Ajouter un produit</a>
  </header>

  <main class="product-list">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Description</th>
          <th>Prix</th>
          <th>Stock</th>
          <th>Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
            // Afficher les données de chaque ligne
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["produit_id"] . "</td>";
                echo "<td>" . $row["nom"] . "</td>";
                echo "<td class='description'>" . $row["description"] . "</td>";
                echo "<td>" . $row["prix"] . " €</td>";
                echo "<td>" . $row["stock"] . "</td>";
                echo "<td><img src='" . $row["image"] . "' alt='" . $row["nom"] . "' class='thumb'></td>";
                echo "<td>
                        <a href='modifier_produit.php?id=" . $row["produit_id"] . "' class='btn-edit'>Modifier</a>
                        <a href='supprimer_produit.php?id=" . $row["produit_id"] . "' class='btn-delete' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce produit ?\")'>Supprimer</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Aucun produit trouvé</td></tr>";
        }
        $conn->close();
        ?>
      </tbody>
    </table>
  </main>

</body>
</html>