<?php
// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            flash("Cet email est déjà utilisé", 'error');
            redirect('register');
        }
        
        // Insérer le nouvel utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $password]);
        
        flash("Inscription réussie ! Vous pouvez maintenant vous connecter.", 'success');
        redirect('login');
    } catch(PDOException $e) {
        flash("Erreur lors de l'inscription", 'error');
        redirect('register');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription - eKOM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Inscription</h1>
        
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="flash-message <?= $_SESSION['flash']['type'] ?>">
                <?= escape($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div>
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">S'inscrire</button>
        </form>
        
        <p>Déjà un compte ? <a href="?page=login">Se connecter</a></p>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
