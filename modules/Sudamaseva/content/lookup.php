<?php
/**
 * Sudamaseva Module — Donor Lookup Page (Public)
 *
 * Entry page for existing donors to find their records by phone number or legacy ID.
 * After finding the donor, redirects to the dashboard page.
 */

$pageTitle = 'Sudamaseva — Find Your Seva';
$metaDescription = 'Access your Sudamaseva donation history, view payment schedule, and make monthly contributions.';
$pageType = 'sudamaseva';
include __DIR__ . '/../../Kernel/partials/header.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Find Your Seva</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>sudamaseva">Sudamaseva</a>
      <span>›</span>
      <span>Find My Seva</span>
    </div>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <div class="row" style="max-width:600px; margin:0 auto;">
      <div class="reveal" style="text-align:center; margin-bottom:var(--space-2xl);">
        <div style="font-size:48px; margin-bottom:var(--space-md);">🔍</div>
        <h2>Access Your Seva Records</h2>
        <p style="color:var(--text-light); max-width:480px; margin:0 auto;">
          Enter the phone number or ID you used when you registered for Sudamaseva.
          We'll find your records so you can view your payment history and make contributions.
        </p>
      </div>

      <!-- Lookup Form -->
      <div class="donate-form-card reveal" style="margin-bottom:var(--space-2xl);">
        <form id="lookupForm" autocomplete="on" style="padding:var(--space-lg);">
          <!-- Honeypot Field -->
          <div style="display:none;" aria-hidden="true">
            <input type="text" id="middleNameHP" name="middle_name" autocomplete="off" tabindex="-1">
          </div>

          <div class="form-group">
            <label for="lookupQuery">Phone Number or ID</label>
            <input
              type="text"
              id="lookupQuery"
              name="query"
              class="form-control"
              placeholder="e.g. 9876543210 or old ID number"
              required
              autofocus
              style="font-size:18px; padding:14px 16px; text-align:center;"
            >
            <div style="font-size:12px; color:var(--text-light); margin-top:6px;">
              <i class="fas fa-info-circle"></i>
              Enter the phone number you registered with, or your old Sudamaseva ID.
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width:100%; margin-top:var(--space-lg);" id="lookupBtn">
            <i class="fas fa-search"></i> Find My Seva
          </button>

          <div class="donate-secure" style="margin-top:var(--space-lg);">
            <i class="fas fa-shield-alt"></i>
            <span>Your information is kept private and secure.</span>
          </div>
        </form>

        <!-- Loading -->
        <div class="donate-loading" id="lookupLoading" style="display:none;">
          <div class="donate-loading-spinner"></div>
          <p>Searching for your records...</p>
        </div>

        <!-- Error -->
        <div id="lookupError" style="display:none; padding:var(--space-lg);">
          <div class="alert alert-danger" style="text-align:center; margin:0;">
            <i class="fas fa-exclamation-circle" style="margin-right:8px;"></i>
            <span id="lookupErrorMessage"></span>
          </div>
        </div>
      </div>

      <!-- New Registration CTA -->
      <div class="reveal" style="text-align:center; padding:var(--space-lg); background:var(--cream); border-radius:var(--radius-lg);">
        <h4 style="margin-bottom:var(--space-sm);">New to Sudamaseva?</h4>
        <p style="color:var(--text-light); font-size:14px; margin-bottom:var(--space-md);">
          Start your monthly seva subscription today.
        </p>
        <a href="<?php echo BASE_URL; ?>sudamaseva" class="btn btn-primary">
          <i class="fas fa-sync-alt"></i> Subscribe Now
        </a>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('lookupForm');
  var input = document.getElementById('lookupQuery');
  var btn = document.getElementById('lookupBtn');
  var loading = document.getElementById('lookupLoading');
  var errorDiv = document.getElementById('lookupError');
  var errorMsg = document.getElementById('lookupErrorMessage');

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    var query = input.value.trim();
    if (!query) {
      showError('Please enter a phone number or ID.');
      return;
    }

    // Clear previous errors
    hideError();
    btn.disabled = true;
    loading.style.display = 'flex';

    var middleName = document.getElementById('middleNameHP').value;

    fetch('<?php echo BASE_URL; ?>api/sudamaseva/lookup', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ query: query, middle_name: middleName })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      btn.disabled = false;
      loading.style.display = 'none';

      if (data.found) {
        // Redirect to dashboard
        window.location.href = data.redirect_url;
      } else {
        showError(data.error || 'No donor found with that information.');
      }
    })
    .catch(function(err) {
      btn.disabled = false;
      loading.style.display = 'none';
      showError('An error occurred. Please try again.');
      console.error('Lookup error:', err);
    });
  });

  // Allow Enter key to submit
  input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      form.dispatchEvent(new Event('submit'));
    }
  });

  function showError(msg) {
    errorMsg.textContent = msg;
    errorDiv.style.display = 'block';
  }

  function hideError() {
    errorDiv.style.display = 'none';
    errorMsg.textContent = '';
  }
});
</script>

<style>
#lookupForm .form-control:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(200,107,31,0.15);
}

#lookupLoading {
  position: relative;
  min-height: 100px;
  background: rgba(255,255,255,0.9);
  border-radius: var(--radius-lg);
}
</style>

<?php include __DIR__ . '/../../Kernel/partials/footer.php'; ?>
