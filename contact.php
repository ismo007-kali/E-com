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
        </p>
      </div>
    </div>
    
    <div class="container">
      <div class="row">
        <!-- Colonne 1: Formulaire de contact et informations -->
        <div class="col-md-6">
          <div class="contact-form-container">
            <h4 class="contact-subtitle">Envoyez-nous un message</h4>
            <form action="#" method="post" class="contact-form">
              <div class="form-group">
                <input type="text" class="form-control" placeholder="Votre nom" name="name" required>
              </div>
              <div class="form-group">
                <input type="email" class="form-control" placeholder="Votre email" name="email" required>
              </div>
              <div class="form-group">
                <input type="tel" class="form-control" placeholder="Votre téléphone" name="phone">
              </div>
              <div class="form-group">
                <textarea class="form-control message-box" rows="5" placeholder="Votre message" name="message" required></textarea>
              </div>
              <div class="btn-box">
                <button type="submit" class="btn-contact">
                  <i class="fa fa-paper-plane"></i> Envoyer le message
                </button>
              </div>
            </form>
            
            <!-- Informations de contact -->
            <div class="contact-info mt-4">
              <h4 class="contact-subtitle">Nos coordonnées</h4>
              <div class="contact-item">
                <i class="fa fa-map-marker"></i>
                <span>123 Rue de la Mode, 75001 Paris, France</span>
              </div>
              <div class="contact-item">
                <i class="fa fa-phone"></i>
                <span>+33 1 23 45 67 89</span>
              </div>
              <div class="contact-item">
                <i class="fa fa-envelope"></i>
                <span>contact@modeettendance.fr</span>
              </div>
              <div class="contact-item">
                <i class="fa fa-clock-o"></i>
                <span>Lun - Sam: 9h00 - 19h00</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Colonne 2: Carte et photo -->
        <div class="col-md-6">
          <div class="map-photo-container">
            <!-- Section photo -->
            <div class="contact-photo-section">
              <h4 class="contact-subtitle">Notre boutique</h4>
              <div class="photo-upload-area">
                <img src="images/contact-store.jpg" alt="Notre boutique MODE ET TENDANCE" class="contact-store-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="photo-placeholder" style="display: none;">
                  <i class="fa fa-camera"></i>
                  <p>Photo de la boutique</p>
                  <small>Ajoutez une photo de votre magasin ici</small>
                </div>
              </div>
            </div>
            
            <!-- Section carte -->
            <div class="map-section mt-4">
              <h4 class="contact-subtitle">Notre localisation</h4>
              <div class="map-container">
                <iframe 
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9916256937595!2d2.292292615674073!3d48.85837007928746!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e2964e34e2d%3A0x8ddca9ee380ef7e0!2sEiffel%20Tower!5e0!3m2!1sen!2sfr!4v1642678901234!5m2!1sen!2sfr" 
                  width="100%" 
                  height="300" 
                  style="border:0; border-radius: 10px;" 
                  allowfullscreen="" 
                  loading="lazy" 
                  referrerpolicy="no-referrer-when-downgrade">
                </iframe>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- end contact section -->

<?php
require_once 'footer.php';
?>