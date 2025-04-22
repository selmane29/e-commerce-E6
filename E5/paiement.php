<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement</title>
    <link rel="stylesheet" href="paiement.css">
</head>
<body>
    <div class="container">
        <h2>Paiement sécurisé</h2>
        <form action="confirmation.php" method="POST" class="payment-form">
            <fieldset>
                <legend>Informations personnelles</legend>
                <input type="text" name="nom" placeholder="Nom complet" required>
                <input type="email" name="email" placeholder="Adresse email" required>
            </fieldset>

            <fieldset>
                <legend>Adresse de livraison</legend>
                <input type="text" name="adresse" placeholder="Adresse" required>
                <input type="text" name="ville" placeholder="Ville" required>
                <input type="text" name="code_postal" placeholder="Code postal" required>
                <input type="text" name="pays" placeholder="Pays" required>
            </fieldset>

            <fieldset>
                <legend>Détails de paiement</legend>
                <input type="text" name="numero_carte" placeholder="Numéro de carte" maxlength="19" required>
                <div class="card-details">
                    <input type="text" name="expiration" placeholder="MM/AA" required>
                    <input type="text" name="cvv" placeholder="CVV" maxlength="4" required>
                </div>
            </fieldset>

            <button type="submit">Payer maintenant</button>
        </form>
    </div>
</body>
</html>
