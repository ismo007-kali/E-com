<?php
require_once '../includes/init.php';

// Vérifier si l'utilisateur est connecté
if (!$user->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'account/';
    header('Location: ../login.php');
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$userData = $user->getUser();

// Titre de la page
$page_title = 'Mon compte';

// Inclure l'en-tête
include '../header.php';
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
                            <?php echo strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1)); ?>
                        </div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($userData['email']); ?></small>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action active">
                        <i class="fa fa-user me-2"></i> Mon profil
                    </a>
                    <a href="orders/" class="list-group-item list-group-item-action">
                        <i class="fa fa-shopping-bag me-2"></i> Mes commandes
                    </a>
                    <a href="addresses/" class="list-group-item list-group-item-action">
                        <i class="fa fa-address-book me-2"></i> Mes adresses
                    </a>
                    <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fa fa-sign-out me-2"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Mon profil</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo htmlspecialchars($_SESSION['success_message']); 
                            unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="update-profile.php" method="POST" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($userData['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($userData['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
                            <small class="form-text text-muted">Pour modifier votre adresse email, veuillez contacter le support.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="address" name="address"
                                   value="<?php echo htmlspecialchars($userData['address'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?php echo htmlspecialchars($userData['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                       value="<?php echo htmlspecialchars($userData['postal_code'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="country" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="country" name="country"
                                       value="<?php echo htmlspecialchars($userData['country'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="change-password.php" class="btn btn-outline-secondary me-md-2">
                                <i class="fa fa-lock me-1"></i> Changer le mot de passe
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script de validation du formulaire -->
<script>
// Désactiver la soumission des formulaires en cas de champs invalides
(function () {
  'use strict'
  
  // Récupérer tous les formulaires auxquels nous voulons appliquer les styles de validation Bootstrap
  var forms = document.querySelectorAll('.needs-validation')
  
  // Boucle sur les formulaires pour empêcher la soumission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<?php include '../footer.php'; ?>
