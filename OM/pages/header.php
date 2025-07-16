<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKOM - <?php echo ucfirst($page); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
    <!-- <link rel="stylesheet" href="/assets/css/style.css"> -->
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php
    // Enregistrer la visite
    $page = $_GET['page'] ?? 'home';
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    try {
        $stmt = $pdo->prepare("INSERT INTO visits (ip_address, user_agent, page) VALUES (REMOTE_ADDR, HTTP_USER_AGENT, page)");
        $stmt->execute([$ip, $user_agent, $page]);
    } catch(PDOException $e) {
        // Ignorer les erreurs d'insertion
    }
    ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="?page=home">eKOM</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?page=home">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=products">Produits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=cart"><i class="fas fa-shopping-cart"></i> Panier</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=login"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="visitor-counter">
        <span id="visitorCount" class="badge bg-light text-dark">
            <?php
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM visits WHERE DATE(created_at) = CURDATE()");
            echo $stmt->fetch()['count'];
            ?> visiteurs aujourd'hui
        </span>
    </div>

    <div class="container ">
        <!-- Contenu principal -->
        <main class="py-4">
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
                    <?= escape($_SESSION['flash']['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript personnalisé -->
    <script src="../assets/js/charts.js"></script>
    
    <script>
        // Mettre à jour le compteur de visites toutes les 5 minutes
        setInterval(function() {
            fetch('?page=get_visitor_count')
                .then(response => response.text())
                .then(count => {
                    document.getElementById('visitorCount').textContent = count + ' visiteurs aujourd\'hui';
                });
        }, 5 * 60 * 1000);
    </script>
    
    <?php
    // End of PHP section
    ?>

