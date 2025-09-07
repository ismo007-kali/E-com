<?php
require_once __DIR__ . '/../includes/init.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'country' => trim($_POST['country'] ?? '')
    ];

    // Validation
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'Le prénom est requis';
    }
    
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Le nom est requis';
    }
    
    if (empty($data['email'])) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format d\'email invalide';
    }
    
    if (empty($data['password'])) {
        $errors['password'] = 'Le mot de passe est requis';
    } elseif (strlen($data['password']) < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
    } elseif ($data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
    }
    
    // Vérifier si l'email existe déjà
    if (empty($errors['email'])) {
        $user = new User();
        $db = Database::getInstance();
        $db->query("SELECT id FROM users WHERE email = :email", [':email' => $data['email']]);
        if ($db->fetch()) {
            $errors['email'] = 'Cet email est déjà utilisé';
        }
    }
    
    if (empty($errors)) {
        $user = new User();
        if ($user->register($data)) {
            $success = true;
            // Rediriger vers la page de connexion après inscription réussie
            header('Location: login.php?registered=1');
            exit();
        } else {
            $errors['general'] = 'Une erreur est survenue lors de l\'inscription';
        }
    }
}
?>

<?php include __DIR__ . '/../includes/layout/header.php'; ?>

<!-- Contact section -->
<section class="contact_section layout_padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center mb-0">Créer un compte</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control <?php echo !empty($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                           id="first_name" name="first_name" required 
                                           value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>">
                                    <?php if (!empty($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Nom *</label>
                                    <input type="text" class="form-control <?php echo !empty($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                           id="last_name" name="last_name" required
                                           value="<?php echo htmlspecialchars($data['last_name'] ?? ''); ?>">
                                    <?php if (!empty($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" required
                                       value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>">
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
                                    <input type="password" class="form-control <?php echo !empty($errors['password']) ? 'is-invalid' : ''; ?>" 
                                           id="password" name="password" required>
                                    <?php if (!empty($errors['password'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Minimum 8 caractères</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                                    <input type="password" class="form-control <?php echo !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" name="confirm_password" required>
                                    <?php if (!empty($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Informations de livraison (optionnel)</h5>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="address" name="address"
                                       value="<?php echo htmlspecialchars($data['address'] ?? ''); ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                           value="<?php echo htmlspecialchars($data['city'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="postal_code" class="form-label">Code postal</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code"
                                           value="<?php echo htmlspecialchars($data['postal_code'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="country" class="form-label">Pays</label>
                                    <input type="text" class="form-control" id="country" name="country"
                                           value="<?php echo htmlspecialchars($data['country'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Créer mon compte</button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p>Déjà un compte ? <a href="login.php">Connectez-vous ici</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add validation script -->
<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
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

<?php include __DIR__ . '/../includes/layout/footer.php'; ?>
