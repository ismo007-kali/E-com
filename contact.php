<?php
require_once 'header.php';
?>
  <!-- end hero area -->

  <!-- contact section -->
  <section class="contact_section layout_padding">
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
          CONTACTEZ-NOUS
        </h2>
        <p>
          N'hésitez pas à nous contacter pour toute question ou demande d'information
    </div>
    <div class="row">
      <div class="col-md-6">
        <form class="contact-form" action="mailto:modetendanceny@gmail.com" method="post" enctype="text/plain">
          <div class="form-group">
            <input type="text" class="form-control" name="nom" placeholder="Votre Nom" required>
          </div>
          <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="Votre Email" required>
          </div>
          <div class="form-group">
            <input type="tel" class="form-control" name="telephone" placeholder="Votre Téléphone">
          </div>
          <div class="form-group">
            <textarea class="form-control" name="message" rows="5" placeholder="Votre Message" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>

        <div class="contact-info mt-4">
          <h4>Informations de Contact</h4>
          <div class="info-item">
            <i class="fa fa-map-marker"></i>
            <span>Qtr Ryad Plaque Avocat</span>
          </div>
          <div class="info-item">
            <i class="fa fa-phone"></i>
            <a href="tel:+22789735585">+227 89 73 55 85</a>
          </div>
          <div class="info-item">
            <i class="fa fa-envelope"></i>
            <a href="mailto:modetendanceny@gmail.com">modetendanceny@gmail.com</a>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="contact-photo-section">
          <h4>Notre Boutique</h4>
          <img src="images/1695466490395.jpg" alt="Notre boutique MODE ET TENDANCE" class="img-fluid">
        </div>

        <div class="map-section mt-4">
          <h4>Notre Localisation</h4>
          <div class="map-container">
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.835434509374!2d2.1734034!3d13.5086767!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTPCsDMwJzMxLjIiTiAywrAxMCcyNC4yIkU!5e0!3m2!1sfr!2sne!4v1234567890123!5m2!1sfr!2sne" 
              width="100%" 
              height="250" 
              style="border:0; border-radius: 10px;" 
              allowfullscreen="" 
              loading="lazy">
            </iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'footer.php'; ?>