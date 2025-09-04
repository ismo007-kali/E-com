<?php
require_once 'includes/config.php';

// Vérifier si c'est une édition de produit
$is_edit = isset($_GET['id']);
$product = null;
$product_id = null;
$page_title = $is_edit ? 'Modifier un produit' : 'Ajouter un produit';

// Récupérer les catégories pour le formulaire
$categories = $pdo->query("SELECT id, name, parent_id FROM categories ORDER BY name")->fetchAll();

// Fonction pour organiser les catégories en hiérarchie
function buildCategoryTree($categories, $parentId = null, $level = 0) {
    $branch = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parentId) {
            $children = buildCategoryTree($categories, $category['id'], $level + 1);
            $category['level'] = $level;
            $branch[] = $category;
            if (!empty($children)) {
                $branch = array_merge($branch, $children);
            }
        }
    }
    return $branch;
}

$category_tree = buildCategoryTree($categories);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)str_replace([' ', ','], ['', '.'], $_POST['price']);
    $compare_at_price = !empty($_POST['compare_at_price']) ? (float)str_replace([' ', ','], ['', '.'], $_POST['compare_at_price']) : null;
    $cost_per_item = !empty($_POST['cost_per_item']) ? (float)str_replace([' ', ','], ['', '.'], $_POST['cost_per_item']) : null;
    $sku = trim($_POST['sku']);
    $barcode = trim($_POST['barcode']);
    $quantity = (int)$_POST['quantity'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $seo_title = trim($_POST['seo_title']);
    $seo_description = trim($_POST['seo_description']);
    $seo_keywords = trim($_POST['seo_keywords']);
    
    // Validation des champs obligatoires
    $errors = [];
    if (empty($name)) {
        $errors[] = "Le nom du produit est obligatoire";
    }
    if (empty($price) || $price <= 0) {
        $errors[] = "Le prix du produit est invalide";
    }
    if ($quantity < 0) {
        $errors[] = "La quantité ne peut pas être négative";
    }
    
    // Si pas d'erreurs, on procède à l'enregistrement
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Préparation des données pour l'insertion/mise à jour
            $product_data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'compare_at_price' => $compare_at_price,
                'cost_per_item' => $cost_per_item,
                'sku' => $sku,
                'barcode' => $barcode,
                'quantity' => $quantity,
                'category_id' => $category_id,
                'is_active' => $is_active,
                'is_featured' => $is_featured,
                'is_bestseller' => $is_bestseller,
                'is_new' => $is_new,
                'seo_title' => $seo_title,
                'seo_description' => $seo_description,
                'seo_keywords' => $seo_keywords,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($is_edit) {
                // Mise à jour du produit existant
                $product_id = (int)$_GET['id'];
                $sql = "UPDATE products SET ";
                $set = [];
                foreach ($product_data as $key => $value) {
                    $set[] = "$key = :$key";
                }
                $sql .= implode(", ", $set) . " WHERE id = :id";
                $product_data['id'] = $product_id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($product_data);
                
                $message = "Le produit a été mis à jour avec succès";
            } else {
                // Insertion d'un nouveau produit
                $product_data['created_at'] = date('Y-m-d H:i:s');
                $product_data['slug'] = createSlug($name);
                
                $columns = implode(", ", array_keys($product_data));
                $placeholders = ":" . implode(", :", array_keys($product_data));
                
                $sql = "INSERT INTO products ($columns) VALUES ($placeholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($product_data);
                
                $product_id = $pdo->lastInsertId();
                $message = "Le produit a été ajouté avec succès";
            }
            
            // Gestion des images
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_dir = '../uploads/products/' . $product_id . '/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Supprimer les anciennes images si nécessaire
                if ($is_edit && isset($_POST['delete_images'])) {
                    $delete_ids = array_map('intval', $_POST['delete_images']);
                    if (!empty($delete_ids)) {
                        // Récupérer les chemins des images à supprimer
                        $placeholders = rtrim(str_repeat('?,', count($delete_ids)), ',');
                        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id IN ($placeholders)");
                        $stmt->execute($delete_ids);
                        $images_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        // Supprimer les fichiers physiques
                        foreach ($images_to_delete as $image_path) {
                            if (file_exists('../' . $image_path)) {
                                unlink('../' . $image_path);
                            }
                        }
                        
                        // Supprimer les entrées en base de données
                        $stmt = $pdo->prepare("DELETE FROM product_images WHERE id IN ($placeholders)");
                        $stmt->execute($delete_ids);
                    }
                }
                
                // Traiter les nouvelles images téléchargées
                $files = $_FILES['images'];
                $file_count = count($files['name']);
                
                for ($i = 0; $i < $file_count; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $file_name = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "", $files['name'][$i]);
                        $file_path = $upload_dir . $file_name;
                        $relative_path = 'uploads/products/' . $product_id . '/' . $file_name;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $file_path)) {
                            // Vérifier si c'est l'image principale
                            $is_primary = (isset($_POST['primary_image']) && $_POST['primary_image'] == $i) ? 1 : 0;
                            
                            // Si c'est la première image et qu'aucune image principale n'est définie, la définir comme principale
                            if ($is_primary || $pdo->query("SELECT COUNT(*) FROM product_images WHERE product_id = $product_id AND is_primary = 1")->fetchColumn() == 0) {
                                $is_primary = 1;
                            }
                            
                            // Désélectionner les autres images principales si nécessaire
                            if ($is_primary) {
                                $pdo->exec("UPDATE product_images SET is_primary = 0 WHERE product_id = $product_id");
                            }
                            
                            // Insérer l'image en base de données
                            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, position) VALUES (?, ?, ?, 0)");
                            $stmt->execute([$product_id, $relative_path, $is_primary]);
                        }
                    }
                }
            }
            
            // Gestion des variantes (à implémenter si nécessaire)
            
            $pdo->commit();
            
            // Rediriger vers la page du produit avec un message de succès
            $_SESSION['success_message'] = $message;
            header("Location: product-edit.php?id=$product_id");
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Une erreur est survenue lors de l'enregistrement du produit: " . $e->getMessage();
        }
    }
} elseif ($is_edit) {
    // Charger les données du produit pour édition
    $product_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error_message'] = "Produit introuvable";
        header("Location: products.php");
        exit();
    }
    
    // Charger les images du produit
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, position ASC, id ASC");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll();
}

