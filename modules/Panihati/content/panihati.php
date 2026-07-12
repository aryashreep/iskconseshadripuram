<?php
$pageTitle = 'Panihati Chips & Dahi Utsav Yatra - Registration';
$metaDescription = 'Register for Panihati Chips Dahi Utsav Yatra at ISKCON The Palace Temple of Lord Jagannath, Bangalore. Bus and own vehicle options available. Book your spot now.';
$pageType = 'yatra';
require_once '../config.php';
require_once __DIR__ . '/../panihati-helpers.php';

try {
    $db = getDB();
    // Fetch dynamic options
    $stmt = $db->query("SELECT name FROM panihati_bhakti_sadans WHERE is_active = 1 ORDER BY name ASC");
    $dbSadans = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $db->query("SELECT name FROM panihati_pickup_locations WHERE is_active = 1 ORDER BY name ASC");
    $dbPickups = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $dbSadans = [];
    $dbPickups = [];
}

// Get dynamic pricing
$pricing = getPanihatiPricing();
$busAdultLabel = getPanihatiRateLabel($pricing, 'bus');
$vehicleAdultLabel = getPanihatiRateLabel($pricing, 'own_vehicle');
$kidsPrice = $pricing['bus_kid_price']; // Same for both modes

include '../partials/header.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/panihati-banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Panihati Yatra Registration</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>yatra">Yatra</a>
      <span>›</span>
      <span>Panihati Yatra</span>
    </div>
  </div>
</section>

