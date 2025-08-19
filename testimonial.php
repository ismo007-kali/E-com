<?php require_once 'header.php'; ?>

<!-- Témoignages Section -->
<section class="client_section layout_padding">
  <div class="container">
    <div class="heading_container heading_center">
      <h2>Témoignages Clients</h2>
    </div>
  </div>
  <div class="container px-0">
    <div id="customCarousel2" class="carousel carousel-fade" data-ride="carousel">
      <div class="carousel-inner">
        <?php
        $testimonials = [
          [
            'text' => 'Excellent service et produits de qualité. Je recommande vivement MODE ET TENDANCE pour tous vos accessoires.',
            'name' => 'Aminata Diallo',
            'role' => 'Cliente fidèle',
            'active' => true
          ],
          [
            'text' => 'Service client exceptionnel et livraison rapide. Mes achats correspondent toujours à mes attentes.',
            'name' => 'Ibrahim Moussa', 
            'role' => 'Client satisfait',
            'active' => false
          ],
          [
            'text' => 'Large choix d\'accessoires à des prix abordables. La qualité est toujours au rendez-vous.',
            'name' => 'Fatima Ousmane',
            'role' => 'Cliente régulière',
            'active' => false
          ]
        ];
        
        foreach($testimonials as $testimonial): ?>
          <div class="carousel-item <?php echo $testimonial['active'] ? 'active' : ''; ?>">
            <div class="box">
              <div class="client_info">
                <div class="client_name">
                  <h5><?php echo $testimonial['name']; ?></h5>
                  <h6><?php echo $testimonial['role']; ?></h6>
                </div>
              </div>
              <div class="detail-box">
                <p>"<?php echo $testimonial['text']; ?>"</p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="carousel_btn-box">
        <a class="carousel-control-prev" href="#customCarousel2" role="button" data-slide="prev">
          <i class="fa fa-arrow-left" aria-hidden="true"></i>
          <span class="sr-only">Précédent</span>
        </a>
        <a class="carousel-control-next" href="#customCarousel2" role="button" data-slide="next">
          <i class="fa fa-arrow-right" aria-hidden="true"></i>
          <span class="sr-only">Suivant</span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once 'footer.php'; ?>