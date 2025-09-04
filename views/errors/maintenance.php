<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance en cours - <?= SITE_NAME ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }
        .maintenance-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 3rem 2rem;
            margin-top: 5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 2rem;
        }
        .maintenance-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #6c757d;
        }
        .contact-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
        }
        .countdown {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <img src="<?= ASSETS_URL ?>images/logo/logo.png" alt="<?= SITE_NAME ?>" class="logo">
        
        <h1>Site en maintenance</h1>
        
        <div class="maintenance-message">
            <p>Notre site est actuellement en cours de maintenance pour améliorer votre expérience.</p>
            <p>Nous nous excusons pour la gêne occasionnée et vous remercions de votre compréhension.</p>
        </div>
        
        <div class="countdown" id="countdown">Réessayez dans : <span id="timer">05:00</span></div>
        
        <div class="contact-info">
            <p>Pour toute urgence, vous pouvez nous contacter à l'adresse :</p>
            <p><a href="mailto:<?= SITE_EMAIL ?>" style="color: #dc3545; text-decoration: none;"><?= SITE_EMAIL ?></a></p>
        </div>
    </div>

    <script>
        // Compte à rebours de 5 minutes
        let timeLeft = 5 * 60; // 5 minutes en secondes
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                // Recharger la page quand le compte à rebours est terminé
                window.location.reload();
            } else {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            }
        }
        
        // Démarrer le compte à rebours
        updateTimer();
    </script>
</body>
</html>
