<?php
// Inclure le fichier d'initialisation pour accéder aux constantes et fonctions
require_once __DIR__ . '/../init.php';
?>
<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="author" content="" />
  <link rel="shortcut icon" href="<?= BASE_URL ?>/images/favicon.png" type="image/x-icon">
  <title>MODE ET TENDANCE</title>
  
  <!-- Font Awesome stylesheet -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <!-- slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>/css/css/bootstrap.css" />

  <!-- Custom styles for this template -->
  <link href="<?= BASE_URL ?>/css/css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="<?= BASE_URL ?>/css/css/responsive.css" rel="stylesheet" />
  <!-- Dark mode styles -->
  <link href="<?= BASE_URL ?>/css/css/dark-mode.css" rel="stylesheet" />
  <!-- Account pages styles -->
  <link href="<?= BASE_URL ?>/css/css/account.css" rel="stylesheet" />
  
</head>

<body>
  <div class="hero_area" style="background: #ffffff;">
    <!-- header section strats -->
    <header class="header_section">
      <nav class="navbar navbar-expand-lg custom_nav-container ">
        <a class="navbar-brand" href="<?= BASE_URL ?>">
          <img src="<?= BASE_URL ?>/images/logo/logo.png" alt="MODE ET TENDANCE" style="height: 50px; width: 50px;">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class=""></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav  ">
            <li class="nav-item <?php echo ($_SERVER['REQUEST_URI'] === '/E-com/' || $_SERVER['REQUEST_URI'] === '/E-com/index.php') ? 'active' : ''; ?>">
              <a class="nav-link" href="<?= BASE_URL ?>"><strong>ACCUEIL</strong> <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'shop.php') !== false) ? 'active' : ''; ?>">
              <a class="nav-link" href="<?= BASE_URL ?>/pages/shop.php">
                <strong>Shop</strong>
              </a>
            </li>
          <!--   <li class="nav-item">
              <a class="nav-link" href="why.php">
               <strong>POURQUOI NOUS</strong>
              </a>
            </li> -->
            <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'testimonial.php') !== false) ? 'active' : ''; ?>">
              <a class="nav-link" href="<?= BASE_URL ?>/pages/testimonial.php">
                <strong>TEMOIGNAGE</strong>
              </a>
            </li>
            <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'contact.php') !== false) ? 'active' : ''; ?>">
              <a class="nav-link" href="<?= BASE_URL ?>/pages/contact.php"><strong>CONTACT</strong></a>
            </li>
          </ul>
          <div class="user_option">
            <?php if (isset($user) && $user->isLoggedIn()): ?>
              <div class="dropdown">
                <a href="#" class="dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-user" aria-hidden="true"></i>
                  <span class="d-none d-md-inline ms-1">Mon compte</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                  <li><a class="dropdown-item" href="<?= BASE_URL ?>/account/"><i class="fa fa-user me-2"></i>Mon profil</a></li>
                  <li><a class="dropdown-item" href="account/orders"><i class="fa fa-shopping-bag me-2"></i>Mes commandes</a></li>
                  <li><a class="dropdown-item" href="account/addresses"><i class="fa fa-address-book me-2"></i>Mes adresses</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <?php if ($user->isAdmin()): ?>
                    <li><a class="dropdown-item text-primary" href="<?= BASE_URL ?>/admin/"><i class="fa fa-cog me-2"></i>Administration</a></li>
                  <?php endif; ?>
                  <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/pages/logout.php"><i class="fa fa-sign-out me-2"></i>Déconnexion</a></li>
                </ul>
              </div>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/pages/login.php" class="me-2">
                <i class="fa fa-sign-in" aria-hidden="true"></i>
                <span class="d-none d-md-inline">Connexion</span>
              </a>
              <a href="<?= BASE_URL ?>/pages/register.php" class="btn btn-outline-primary btn-sm ms-2">
                S'inscrire
              </a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>/pages/cart.php" class="position-relative ms-3">
              <i class="fa fa-shopping-bag" aria-hidden="true"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                0
                <span class="visually-hidden">articles dans le panier</span>
              </span>
            </a>
            
            <form class="form-inline ms-3 d-none d-md-block">
              <button class="btn nav_search-btn" type="submit">
                <i class="fa fa-search" aria-hidden="true"></i>
              </button>
            </form>
            
            <!-- Bouton de basculement du mode sombre -->
            <button id="theme-toggle" class="btn btn-link ms-2 text-dark">
              <i class="fa fa-moon-o" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </nav>
    </header>