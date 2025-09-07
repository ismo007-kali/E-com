<?php
http_response_code(404);
require_once __DIR__ . '/../includes/layout/header.php';
?>

<section class="contact_section layout_padding">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="heading_container heading_center">
                    <h1 style="font-size: 6rem; font-weight: bold; color: #f7444e;">404</h1>
                    <h2>Oops! Page non trouvée.</h2>
                </div>
                <p class="lead mt-4">
                    Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
                </p>
                <a href="index.php" class="btn btn-primary mt-4">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/layout/footer.php';
?>
