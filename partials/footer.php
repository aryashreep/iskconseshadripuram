<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div class="footer-brand">
        <a href="<?php echo BASE_URL; ?>" class="logo">
          <img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON The Palace Temple of Lord Jagannath" class="logo-img">
          <div class="logo-text">
            <span class="logo-title logo-title-full">ISKCON</span>
            <span class="logo-subtitle-full">The Palace Temple of Lord Jagannath</span>
          </div>
        </a>
        <p>
          ISKCON The Palace Temple of Lord Jagannath, Seshadripuram is a branch of ISKCON Juhu, 
          dedicated to propagating spiritual knowledge and preserving Vedic culture 
          under the guidance of His Divine Grace A.C. Bhaktivedanta Swami Prabhupada.
        </p>
        <div class="footer-social">
          <a href="https://www.facebook.com/sjmblr" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://www.instagram.com/iskcon_seshadripuram" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="https://www.youtube.com/@ISKCONSeshadripuramBengaluru" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>

        </div>
      </div>



      <!-- We Offer -->
      <div>
        <h4>We Offer</h4>
        <div class="footer-links">
          <a href="services/sunday-feast">Sunday Feast</a>
          <a href="services/life-membership">Life Membership</a>
          <a href="forums">Forums</a>
          <a href="services/siksha">Siksha – Bhakti Steps</a>
          <a href="services/function-hall">Function Hall</a>
          <a href="services/corporate-programs">Corporate Programs</a>
          <a href="services/govindas-prasadam">Govinda's Prasadam</a>
        </div>
      </div>

      <!-- Bookings -->
      <div>
        <h4>Bookings</h4>
        <div class="footer-links">
          <a href="booking/puja">Puja Booking</a>
          <a href="booking/yagya">Yagya Booking</a>
          <a href="booking/guest-house">Guest House Booking</a>
          <a href="booking">All Bookings</a>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4>Quick Links</h4>
        <div class="footer-links">
          <a href="yatra/">Yatra</a>
          <a href="festivals/">Festivals</a>
          <a href="blogs">Blogs</a>
          <a href="darshan">Gallery</a>
          <a href="donate">Donate</a>
          <a href="contact">Contact Us</a>
        </div>
      </div>

      <!-- Contact -->
      <div>
        <h4>Contact Us</h4>
        <div class="footer-contact">
          <p>
            <i class="fas fa-map-marker-alt"></i>
            159, 1st Main road, Beside TRUGAS,<br>
            Seshadripuram, Bengaluru - 560020
          </p>
          <p>
            <i class="fas fa-phone-alt"></i>
            +91 99860 77269
          </p>            <p>
              <i class="fas fa-envelope"></i>
              info@iskconseshadripuram.org
            </p>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?php echo date('Y'); ?> ISKCON The Palace Temple of Lord Jagannath, Seshadripuram. All rights reserved.</span>
      <div class="footer-bottom-links">
        <a href="<?php echo BASE_URL; ?>privacy-policy">Privacy Policy</a>
        <a href="<?php echo BASE_URL; ?>terms-of-service">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

<a href="#" class="back-to-top" aria-label="Back to top">
  <i class="fas fa-chevron-up"></i>
</a>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="assets/js/cart.js"></script>
<script src="assets/js/main.js"></script>
<!-- Schema.org Structured Data -->
<?php if (file_exists(__DIR__ . '/../partials/schema.php')) include __DIR__ . '/../partials/schema.php'; ?>
</body>
</html>
