<?php include 'header.php'; ?>

<main>
    <!-- HERO SECTION -->
    <div class="hero-section" >
        <div class="container text-center">
            <h1 class="display-4 mb-4">Bienvenue sur eKOM</h1>
            <p class="lead mb-4">Découvrez notre collection exceptionnelle de produits technologiques</p>
            <a href="?page=products" class="btn btn-lg btn-primary">Découvrir</a>
        </div>
    </div>

    <!-- CATEGORIES SECTION -->
    <div class="section">
        <div class="container">
            <div class="row">
                <!-- shop -->
                <div class="col-md-4 col-xs-6">
                    <div class="shop hover-effect">
                        <div class="shop-img">
                            <img src="images/sac.png" alt="sacs">
                            <div class="overlay"></div>
                        </div>
                        <div class="shop-body">
                            <h3>collection<br>de sacs</h3>
                            <a href="?page=products&category=sacs" class="cta-btn">voir plus <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- shop -->
                <div class="col-md-4 col-xs-6">
                    <div class="shop hover-effect">
                        <div class="shop-img">
                            <img src="images/montre.png" alt="Accessories">
                            <div class="overlay"></div>
                        </div>
                        <div class="shop-body">
                            <h3>Collection<br>de montres</h3>
                            <a href="?page=products&category=montres" class="cta-btn">voir plus <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- shop -->
                <div class="col-md-4 col-xs-6">
                    <div class="shop hover-effect">
                        <div class="shop-img">
                            <img src="images/boxeur.png" alt="Cameras">
                            <div class="overlay"></div>
                        </div>
                        <div class="shop-body">
                            <h3>Collection<br>de boxeurs</h3>
                            <a href="?page=products&category=boxeurs" class="cta-btn">voir plus <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FEATURED PRODUCTS -->
    <div class="section bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Produits Populaires</h2>
            <div class="row">
                <?php
                $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
                while ($product = $stmt->fetch()): ?>
                    <div class="col-md-3">
                        <div class="card product-card">
                            <div class="product-image">
                                <img src="<?= escape($product['image']) ?>" class="card-img-top" alt="<?= escape($product['name']) ?>">
                                <div class="product-overlay">
                                    <a href="?page=product&id=<?= $product['id'] ?>" class="btn btn-primary">Voir détails</a>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= escape($product['name']) ?></h5>
                                <p class="price"><?= number_format($product['price'], 2) ?> €</p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- FEATURES SECTION -->
    <div class="section py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="fa fa-truck fa-3x mb-3"></i>
                        <h4>Livraison Gratuite</h4>
                        <p>Pour toute commande > 50€</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="fa fa-credit-card fa-3x mb-3"></i>
                        <h4>Paiement Sécurisé</h4>
                        <p>100% sécurisé</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="fa fa-headphones fa-3x mb-3"></i>
                        <h4>Support 24/7</h4>
                        <p>Service client dédié</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="fa fa-undo fa-3x mb-3"></i>
                        <h4>Retours Gratuits</h4>
                        <p>Sous 30 jours</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* Effets de survol pour les catégories */
.section{
    padding: 60px 0;
}
.hover-effect {
    transition: transform 0.3s ease;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.hover-effect:hover {
    transform: translateY(-5px);
}

.shop {
    position: relative;
    margin-bottom: 30px;
}

.shop-img {
    position: relative;
    overflow: hidden;
}

.shop-img img {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    transition: all 0.3s;
}

.shop:hover .overlay {
    background: rgba(0,0,0,0.5);
}

.shop-body {
    position: absolute;
    bottom: 30px;
    left: 30px;
    color: white;
}

/* Style pour les cartes produits */
.product-card {
    transition: all 0.3s;
    margin-bottom: 20px;
}

.product-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.product-image {
    position: relative;
    overflow: hidden;
}

.product-image img {
    height: 200px;
    object-fit: cover;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.price {
    color: #D10024;
    font-weight: bold;
    font-size: 1.2em;
}

/* Style pour les features */
.feature-box {
    padding: 20px;
    transition: all 0.3s;
}

.feature-box:hover {
    transform: translateY(-5px);
}

.feature-box i {
    color: #D10024;
}
</style>
</body>
</html>
