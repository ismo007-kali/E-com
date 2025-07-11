# eKOM - Boutique en ligne simplifiée

## Structure du projet

```
eKOM/
├── assets/          # Fichiers CSS, JavaScript et images
├── pages/           # Pages HTML
├── uploads/         # Fichiers uploadés par les utilisateurs
├── public/          # Fichiers publics
├── index.php        # Point d'entrée de l'application
├── config.php       # Configuration et fonctions utilitaires
└── database.sql     # Structure de la base de données
```

## Fonctionnalités principales

- Affichage des produits
- Panier d'achat
- Connexion/déconnexion
- Gestion des utilisateurs
- Gestion des commandes

## Installation

1. Créer une base de données MySQL nommée "ecommerce"
2. Importer le fichier `database.sql`
3. Configurer les accès dans `config.php`
4. Accéder à l'application via `index.php`

## Sécurité

- Utilisation de PDO pour la base de données
- Protection contre les injections SQL
- Sécurisation des entrées utilisateur
- Gestion des sessions sécurisée
