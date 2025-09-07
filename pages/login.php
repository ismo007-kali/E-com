<?php
require_once __DIR__ . '/../includes/init.php';

// Rediriger les utilisateurs déjà connectés
if ($user->isLoggedIn()) {
    if ($user->isAdmin()) {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$errors = [];
$email = '';

// Vérifier si l'utilisateur vient de s'inscrire
$registered = isset($_GET['registered']) ? (int)$_GET['registered'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email)) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format d\'email invalide';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis';
    }
    
    if (empty($errors)) {
        $user = new User();
        if ($user->login($email, $password)) {
            // Connexion réussie
            if ($user->isAdmin()) {
                header('Location: ../admin/dashboard.php');
            } else {
                // Rediriger vers la page précédente ou l'accueil
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
            }
            exit();
        } else {
            $errors['general'] = 'Email ou mot de passe incorrect';
        }
    }
}
?>

<?php include __DIR__ . '/../includes/layout/header.php'; ?>

<!-- Contact section -->
<section class="contact_section layout_padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center mb-0">Connexion</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($registered): ?>
                            <div class="alert alert-success">
                                Inscription réussie ! Vous pouvez maintenant vous connecter.
                            </div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" required
                                       value="<?php echo htmlspecialchars($email); ?>">
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control <?php echo !empty($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="password" name="password" required>
                                <?php if (!empty($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                <?php endif; ?>
                                <div class="text-end mt-2">
                                    <a href="forgot-password.php" class="text-muted small">Mot de passe oublié ?</a>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Se souvenir de moi</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Se connecter</button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p>Pas encore de compte ? <a href="register.php">Inscrivez-vous ici</a></p>
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
