<?php
// Configuration de la base de données
include 'bdd.php'; // s'assurer que ce fichier contient les variables $servername, $dbname, $dbusername, $dbpassword

// Initialisation des variables
$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';
$client = null;
$confirmation = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Vérifier si le client existe
    $clientStmt = $pdo->prepare("SELECT * FROM client WHERE client_id = :client_id");
    $clientStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $clientStmt->execute();
    
    if ($clientStmt->rowCount() > 0) {
        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($confirmation) {
            // Vérifier les relations avec d'autres tables
            $checkPanierStmt = $pdo->prepare("SELECT COUNT(*) FROM panier WHERE client_id = :client_id");
            $checkPanierStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $checkPanierStmt->execute();
            $panierCount = $checkPanierStmt->fetchColumn();
            
            $checkCommandeStmt = $pdo->prepare("SELECT COUNT(*) FROM commande WHERE client_id = :client_id");
            $checkCommandeStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $checkCommandeStmt->execute();
            $commandeCount = $checkCommandeStmt->fetchColumn();
            
            // Si le client a des relations, avertir mais permettre la suppression forcée
            if (($panierCount > 0 || $commandeCount > 0) && !isset($_GET['force'])) {
                $error = "Ce client a des articles dans son panier ou des commandes associées. ";
                $error .= "La suppression pourrait entraîner des problèmes dans la base de données. ";
                $error .= "<a href='supprimer_client.php?id={$client_id}&confirm=yes&force=yes' class='btn btn-danger'>Supprimer quand même</a>";
            } else {
                // Début de la transaction
                $pdo->beginTransaction();
                
                // Supprimer d'abord les adresses associées
                $deleteAdressesStmt = $pdo->prepare("DELETE FROM client_adresse WHERE client_id = :client_id");
                $deleteAdressesStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                $deleteAdressesStmt->execute();
                
                // Si force=yes, supprimer également les entrées dans le panier
                if (isset($_GET['force']) && $_GET['force'] === 'yes') {
                    $deletePanierStmt = $pdo->prepare("DELETE FROM panier WHERE client_id = :client_id");
                    $deletePanierStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                    $deletePanierStmt->execute();
                    
                    // Pour les commandes, on met client_id à NULL plutôt que de supprimer les commandes
                    $updateCommandesStmt = $pdo->prepare("UPDATE commande SET client_id = NULL WHERE client_id = :client_id");
                    $updateCommandesStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                    $updateCommandesStmt->execute();
                }
                
                // Supprimer le client
                $deleteClientStmt = $pdo->prepare("DELETE FROM client WHERE client_id = :client_id");
                $deleteClientStmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                $deleteClientStmt->execute();
                
                // Validation de la transaction
                $pdo->commit();
                
                $message = 'Client supprimé avec succès';
                // Redirection après 2 secondes
                header("refresh:2;url=admin_clients.php");
            }
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=