<!-- Main content section -->
<section class="page-content">
  <div class="container" style="max-width: 1100px;">
    <div class="yatra-layout" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: var(--space-2xl); align-items: start;">
      
      <!-- Left Column: Details, Gallery, Venue -->
      <div class="yatra-details">

        <!-- Top Banner Image -->
        <div class="reveal" style="margin-bottom: var(--space-2xl); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); height: 350px;">
          <img src="<?php echo BASE_URL; ?>assets/images/banners/panihati-banner1.jpg" alt="Panihati Yatra Banner" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        
        <!-- About Event -->
        <div class="yatra-about reveal" style="margin-bottom: var(--space-2xl); background: var(--white); padding: var(--space-xl); border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
          <h2 style="font-family: var(--font-heading); color: var(--text-dark); border-bottom: 2px solid var(--primary); display: inline-block; padding-bottom: var(--space-xs); margin-top: 0; margin-bottom: var(--space-md);">About the Event</h2>
          <p style="line-height: 1.8; color: var(--text); margin-bottom: var(--space-md);">
            Panihati is the name of a village located in the state of West Bengal, on the banks of River Ganges (10 miles north of Kolkata). It was one of the leading trade centres in earlier days when the river route was the main means of communication. A special rice variety called Peneti was imported at this place from Jessore in Bangladesh. Probably the name Panihati might have been derived from this trade connection. 
          </p>
          <p style="line-height: 1.8; color: var(--text);">
            Once upon a time, this place was the centre of worship of the Buddhist Tantrics and the Kapalikas. But later, in the sixteenth century, when Sri Chaitanya Mahaprabhu appeared to propagate the Sankirtana Movement, Panihati became a major centre of the Gaudiya Vaishnavas. The residential quarters of Sri Raghava Pandita (one of the associates of Chaitanya Mahaprabhu) which still exists in Panihati.
          </p>
        </div>

        <!-- Gallery Section (Remaining Images) -->
        <div class="yatra-gallery reveal" style="margin-bottom: var(--space-2xl);">
          <div class="gallery-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
            <div class="gallery-item" style="border-radius: var(--radius-md); overflow: hidden; height: 180px; box-shadow: var(--shadow-sm);">
              <img src="<?php echo BASE_URL; ?>assets/images/banners/panihati-banner2.jpg" alt="Panihati Utsav Chida Dahi" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="gallery-item" style="border-radius: var(--radius-md); overflow: hidden; height: 180px; box-shadow: var(--shadow-sm);">
              <img src="<?php echo BASE_URL; ?>assets/images/banners/panihati-banner3.jpg" alt="Panihati Utsav Kaveri River" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
          </div>
        </div>

        <!-- Venue Section -->
        <div class="yatra-venue reveal" style="background: var(--cream); padding: var(--space-xl); border-radius: var(--radius-lg); border: 1px solid var(--border);">
          <h3 style="font-family: var(--font-heading); color: var(--text-dark); margin-bottom: var(--space-md); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-map-marker-alt" style="color: var(--maroon);"></i> Venue Details
          </h3>
          <div style="font-size: var(--font-size-md); font-weight: 600; color: var(--text-dark); margin-bottom: var(--space-xs);">
            Dodda Gosai Ghat, Srirangapatna
          </div>
          <div style="color: var(--text-light); line-height: 1.6; margin-bottom: var(--space-md);">
            Situated beautifully on the scenic banks of the sacred Kaveri River.
          </div>
          <div style="background: var(--white); padding: var(--space-md); border-radius: var(--radius-md); font-size: var(--font-size-sm); color: var(--text);">
            <i class="fas fa-info-circle" style="color: var(--primary); margin-right: 6px;"></i>
            <strong>Note:</strong> Devotees traveling by bus will be pick up from their selected pickup locations. Devotees using own vehicle should report directly to the venue by 8:30 AM.
          </div>
        </div>

      </div>

      <!-- Right Column: Interactive Registration Form -->
      <div class="yatra-sidebar reveal" style="position: sticky; top: 100px;">
        <div class="registration-card" style="background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--border); border-top: 4px solid var(--primary); box-shadow: var(--shadow-md); padding: var(--space-xl);">
          <h3 style="font-family: var(--font-heading); color: var(--text-dark); border-bottom: 2px solid var(--primary); padding-bottom: var(--space-xs); margin-top: 0; margin-bottom: var(--space-lg); text-align: center;">Yatra Registration</h3>
          
          <form id="panihatiForm">
            <!-- Honeypot Field -->
            <div style="display:none;" aria-hidden="true">
              <input type="text" id="middleNameHP" name="middle_name" autocomplete="off" tabindex="-1">
            </div>
            <!-- Basic Details -->
            <div class="form-group" style="margin-bottom: var(--space-md);">
              <label for="regName" style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">Full Name *</label>
              <input type="text" id="regName" required placeholder="Enter your full name">
            </div>

            <div class="form-group" style="margin-bottom: var(--space-md);">
              <label for="regPhone" style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">Phone Number *</label>
              <input type="tel" id="regPhone" required placeholder="e.g. +91 9876543210">
            </div>

            <div class="form-group" style="margin-bottom: var(--space-md);">
              <label for="regEmail" style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">Email Address *</label>
              <input type="email" id="regEmail" required placeholder="name@domain.com">
            </div>

            <!-- Travel Mode -->
            <div class="form-group" style="margin-bottom: var(--space-md);">
              <label style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 8px;">Mode of Travel *</label>
              <div style="display: flex; gap: var(--space-md);">
                <label class="travel-mode-option travel-mode-active" id="labelBus">
                  <input type="radio" name="travel_mode" value="bus" checked onclick="updateTravelMode('bus')">
                  <div>
                    <strong style="display:block; font-size:var(--font-size-sm); transition: color var(--transition-fast);">By Bus</strong>
                    <span style="font-size:11px; color:var(--text-light);"><?php echo $busAdultLabel; ?></span>
                  </div>
                </label>
                <label class="travel-mode-option" id="labelVehicle">
                  <input type="radio" name="travel_mode" value="own_vehicle" onclick="updateTravelMode('own_vehicle')">
                  <div>
                    <strong style="display:block; font-size:var(--font-size-sm); transition: color var(--transition-fast);">Own Vehicle</strong>
                    <span style="font-size:11px; color:var(--text-light);"><?php echo $vehicleAdultLabel; ?></span>
                  </div>
                </label>
              </div>
            </div>

            <!-- Counters (Adults, Kids) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md); margin-bottom: var(--space-md);">
              <div class="form-group">
                <label for="cntAdults" style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">Adults *</label>
                <input type="number" id="cntAdults" min="1" max="50" value="1" onchange="calculatePrices()">
              </div>
              <div class="form-group">
                <label for="cntKids" style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">Kids (5 to 10 Yrs)</label>
                <input type="number" id="cntKids" min="0" max="50" value="0" onchange="calculatePrices()">
              </div>
            </div>
            <div style="font-size: 11px; color: var(--text-light); margin-bottom: var(--space-md); line-height: 1.4;">
              <i class="fas fa-info-circle"></i> Children under 5 years are registered <strong>Free</strong>. Kids 5 to 10 years are charged ₹<?php echo number_format($kidsPrice); ?>.
            </div>

            <!-- Bhakti Sadan Dropdown -->
            <div class="form-group" style="margin-bottom: var(--space-md);">
              <label for="selBhaktiSadan" style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">Connected Bhakti Sadan *</label>
              <select id="selBhaktiSadan" required>
                <option value="" disabled selected>-- Select Bhakti Sadan --</option>
                <?php foreach ($dbSadans as $sadanName): ?>
                  <option value="<?php echo htmlspecialchars($sadanName); ?>"><?php echo htmlspecialchars($sadanName); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Pickup Location Dropdown -->
            <div class="form-group" style="margin-bottom: var(--space-lg);" id="pickupWrapper">
              <label for="selPickup" style="display: block; font-size: var(--font-size-sm); font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">Select Pickup Location *</label>
              <select id="selPickup" required>
                <option value="" disabled selected>-- Select Pickup Point --</option>
                <?php foreach ($dbPickups as $pickupName): ?>
                  <option value="<?php echo htmlspecialchars($pickupName); ?>"><?php echo htmlspecialchars($pickupName); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Price Breakdown Summary -->
            <div id="priceSummary" style="background: var(--cream); border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--space-lg); font-size: var(--font-size-sm); border: 1px solid var(--border);">
              <div style="font-weight: 600; color: var(--text-dark); margin-bottom: var(--space-sm); border-bottom: 1px solid var(--border); padding-bottom: 4px;">Payment Summary</div>
              <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span id="lblAdultSummary">Adults (1 × ₹<?php echo number_format($pricing['bus_adult_price']); ?>)</span>
                <span id="valAdultSummary">₹<?php echo number_format($pricing['bus_adult_price']); ?></span>
              </div>
              <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span id="lblKidsSummary">Kids (0 × ₹<?php echo number_format($pricing['bus_kid_price']); ?>)</span>
                <span id="valKidsSummary">₹0</span>
              </div>
              <div style="display:flex; justify-content:space-between; font-weight:700; color:var(--primary); font-size:var(--font-size-base); border-top:1px dashed var(--border); padding-top:var(--space-xs);">
                <span>Total Amount</span>
                <span id="valTotalSummary">₹<?php echo number_format($pricing['bus_adult_price']); ?></span>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="btnSubmit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center;">
              <i class="fas fa-lock"></i> Register & Pay <span id="btnAmount">₹<?php echo number_format($pricing['bus_adult_price']); ?></span>
            </button>
            
            <div style="text-align: center; font-size: 11px; color: var(--text-light); margin-top: var(--space-md);">
              <i class="fas fa-shield-alt"></i> Secured by <strong>Razorpay</strong>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Additional Responsive Styles -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/yatra/panihati.css') ?>">

