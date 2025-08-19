<?php require_once 'header.php'; ?>

<!-- Boutique Section -->
<section class="shop_section layout_padding" id="NSP">
  <div class="container">
    <div class="heading_container heading_center">
      <h2>Nos Produits</h2>
    </div>
    <div class="row">
      <?php
      $products = [
        ['img' => '1675180738185.png', 'name' => 'Sac en Cuir à Code', 'link' => '1675180740930.png'],
        ['img' => '1678297618032.png', 'name' => 'Sac en Cuir', 'link' => '1678297615468.png'],
        ['img' => '1681039167493.png', 'name' => 'Montre Dame', 'link' => '1681039175601.png'],
        ['img' => '1675343135320.png', 'name' => 'Sac en Bandoulière', 'link' => '1675343148893.png'],
        ['img' => '1695466490047.png', 'name' => 'Montre Homme', 'link' => '1695466490047.png'],
        ['img' => '1675171671126.png', 'name' => 'Boxer', 'link' => '1675171666915.png'],
        ['img' => '1695466490383.png', 'name' => 'Bracelet', 'link' => '1695466490395.jpg'],
        ['img' => '1675437271252.png', 'name' => 'Chaussures', 'link' => '1675437283862.png']
      ];
      
      foreach($products as $product): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="box">
            <a href="images/<?php echo $product['link']; ?>">
              <div class="img-box">
                <img src="images/<?php echo $product['img']; ?>" alt="<?php echo $product['name']; ?>">
              </div>
              <div class="detail-box">
                <h6><?php echo $product['name']; ?></h6>
              </div>
              <div class="new">
                <span>New</span>
              </div>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="btn-box">
      <a href="produt.php">Voir tous les produits</a>
    </div>
  </div>
</section>

<?php require_once 'footer.php'; ?>