// Fonction pour générer un slug à partir d'un nom
function createSlug($string) {
    $string = preg_replace('/[^\p{L}0-9-]+/u', '-', $string);
    $string = trim($string, '-');
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = strtolower($string);
    $string = preg_replace('/[^-a-z0-9]+/', '', $string);
    return $string;
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $page_title ?></h1>
    <div>
        <a href="products.php" class="btn btn-outline-secondary me-2">
            <i class='bx bx-arrow-back'></i> Retour à la liste
        </a>
        <?php if ($is_edit): ?>
            <a href="product-edit.php" class="btn btn-primary">
                <i class='bx bx-plus'></i> Ajouter un produit
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <h5 class="alert-heading">Erreurs</h5>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Informations de base -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations de base</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom du produit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                        <div class="invalid-feedback">Veuillez saisir un nom de produit.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Prix de vente (FCFA) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="price" name="price" 
                                       value="<?= isset($product['price']) ? number_format($product['price'], 0, '.', ' ') : '' ?>" required>
                                <div class="invalid-feedback">Veuillez saisir un prix valide.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="compare_at_price" class="form-label">Prix barré (FCFA)</label>
                                <input type="text" class="form-control" id="compare_at_price" name="compare_at_price"
                                       value="<?= isset($product['compare_at_price']) ? number_format($product['compare_at_price'], 0, '.', ' ') : '' ?>">
                                <small class="text-muted">Laissez vide si non applicable</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cost_per_item" class="form-label">Coût par article (FCFA)</label>
                                <input type="text" class="form-control" id="cost_per_item" name="cost_per_item"
                                       value="<?= isset($product['cost_per_item']) ? number_format($product['cost_per_item'], 0, '.', ' ') : '' ?>">
                                <small class="text-muted">Prix d'achat pour calcul de marge</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantité en stock</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0"
                                       value="<?= $product['quantity'] ?? 0 ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sku" class="form-label">Référence (SKU)</label>
                                <input type="text" class="form-control" id="sku" name="sku"
                                       value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="barcode" class="form-label">Code-barres (ISBN, UPC, etc.)</label>
                                <input type="text" class="form-control" id="barcode" name="barcode"
                                       value="<?= htmlspecialchars($product['barcode'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Images du produit -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Images du produit</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($product_images) && !empty($product_images)): ?>
                        <div class="row mb-4" id="product-images-container">
                            <?php foreach ($product_images as $index => $image): ?>
                                <div class="col-md-3 mb-3 image-container" data-image-id="<?= $image['id'] ?>">
                                    <div class="card position-relative">
                                        <img src="../<?= htmlspecialchars($image['image_url']) ?>" class="card-img-top" alt="Image du produit" style="height: 120px; object-fit: cover;">
                                        <div class="card-body p-2 text-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="primary_image" 
                                                       id="primary_<?= $image['id'] ?>" value="existing_<?= $image['id'] ?>" 
                                                       <?= $image['is_primary'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="primary_<?= $image['id'] ?>">
                                                    Image principale
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2 delete-image" 
                                                    data-image-id="<?= $image['id'] ?>">
                                                <i class='bx bx-trash'></i> Supprimer
                                            </button>
                                            <input type="hidden" name="existing_images[]" value="<?= $image['id'] ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="images" class="form-label">Ajouter des images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                        <div class="form-text">Sélectionnez une ou plusieurs images. La première image sera utilisée comme image principale.</div>
                    </div>
                    
                    <div id="image-preview" class="row d-none">
                        <!-- Les aperçus des nouvelles images seront ajoutés ici par JavaScript -->
                    </div>
                </div>
            </div>
            
            <!-- SEO -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Référencement (SEO)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="seo_title" class="form-label">Titre SEO</label>
                        <input type="text" class="form-control" id="seo_title" name="seo_title"
                               value="<?= htmlspecialchars($product['seo_title'] ?? '') ?>">
                        <div class="form-text">50-60 caractères recommandés</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="seo_description" class="form-label">Description SEO</label>
                        <textarea class="form-control" id="seo_description" name="seo_description" rows="2"><?= htmlspecialchars($product['seo_description'] ?? '') ?></textarea>
                        <div class="form-text">150-160 caractères recommandés</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="seo_keywords" class="form-label">Mots-clés SEO</label>
                        <input type="text" class="form-control" id="seo_keywords" name="seo_keywords"
                               value="<?= htmlspecialchars($product['seo_keywords'] ?? '') ?>">
                        <div class="form-text">Séparez les mots-clés par des virgules</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Colonne latérale -->
        <div class="col-lg-4">
            <!-- Statut -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statut</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" name="save" class="btn btn-primary">
                            <i class='bx bx-save'></i> Enregistrer
                        </button>
                        <?php if ($is_edit): ?>
                            <a href="#" class="btn btn-outline-secondary" onclick="window.open('../product.php?id=<?= $product_id ?>', '_blank'); return false;">
                                <i class='bx bx-link-external'></i> Voir sur le site
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?= (isset($product['is_active']) && $product['is_active']) || !$is_edit ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Produit actif</label>
                        </div>
                        <div class="form-text">Les produits inactifs ne sont pas visibles sur le site.</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                   <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_featured">Mettre en avant</label>
                        </div>
                        <div class="form-text">Afficher ce produit en page d'accueil.</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_bestseller" name="is_bestseller"
                                   <?= !empty($product['is_bestseller']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_bestseller">Meilleure vente</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_new" name="is_new"
                                   <?= !empty($product['is_new']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_new">Nouveauté</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Organisation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Organisation</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Catégorie</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Sélectionnez une catégorie</option>
                            <?php foreach ($category_tree as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                    <?= (isset($product['category_id']) && $product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= str_repeat('&nbsp;&nbsp;&nbsp;', $category['level']) ?><?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($is_edit): ?>
                        <div class="mb-3">
                            <label class="form-label">Date de création</label>
                            <p class="form-control-static">
                                <?= date('d/m/Y H:i', strtotime($product['created_at'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Dernière modification</label>
                            <p class="form-control-static">
                                <?= date('d/m/Y H:i', strtotime($product['updated_at'] ?? $product['created_at'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Champ caché pour les images à supprimer -->
    <input type="hidden" name="delete_images" id="delete_images" value="">
</form>

<!-- Modal de confirmation de suppression d'image -->
<div class="modal fade" id="deleteImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer cette image ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-image">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Valider les champs requis avant la soumission du formulaire
document.addEventListener('DOMContentLoaded', function() {
    // Désactiver la validation HTML5 par défaut
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Gestion de la suppression des images
    const deleteButtons = document.querySelectorAll('.delete-image');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteImageModal'));
    let imageToDelete = null;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            imageToDelete = this.getAttribute('data-image-id');
            deleteModal.show();
        });
    });
    
    document.getElementById('confirm-delete-image').addEventListener('click', function() {
        if (imageToDelete) {
            // Ajouter l'ID de l'image au champ caché des images à supprimer
            const deleteInput = document.getElementById('delete_images');
            const currentDeletes = deleteInput.value ? deleteInput.value.split(',') : [];
            
            if (!currentDeletes.includes(imageToDelete)) {
                currentDeletes.push(imageToDelete);
                deleteInput.value = currentDeletes.join(',');
                
                // Masquer le conteneur de l'image
                const imageContainer = document.querySelector(`.image-container[data-image-id="${imageToDelete}"]`);
                if (imageContainer) {
                    imageContainer.style.display = 'none';
                }
            }
            
            deleteModal.hide();
        }
    });
    
    // Aperçu des nouvelles images avant téléchargement
    const imageInput = document.getElementById('images');
    const imagePreview = document.getElementById('image-preview');
    
    imageInput.addEventListener('change', function() {
        imagePreview.innerHTML = ''; // Vider l'aperçu précédent
        imagePreview.classList.remove('d-none');
        
        if (this.files && this.files.length > 0) {
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 mb-3';
                        
                        col.innerHTML = `
                            <div class="card">
                                <img src="${e.target.result}" class="card-img-top" alt="Aperçu" style="height: 120px; object-fit: cover;">
                                <div class="card-body p-2 text-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="primary_image" 
                                               id="primary_new_${i}" value="${i}" ${i === 0 ? 'checked' : ''}>
                                        <label class="form-check-label" for="primary_new_${i}">
                                            Image principale
                                        </label>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        imagePreview.appendChild(col);
                    };
                    
                    reader.readAsDataURL(file);
                }
            }
        }
    });
    
    // Mise en forme du prix au format français
    const priceInputs = ['price', 'compare_at_price', 'cost_per_item'];
    
    priceInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            // Au focus, supprimer les espaces pour faciliter l'édition
            input.addEventListener('focus', function() {
                this.value = this.value.replace(/\s/g, '');
            });
            
            // À la perte du focus, formater le nombre avec des espaces comme séparateurs de milliers
            input.addEventListener('blur', function() {
                if (this.value) {
                    this.value = this.value.replace(/\s/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                }
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
