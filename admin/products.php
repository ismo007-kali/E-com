<?php
require_once 'includes/header.php';

// Récupérer les produits avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page > 1) ? ($page - 1) * $per_page : 0;

// Gestion des filtres
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construire la requête de base
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";
$params = [];

// Ajouter les filtres
if (!empty($category_filter)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    if ($status_filter === 'in_stock') {
        $query .= " AND p.quantity > 0";
    } elseif ($status_filter === 'out_of_stock') {
        $query .= " AND p.quantity <= 0";
    } elseif ($status_filter === 'featured') {
        $query .= " AND p.is_featured = 1";
    }
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku = ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search]);
}

// Compter le nombre total de produits pour la pagination
$count_query = "SELECT COUNT(*) as total FROM ($query) as total_query";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_products = $stmt->fetch()['total'];
$total_pages = ceil($total_products / $per_page);

// Ajouter le tri et la pagination
$query .= " ORDER BY p.created_at DESC LIMIT $start, $per_page";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Récupérer les catégories pour le filtre
$categories = $pdo->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Gestion des produits</h1>
    <a href="product-edit.php" class="btn btn-primary">
        <i class='bx bx-plus'></i> Ajouter un produit
    </a>
</div>

<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Nom, description ou référence...">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Catégorie</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" 
                                <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="in_stock" <?= $status_filter === 'in_stock' ? 'selected' : '' ?>>En stock</option>
                    <option value="out_of_stock" <?= $status_filter === 'out_of_stock' ? 'selected' : '' ?>>Rupture de stock</option>
                    <option value="featured" <?= $status_filter === 'featured' ? 'selected' : '' ?>>En vedette</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class='bx bx-filter-alt'></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Liste des produits -->
<div class="card">
    <div class="card-body p-0">
        <?php if (count($products) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Référence</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): 
                            // Récupérer l'image principale du produit
                            $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
                            $stmt->execute([$product['id']]);
                            $primary_image = $stmt->fetch();
                            $image_url = $primary_image ? '../' . $primary_image['image_url'] : '../images/no-image.png';
                            
                            // Déterminer la classe de statut du stock
                            $stock_class = $product['quantity'] > 0 ? 'success' : 'danger';
                            $stock_text = $product['quantity'] > 0 ? $product['quantity'] . ' en stock' : 'Rupture';
                            
                            // Déterminer si le produit est en vedette
                            $featured_badge = $product['is_featured'] ? '<span class="badge bg-warning text-dark ms-2">Vedette</span>' : '';
                        ?>
                            <tr>
                                <td>
                                    <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($product['name']) ?></div>
                                    <small class="text-muted"><?= substr(strip_tags($product['description']), 0, 50) ?>...</small>
                                    <?= $featured_badge ?>
                                </td>
                                <td><?= $product['sku'] ?: '-' ?></td>
                                <td><?= $product['category_name'] ?: 'Non catégorisé' ?></td>
                                <td class="fw-bold"><?= number_format($product['price'], 0, ',', ' ') ?> FCFA</td>
                                <td>
                                    <span class="badge bg-<?= $stock_class ?>">
                                        <?= $stock_text ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $product['is_active'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="product-edit.php?id=<?= $product['id'] ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Modifier">
                                            <i class='bx bx-edit-alt'></i>
                                        </a>
                                        <a href="#" 
                                           onclick="return confirmDelete(event)" 
                                           data-href="product-delete.php?id=<?= $product['id'] ?>" 
                                           class="btn btn-outline-danger" 
                                           data-bs-toggle="tooltip" 
                                           title="Supprimer">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                        <a href="../product.php?id=<?= $product['id'] ?>" 
                                           target="_blank" 
                                           class="btn btn-outline-secondary" 
                                           data-bs-toggle="tooltip" 
                                           title="Voir sur le site">
                                            <i class='bx bx-link-external'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="p-3 border-top">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_filter) ? '&category=' . $category_filter : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" aria-label="Précédent">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_filter) ? '&category=' . $category_filter : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_filter) ? '&category=' . $category_filter : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" aria-label="Suivant">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center p-5">
                <div class="mb-3">
                    <i class='bx bx-package text-muted' style="font-size: 5rem;"></i>
                </div>
                <h4 class="text-muted">Aucun produit trouvé</h4>
                <p class="text-muted">Aucun produit ne correspond à vos critères de recherche.</p>
                <a href="products.php" class="btn btn-primary mt-2">Réinitialiser les filtres</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Script pour la confirmation de suppression -->
<script>
function confirmDelete(event) {
    event.preventDefault();
    const deleteUrl = event.target.closest('a').getAttribute('data-href');
    
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.')) {
        window.location.href = deleteUrl;
    }
    
    return false;
}

// Activer les tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
