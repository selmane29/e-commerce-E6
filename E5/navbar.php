<nav class="navbar navbar-expand-lg" style="background-color: #ffffff; border-bottom: 2px solid #028a0f;">
    <div class="container-fluid">
        <a class="navbar-brand" href="Stage.php" style="color: #028a0f; font-weight: bold;">Informatique.net</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="color: #028a0f;"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="Stage.php" style="color: #028a0f;">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="catalogue.php" style="color: #028a0f;">Catalogue</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inscriptions.php" style="color: #028a0f;">Inscription</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="connection.php" style="color: #028a0f;">Connexion</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="mon_panier.php" style="color: #028a0f;">Panier</a>
                </li>
            </ul>
            <form class="d-flex" action="search.php" method="get" style="display: flex; align-items: center;">
                <input type="text" name="query" placeholder="Recherche une vidéo..." class="form-control me-2" style="border: 1px solid #028a0f;" required>
                <button type="submit" style="background: none; border: none; padding: 0;">
                    <img src="img/loupe.jpg" alt="Rechercher" class="search-icon" style="width: 24px; height: 24px; object-fit: contain;">
                </button>
            </form>
        </div>
    </div>
</nav>

<style>
    /* Style du texte principal */
    .navbar .nav-link {
        color: #028a0f !important; /* Texte vert */
        font-weight: bold;
        transition: color 0.3s ease-in-out; /* Effet de transition */
    }

    /* Effet au survol */
    .navbar .nav-link:hover {
        color: #026f0c !important; /* Vert foncé au survol */
    }

    /* Titre principal */
    .navbar-brand {
        color: #028a0f !important; /* Couleur du titre */
        font-size: 1.5rem;
        transition: color 0.3s ease-in-out;
    }

    .navbar-brand:hover {
        color: #026f0c !important; /* Effet au survol pour le titre */
    }

    /* Icône de recherche */
    .search-icon {
        filter: invert(26%) sepia(72%) saturate(2422%) hue-rotate(111deg) brightness(89%) contrast(100%);
    }
    .search-icon:hover {
        filter: invert(38%) sepia(75%) saturate(2326%) hue-rotate(119deg) brightness(77%) contrast(111%);
    }

    /* Changement des boutons en mode mobile */
    .navbar-toggler {
        border: 1px solid #028a0f;
    }

    .navbar-toggler-icon {
        background-color: transparent;
        border-radius: 5px;
    }

    .form-control:focus {
        box-shadow: 0 0 5px #028a0f;
        border: 1px solid #026f0c;
    }
</style>
