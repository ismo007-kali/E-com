<?php
// Vérifier les permissions
check_permission('admin');

// Récupérer tous les vendeurs
$stmt = $pdo->query("SELECT v.*, u.email FROM vendors v JOIN users u ON v.user_id = u.id");
$vendors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des vendeurs - eKOM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Gestion des vendeurs</h1>
        
        <div class="admin-actions">
            <button onclick="document.getElementById('addVendorForm').style.display = 'block'">Ajouter un vendeur</button>
        </div>
        
        <div id="addVendorForm" style="display: none; margin-top: 20px; padding: 20px; background: #f5f5f5; border-radius: 8px;">
            <h2>Ajouter un vendeur</h2>
            <form method="POST" action="?page=admin_add_vendor">
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div>
                    <label for="company_name">Nom de la société:</label>
                    <input type="text" id="company_name" name="company_name" required>
                </div>
                
                <div>
                    <label for="address">Adresse:</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                
                <div>
                    <label for="phone">Téléphone:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <button type="submit">Ajouter</button>
                <button type="button" onclick="document.getElementById('addVendorForm').style.display = 'none'">Annuler</button>
            </form>
        </div>
        
        <div class="vendors-list">
            <?php foreach ($vendors as $vendor): ?>
                <div class="vendor-card">
                    <h3><?= escape($vendor['company_name']) ?></h3>
                    <p>Email: <?= escape($vendor['email']) ?></p>
                    <p>Adresse: <?= escape($vendor['address']) ?></p>
                    <p>Téléphone: <?= escape($vendor['phone']) ?></p>
                    <div class="vendor-actions">
                        <a href="?page=edit_vendor&id=<?= $vendor['id'] ?>">Modifier</a>
                        <a href="?page=delete_vendor&id=<?= $vendor['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce vendeur ?')">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