<script>
// Configurations
var RAZORPAY_CONFIG = {
  keyId: '<?php echo RAZORPAY_KEY_ID; ?>',
  siteName: '<?php echo SITE_NAME; ?>',
  themeColor: '#c86b1f'
};

// Dynamic pricing from the database
var PANIHATI_PRICES = {
  bus_adult: <?php echo $pricing['bus_adult_price']; ?>,
  bus_kid: <?php echo $pricing['bus_kid_price']; ?>,
  vehicle_adult: <?php echo $pricing['vehicle_adult_price']; ?>,
  vehicle_kid: <?php echo $pricing['vehicle_kid_price']; ?>
};

var currentTravelMode = 'bus';

function updateTravelMode(mode) {
  currentTravelMode = mode;
  
  var labelBus = document.getElementById('labelBus');
  var labelVehicle = document.getElementById('labelVehicle');
  var pickupWrapper = document.getElementById('pickupWrapper');
  var selPickup = document.getElementById('selPickup');
  
  if (mode === 'bus') {
    labelBus.classList.add('travel-mode-active');
    labelVehicle.classList.remove('travel-mode-active');
    pickupWrapper.style.opacity = '1';
    selPickup.disabled = false;
    selPickup.required = true;
  } else {
    labelBus.classList.remove('travel-mode-active');
    labelVehicle.classList.add('travel-mode-active');
    pickupWrapper.style.opacity = '0.5';
    selPickup.disabled = true;
    selPickup.required = false;
    selPickup.value = "";
  }
  
  calculatePrices();
}

function calculatePrices() {
  var adults = parseInt(document.getElementById('cntAdults').value, 10) || 1;
  var kids = parseInt(document.getElementById('cntKids').value, 10) || 0;
  
  if (adults < 1) {
    adults = 1;
    document.getElementById('cntAdults').value = 1;
  }
  if (kids < 0) {
    kids = 0;
    document.getElementById('cntKids').value = 0;
  }
  
  var adultRate = (currentTravelMode === 'bus') ? PANIHATI_PRICES.bus_adult : PANIHATI_PRICES.vehicle_adult;
  var kidsRate = (currentTravelMode === 'bus') ? PANIHATI_PRICES.bus_kid : PANIHATI_PRICES.vehicle_kid;
  
  var adultTotal = adults * adultRate;
  var kidsTotal = kids * kidsRate;
  var grandTotal = adultTotal + kidsTotal;
  
  document.getElementById('lblAdultSummary').textContent = 'Adults (' + adults + ' × ₹' + adultRate + ')';
  document.getElementById('valAdultSummary').textContent = '₹' + adultTotal.toLocaleString('en-IN');
  
  document.getElementById('lblKidsSummary').textContent = 'Kids (' + kids + ' × ₹' + kidsRate + ')';
  document.getElementById('valKidsSummary').textContent = '₹' + kidsTotal.toLocaleString('en-IN');
  
  document.getElementById('valTotalSummary').textContent = '₹' + grandTotal.toLocaleString('en-IN');
  document.getElementById('btnAmount').textContent = '₹' + grandTotal.toLocaleString('en-IN');
}

