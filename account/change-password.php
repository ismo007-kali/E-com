<?php
require_once '../includes/init.php';

// Vérifier si l'utilisateur est connecté
if (!$user->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password)) {
        $errors['current_password'] = 'Le mot de passe actuel est requis';
    }
    
    if (empty($new_password)) {
        $errors['new_password'] = 'Le nouveau mot de passe est requis';
    } elseif (strlen($new_password) < 8) {
        $errors['new_password'] = 'Le mot de passe doit contenir au moins 8 caractères';
    }
    
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
    }
    
    // Vérifier le mot de passe actuel
    if (empty($errors['current_password'])) {
        $db = Database::getInstance();
        $db->query("SELECT password FROM users WHERE id = :id", [':id' => $_SESSION['user_id']]);
        $user_data = $db->fetch();
        
        if (!$user_data || !password_verify($current_password, $user_data['password'])) {
            $errors['current_password'] = 'Le mot de passe actuel est incorrect';
        }
    }
    
    // Si pas d'erreurs, mettre à jour le mot de passe
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $params = [
            ':id' => $_SESSION['user_id'],
            ':password' => $hashed_password
        ];
        
        if ($db->query($sql, $params)) {
            // Journaliser le changement de mot de passe
            $logger = new Logger();
            $logger->log("Changement de mot de passe", "L'utilisateur ID: {$_SESSION['user_id']} a changé son mot de passe");
            
            $success = true;
            
            // Déconnecter l'utilisateur après le changement de mot de passe
            session_unset();
            session_destroy();
            
            // Rediriger vers la page de connexion avec un message de succès
            $_SESSION['success_message'] = 'Votre mot de passe a été modifié avec succès. Veuillez vous reconnecter.';
            header('Location: ../login.php');
            exit();
        } else {
            $errors['general'] = 'Une erreur est survenue lors de la mise à jour du mot de passe. Veuillez réessayer.';
        }
    }
}

// Titre de la page
$page_title = 'Changer le mot de passe';

// Inclure l'en-tête
include '../header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Changer mon mot de passe</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Votre mot de passe a été modifié avec succès. Vous allez être redirigé vers la page de connexion.
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($errors['general']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="change-password.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control <?php echo !empty($errors['current_password']) ? 'is-invalid' : ''; ?>" 
                                           id="current_password" name="current_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <?php if (!empty($errors['current_password'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['current_password']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control <?php echo !empty($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                           id="new_password" name="new_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <?php if (!empty($errors['new_password'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['new_password']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div id="passwordHelp" class="form-text">
                                    Le mot de passe doit contenir au moins 8 caractères.
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control <?php echo !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <?php if (!empty($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fa fa-arrow-left me-1"></i> Retour
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour afficher/masquer les mots de passe -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher/masquer les mots de passe
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Validation du formulaire
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    }
});
</script>

<?php include '../footer.php'; ?>
