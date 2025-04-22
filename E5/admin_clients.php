<?php
// Configuration de la base de données
include 'bdd.php'; // s'assurer que ce fichier contient les variables $servername, $dbname, $dbusername, $dbpassword

// Paramètres de la page
$resultsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $resultsPerPage;

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($search)) {
    $searchCondition = "WHERE nom LIKE :search OR email LIKE :search";
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Plus sécuriser pour les requêtes préparées
    
    // Requête pour compter le nombre total de client (pour la pagination)
    $countQuery = "SELECT COUNT(*) FROM client $searchCondition";
    $countStmt = $pdo->prepare($countQuery);
    if (!empty($search)) {
        $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalClients = $countStmt->fetchColumn();
    
    // Calcul du nombre total de pages
    $totalPages = ceil($totalClients / $resultsPerPage);
    
    // Requête pour récupérer les clients avec pagination
    $query = "SELECT c.client_id, c.nom, c.email, 
             GROUP_CONCAT(DISTINCT ca.ville, ', ', ca.code_postal, ', ', ca.pays SEPARATOR ' | ') as adresses
             FROM client c
             LEFT JOIN client_adresse ca ON c.client_id = ca.client_id
             $searchCondition
             GROUP BY c.client_id
             ORDER BY c.client_id DESC
             LIMIT :offset, :limit";
    
    $stmt = $pdo->prepare($query);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des clients</title>
    <link rel="stylesheet" href="admin_clients.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Liste des clients</h1>
            <button class="btn btn-edit" onclick="window.location.href='ajouter_client.php'">+ Ajouter un client</button>
        </div>
        
        <div class="search-bar">
            <form action="" method="GET">
                <input type="text" class="search-input" name="search" placeholder="Rechercher un client..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-button">Rechercher</button>
                <?php if (!empty($search)): ?>
                    <a href="?page=1" class="reset-search">Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Adresses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clients) > 0): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['client_id']) ?></td>
                                <td><?= htmlspecialchars($client['nom']) ?></td>
                                <td><?= htmlspecialchars($client['email']) ?></td>
                                <td><?= !empty($client['adresses']) ? htmlspecialchars($client['adresses']) : 'Aucune adresse' ?></td>
                                <td class="actions-cell">
                                    <a href="editer_client.php?id=<?= $client['client_id'] ?>" class="btn btn-edit">Modifier</a>
                                    <a href="supprimer_client.php?id=<?= $client['client_id'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">Aucun client trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="page-link">&laquo;</a>
                    <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="page-link">&lsaquo;</a>
                <?php endif; ?>
                
                <?php
                // Afficher un nombre limité de liens de pages (5 maximum)
                $startPage = max(1, min($page - 2, $totalPages - 4));
                $endPage = min($totalPages, max($page + 2, 5));
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" 
                       class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="page-link">&rsaquo;</a>
                    <a href="?page=<?= $totalPages ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" class="page-link">&raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>