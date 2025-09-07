<?php
require_once '../../includes/init.php';

// Vérifier si l'utilisateur est connecté
if (!$user->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'account/orders/';
    header('Location: ../../login.php');
    exit();
}

// Récupérer les commandes de l'utilisateur
$db = Database::getInstance();
$db->query("SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC", [':user_id' => $_SESSION['user_id']]);
$orders = $db->fetchAll();

// Titre de la page
$page_title = 'Mes commandes';

// Inclure l'en-tête
include '../../header.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-2" 
                             style="width: 80px; height: 80px; border-radius: 50%; font-size: 2rem;">
                            <?php 
                            $userData = $user->getUser();
                            echo strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1)); 
                            ?>
                        </div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($userData['email']); ?></small>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <a href="../" class="list-group-item list-group-item-action">
                        <i class="fa fa-user me-2"></i> Mon profil
                    </a>
                    <a href="#" class="list-group-item list-group-item-action active">
                        <i class="fa fa-shopping-bag me-2"></i> Mes commandes
                    </a>
                    <a href="../addresses/" class="list-group-item list-group-item-action">
                        <i class="fa fa-address-book me-2"></i> Mes adresses
                    </a>
                    <a href="../../logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fa fa-sign-out me-2"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mes commandes</h5>
                    <a href="../../shop.php" class="btn btn-light btn-sm">
                        <i class="fa fa-shopping-cart me-1"></i> Continuer mes achats
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fa fa-shopping-bag fa-4x text-muted"></i>
                            </div>
                            <h5>Vous n'avez pas encore passé de commande</h5>
                            <p class="text-muted">Parcourez nos produits et faites votre première commande !</p>
                            <a href="../../shop.php" class="btn btn-primary mt-3">
                                <i class="fa fa-shopping-cart me-2"></i> Voir nos produits
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° de commande</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                            <td><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> €</td>
                                            <td>
                                                <?php 
                                                // Harmonisation avec l'ENUM de la base: en_attente, traitement, expedie, livre, annule, rembourse
                                                $status = strtolower($order['status']);
                                                $status_class = 'bg-secondary';
                                                $status_labels = [
                                                    'en_attente' => 'En attente',
                                                    'traitement' => 'En cours de traitement',
                                                    'expedie'    => 'Expédiée',
                                                    'livre'      => 'Livrée',
                                                    'annule'     => 'Annulée',
                                                    'rembourse'  => 'Remboursée',
                                                ];
                                                switch ($status) {
                                                    case 'en_attente':
                                                        $status_class = 'bg-warning';
                                                        break;
                                                    case 'traitement':
                                                        $status_class = 'bg-info';
                                                        break;
                                                    case 'expedie':
                                                        $status_class = 'bg-primary';
                                                        break;
                                                    case 'livre':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'annule':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                    case 'rembourse':
                                                        $status_class = 'bg-secondary';
                                                        break;
                                                }
                                                $status_text = $status_labels[$status] ?? ucfirst($status);
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($status_text); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa fa-eye"></i> Voir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <nav aria-label="Navigation des commandes" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Précédent</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Suivant</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>
