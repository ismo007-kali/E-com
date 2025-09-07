# MODE ET TENDANCE - E-commerce de Mode

## 📋 Description
MODE ET TENDANCE est une plateforme e-commerce spécialisée dans la vente de vêtements et accessoires de mode. Le site offre une expérience d'achat fluide et sécurisée avec un design moderne et responsive.

## 🛠️ Technologies Utilisées

### Frontend
- HTML5, CSS3, JavaScript
- Bootstrap 5
- Font Awesome pour les icônes
- Owl Carousel pour les sliders

### Backend
- PHP 8.0+
- MySQL/MariaDB
- Architecture MVC personnalisée

### Sécurité
- Protection contre les injections SQL
- Hachage des mots de passe (bcrypt)
- Protection CSRF
- Validation des entrées utilisateur

## 📂 Structure du Projet

```
E-com/
├── account/               # Gestion des comptes utilisateurs
│   ├── orders/            # Commandes des utilisateurs
│   ├── addresses/         # Adresses des utilisateurs
│   ├── index.php          # Tableau de bord
│   └── change-password.php
├── admin/                 # Interface d'administration
│   ├── products/          # Gestion des produits
│   ├── orders/            # Gestion des commandes
│   ├── customers/         # Gestion des clients
│   └── dashboard.php      # Tableau de bord admin
├── assets/                # Fichiers statiques
│   ├── css/               # Feuilles de style
│   ├── js/                # Scripts JavaScript
│   ├── images/            # Images du site
│   └── uploads/           # Fichiers téléchargés
├── includes/              # Fichiers d'inclusion
│   ├── classes/           # Classes PHP
│   ├── config.php         # Configuration
│   └── init.php           # Initialisation
├── views/                 # Vues
├── index.php              # Page d'accueil
├── shop.php               # Catalogue produits
├── product.php            # Fiche produit
├── cart.php               # Panier d'achat
├── checkout.php           # Paiement
├── login.php              # Connexion
└── register.php           # Inscription
```

## 🚀 Fonctionnalités

### Pour les visiteurs
- Parcourir le catalogue de produits
- Filtrage et recherche de produits
- Inscription et connexion sécurisée
- Ajout au panier
- Passage de commande

### Pour les utilisateurs connectés
- Gestion du profil utilisateur
- Historique des commandes
- Suivi des commandes
- Gestion des adresses
- Changement de mot de passe

### Pour les administrateurs
- Gestion complète des produits
- Gestion des commandes
- Gestion des clients
- Tableau de bord analytique
- Sauvegardes de la base de données

## 🛠 Installation

1. **Prérequis**
   - Serveur web (Apache/Nginx)
   - PHP 8.0 ou supérieur
   - MySQL 5.7+ ou MariaDB 10.3+
   - Composer (pour les dépendances)

2. **Configuration**
   - Cloner le dépôt
   - Installer les dépendances : `composer install`
   - Configurer la base de données dans `includes/config.php`
   - Importer le schéma SQL (`database.sql`)
   - Configurer les permissions des dossiers d'upload

3. **Environnement de développement**
   - Activer les erreurs PHP dans `includes/init.php`
   - Configurer les variables d'environnement
   - Utiliser Xdebug pour le débogage

## 🔒 Sécurité

- Tous les mots de passe sont hachés avec bcrypt
- Protection contre les attaques XSS
- Validation des entrées utilisateur
- Protection CSRF sur les formulaires
- Gestion sécurisée des sessions
- Backup automatique de la base de données

## 🎨 Thème et Personnalisation

Le site utilise un système de thème avec support du mode sombre. Les couleurs principales sont :
- Primaire : `#6b73ff` (Bleu)
- Secondaire : `#9c88ff` (Violet)
- Accent : `#4ecdc4` (Turquoise)
- Texte : `#2c3e50` (Gris foncé)

## 📝 Licence

Ce projet est sous licence propriétaire. Tous droits réservés.

## 👥 Contact

Pour toute question ou support, veuillez contacter l'équipe technique à [email@example.com](mailto:email@example.com)

## 🌐 Réseaux Sociaux

- [Facebook](#)
- [Instagram](#)
- [Twitter](#)

---

*Dernière mise à jour : Septembre 2025*