document.addEventListener('DOMContentLoaded', function() {
  updateTravelMode('bus');
  
  document.getElementById('panihatiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var name = document.getElementById('regName').value.trim();
    var phone = document.getElementById('regPhone').value.trim();
    var email = document.getElementById('regEmail').value.trim();
    var bhaktiSadan = document.getElementById('selBhaktiSadan').value;
    var pickup = document.getElementById('selPickup').value;
    
    var adults = parseInt(document.getElementById('cntAdults').value, 10) || 1;
    var kids = parseInt(document.getElementById('cntKids').value, 10) || 0;
    
    var adultRate = (currentTravelMode === 'bus') ? PANIHATI_PRICES.bus_adult : PANIHATI_PRICES.vehicle_adult;
    var kidsRate = (currentTravelMode === 'bus') ? PANIHATI_PRICES.bus_kid : PANIHATI_PRICES.vehicle_kid;
    var totalAmountRupees = (adults * adultRate) + (kids * kidsRate);
    
    var btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    var payload = {
      name: name,
      phone: phone,
      email: email,
      travel_mode: currentTravelMode,
      adults_count: adults,
      kids_count: kids,
      bhakti_sadan: bhaktiSadan,
      pickup_location: (currentTravelMode === 'bus') ? pickup : 'Own Vehicle',
      amount: totalAmountRupees * 100,
      middle_name: document.getElementById('middleNameHP').value
    };
    
    fetch('<?php echo BASE_URL; ?>api/create-panihati-order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(function(res) {
      if (!res.ok) {
        return res.json().then(function(err) { throw new Error(err.error || 'Failed to create order'); });
      }
      return res.json();
    })
    .then(function(result) {
      var options = {
        key: RAZORPAY_CONFIG.keyId,
        amount: result.amount,
        currency: result.currency,
        name: RAZORPAY_CONFIG.siteName,
        description: 'Panihati Yatra Registration',
        order_id: result.order_id,
        prefill: {
          name: name,
          email: email,
          contact: phone
        },
        theme: { color: RAZORPAY_CONFIG.themeColor },
        handler: function(response) {
          verifyPayment(response, result.registration_id);
        },
        modal: {
          ondismiss: function() {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-lock"></i> Register & Pay <span>₹' + totalAmountRupees.toLocaleString('en-IN') + '</span>';
          }
        }
      };
      
      var rzp = new Razorpay(options);
      rzp.on('payment.failed', function(resp) {
        alert('Payment failed: ' + resp.error.description);
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-lock"></i> Register & Pay <span>₹' + totalAmountRupees.toLocaleString('en-IN') + '</span>';
      });
      rzp.open();
    })
    .catch(function(err) {
      alert('Error: ' + err.message);
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = '<i class="fas fa-lock"></i> Register & Pay <span>₹' + totalAmountRupees.toLocaleString('en-IN') + '</span>';
    });
  });
});

function verifyPayment(response, registrationId) {
  var payload = {
    razorpay_order_id: response.razorpay_order_id,
    razorpay_payment_id: response.razorpay_payment_id,
    razorpay_signature: response.razorpay_signature,
    registration_id: registrationId
  };
  
  fetch('<?php echo BASE_URL; ?>api/verify-panihati-payment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(function(res) {
    if (!res.ok) {
      throw new Error('Payment signature verification failed');
    }
    return res.json();
  })
  .then(function(data) {
    if (data.success) {
      window.location.href = '<?php echo BASE_URL; ?>yatra/panihati-success.php?reg_id=' + registrationId + '&pay_id=' + encodeURIComponent(response.razorpay_payment_id);
    } else {
      alert('Failed to verify payment status.');
      location.reload();
    }
  })
  .catch(function(err) {
    alert('Verification Error: ' + err.message);
    location.reload();
  });
}
</script>

<?php include '../partials/footer.php'; ?>
