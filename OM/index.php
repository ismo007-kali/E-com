<?php
// Démarrer la session
session_start();

// Charger la configuration
require_once 'config.php';

// Définir la page à afficher
$page = $_GET['page'] ?? 'home';

// Inclure la page demandée
switch($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'products':
        include 'pages/products.php';
        break;
    case 'cart':
        include 'pages/cart.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'logout':
        include 'pages/logout.php';
        break;
    case 'checkout':
        include 'pages/checkout.php';
        break;
    case 'order_confirmation':
        include 'pages/order_confirmation.php';
        break;
    case 'admin_products':
        include 'pages/admin_products.php';
        break;
    case 'admin_dashboard':
        include 'pages/admin_dashboard.php';
        break;
    case 'admin_users':
        include 'pages/admin_users.php';
        break;
    case 'vendor_dashboard':
        include 'pages/vendor_dashboard.php';
        break;
    case 'admin_orders':
        include 'pages/admin_orders.php';
        break;
    case 'vendor_orders':
        include 'pages/vendor_orders.php';
        break;
    case 'vendor_products':
        include 'pages/vendor_products.php';
        break;
    case 'vendor_profile':
        include 'pages/vendor_profile.php';
        break;
    default:
        include 'pages/404.php';
}
