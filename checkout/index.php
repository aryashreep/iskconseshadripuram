<?php
$pageTitle = 'Checkout - ISKCON The Palace Temple of Lord Jagannath';
include '../partials/header.php';
require_once '../config.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Checkout</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Checkout</span>
    </div>
  </div>
</section>

<!-- Checkout Content -->
<section class="page-content">
  <div class="container">
    <!-- Cart Loaded via JS -->
    <div id="checkoutApp">
      <!-- Loading State -->
      <div class="checkout-loading" id="checkoutLoading" style="text-align:center;padding:var(--space-4xl) 0;">
        <div style="width:40px;height:40px;border:3px solid var(--border);border-top:3px solid var(--primary);border-radius:50%;animation:spin 1s linear infinite;margin:0 auto var(--space-lg);"></div>
        <p style="color:var(--text-light);">Loading your cart...</p>
      </div>
    </div>
  </div>
</section>

<!-- Checkout Styles -->
<style>
  .checkout-empty {
    text-align: center;
    padding: var(--space-4xl) 0;
  }

  .checkout-empty-icon {
    font-size: 64px;
    color: var(--text-light);
    opacity: 0.4;
    margin-bottom: var(--space-lg);
  }

  .checkout-empty h2 {
    margin-bottom: var(--space-md);
  }

  .checkout-empty p {
    color: var(--text-light);
    margin-bottom: var(--space-xl);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
  }

  .checkout-empty-actions {
    display: flex;
    gap: var(--space-md);
    justify-content: center;
    flex-wrap: wrap;
  }

  /* Checkout Layout */
  .checkout-layout {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: var(--space-2xl);
    align-items: start;
  }

  @media (max-width: 900px) {
    .checkout-layout {
      grid-template-columns: 1fr;
    }
  }

  /* Cart Items */
  .checkout-section {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    margin-bottom: var(--space-xl);
  }

  .checkout-section h3 {
    font-family: var(--font-heading);
    color: var(--text-dark);
    font-size: var(--font-size-lg);
    margin-top: 0;
    margin-bottom: var(--space-lg);
    padding-bottom: var(--space-sm);
    border-bottom: 2px solid var(--primary);
    display: inline-block;
  }

  .checkout-source-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-sm);
    background: var(--cream);
    border-radius: 50px;
    padding: 6px 16px;
    font-size: var(--font-size-sm);
    color: var(--primary);
    font-weight: 600;
    margin-bottom: var(--space-lg);
  }

  .checkout-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-md) 0;
    border-bottom: 1px solid var(--border);
  }

  .checkout-item:last-child {
    border-bottom: none;
  }

  .checkout-item-info {
    flex: 1;
  }

  .checkout-item-name {
    font-weight: 600;
    color: var(--text-dark);
    font-size: var(--font-size-sm);
  }

  .checkout-item-qty {
    font-size: var(--font-size-xs);
    color: var(--text-light);
    margin-top: 2px;
  }

  .checkout-item-price {
    font-weight: 700;
    color: var(--primary);
    font-size: var(--font-size-sm);
    white-space: nowrap;
  }

  .checkout-item-remove {
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 4px 8px;
    font-size: var(--font-size-sm);
    transition: color var(--transition-fast);
    margin-left: var(--space-md);
  }

  .checkout-item-remove:hover {
    color: #d32f2f;
  }

  /* Totals */
  .checkout-totals {
    background: var(--cream);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-top: var(--space-lg);
  }

  .checkout-total-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    font-size: var(--font-size-sm);
    color: var(--text);
  }

  .checkout-total-row.grand-total {
    font-size: var(--font-size-lg);
    font-weight: 700;
    color: var(--text-dark);
    border-top: 2px solid var(--border);
    padding-top: var(--space-md);
    margin-top: var(--space-sm);
  }

  .checkout-total-row.grand-total .amount {
    color: var(--primary);
  }

  /* Form */
  .checkout-form .form-group {
    margin-bottom: var(--space-md);
  }

  .checkout-form label {
    display: block;
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--text-dark);
    margin-bottom: 4px;
  }

  .checkout-form input,
  .checkout-form select,
  .checkout-form textarea {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    font-family: var(--font-body);
    font-size: var(--font-size-sm);
    color: var(--text);
    background: var(--cream-light);
    outline: none;
    transition: border-color var(--transition-fast);
  }

  .checkout-form input:focus,
  .checkout-form select:focus,
  .checkout-form textarea:focus {
    border-color: var(--primary);
    background: var(--white);
  }

  .checkout-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
  }

  .checkout-context-info {
    background: var(--cream-light);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    margin-bottom: var(--space-lg);
    font-size: var(--font-size-sm);
  }

  .checkout-context-info h4 {
    font-size: var(--font-size-sm);
    margin-bottom: var(--space-sm);
    color: var(--text-dark);
  }

  .checkout-context-info p {
    margin: 0;
    color: var(--text-light);
    line-height: 1.6;
  }

  .checkout-context-info .context-row {
    display: flex;
    gap: var(--space-md);
    flex-wrap: wrap;
  }

  .checkout-context-info .context-row span {
    font-size: var(--font-size-xs);
    color: var(--text-light);
  }

  .checkout-context-info .context-row strong {
    color: var(--text-dark);
  }

  /* Pay Button */
  .checkout-pay-btn {
    width: 100%;
    justify-content: center;
    margin-top: var(--space-lg);
  }

  .checkout-secure {
    text-align: center;
    font-size: var(--font-size-xs);
    color: var(--text-light);
    margin-top: var(--space-md);
  }

  /* Error State */
  .checkout-error {
    text-align: center;
    padding: var(--space-4xl) 0;
  }

  .checkout-error h2 {
    color: var(--text-dark);
    margin-bottom: var(--space-md);
  }

  .checkout-error p {
    color: var(--text-light);
    margin-bottom: var(--space-xl);
  }

  /* Success Toast */
  .checkout-toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: #2e7d32;
    color: white;
    padding: 12px 24px;
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
    box-shadow: var(--shadow-lg);
    opacity: 0;
    transition: all var(--transition-base);
    z-index: 9999;
  }

  .checkout-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }

  /* Empty cart CTA actions */
  .empty-cta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: var(--space-md);
    max-width: 700px;
    margin: 0 auto;
  }

  .empty-cta-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    text-align: center;
    text-decoration: none;
    color: var(--text);
    transition: all var(--transition-base);
  }

  .empty-cta-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
    color: var(--text);
  }

  .empty-cta-card i {
    font-size: 32px;
    color: var(--primary);
    margin-bottom: var(--space-sm);
  }

  .empty-cta-card h4 {
    font-size: var(--font-size-base);
    margin-bottom: 4px;
  }

  .empty-cta-card p {
    font-size: var(--font-size-xs);
    color: var(--text-light);
    margin: 0;
  }

  @media (max-width: 600px) {
    .empty-cta-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<script>
  // Pass config to JS
  var RAZORPAY_CONFIG = {
    keyId: '<?php echo RAZORPAY_KEY_ID; ?>',
    currency: '<?php echo CURRENCY; ?>',
    siteName: '<?php echo SITE_NAME; ?>',
    testMode: <?php echo RAZORPAY_TEST_MODE ? 'true' : 'false'; ?>,
  };

  document.addEventListener('DOMContentLoaded', function() {
    var appEl = document.getElementById('checkoutApp');

    // Ensure cart module is loaded
    if (typeof ISJMCart === 'undefined') {
      appEl.innerHTML = '<div class="checkout-error"><div style="font-size:64px;margin-bottom:var(--space-lg);">⚠️</div><h2>Cart not available</h2><p>The cart system could not be loaded. Please try again later.</p><a href="donate/" class="btn btn-primary">Go to Donate</a></div>';
      return;
    }

    var cart = ISJMCart.get();
    var count = ISJMCart.getCount();

    if (!cart.mode || count === 0) {
      // Empty cart
      appEl.innerHTML = '<div class="checkout-empty">' +
        '<div class="checkout-empty-icon"><i class="fas fa-cart-shopping"></i></div>' +
        '<h2>Your Cart is Empty</h2>' +
        '<p>You haven\'t selected any sevas or offerings yet. Browse our services to get started.</p>' +
        '<div class="empty-cta-grid">' +
        '<a href="donate/" class="empty-cta-card">' +
        '<i class="fas fa-hand-holding-heart"></i>' +
        '<h4>Donate</h4>' +
        '<p>Support temple services</p>' +
        '</a>' +
        '<a href="booking/puja" class="empty-cta-card">' +
        '<i class="fas fa-om"></i>' +
        '<h4>Book Puja</h4>' +
        '<p>Offer special prayers</p>' +
        '</a>' +
        '<a href="booking" class="empty-cta-card">' +
        '<i class="fas fa-calendar-alt"></i>' +
        '<h4>All Bookings</h4>' +
        '<p>Explore all services</p>' +
        '</a>' +
        '</div>' +
        '</div>';
      return;
    }

    // Render checkout
    renderCheckout(cart);
  });

  function renderCheckout(cart) {
    var appEl = document.getElementById('checkoutApp');
    var isPuja = cart.mode === 'puja';
    var itemsHtml = '';
    var subtotal = 0;

    cart.items.forEach(function(item) {
      subtotal += item.lineTotal || (item.unitAmount * item.qty);
      itemsHtml += '<div class="checkout-item" data-key="' + item.key + '">' +
        '<div class="checkout-item-info">' +
        '<div class="checkout-item-name">' + escapeHtml(item.name) + '</div>' +
        '<div class="checkout-item-qty">' +
        (item.meta && item.meta.unit ? item.qty + ' ' + item.meta.unit : (isPuja ? '1 offering' : 'Qty: ' + item.qty)) +
        '</div>' +
        '</div>' +
        '<div style="display:flex;align-items:center;">' +
        '<div class="checkout-item-price">₹' + (item.lineTotal || (item.unitAmount * item.qty)).toLocaleString('en-IN') + '</div>' +
        '<button type="button" class="checkout-item-remove" onclick="removeCheckoutItem(\'' + item.key + '\')" title="Remove item">' +
        '<i class="fas fa-times"></i>' +
        '</button>' +
        '</div>' +
        '</div>';
    });

    // Build context info for puja
    var contextHtml = '';
    if (isPuja && cart.context) {
      var ctx = cart.context;
      contextHtml = '<div class="checkout-context-info">' +
        '<h4>Puja Details</h4>' +
        '<div class="context-row">' +
        (ctx.pujaDate ? '<span><strong>Date:</strong> ' + ctx.pujaDate + '</span>' : '') +
        (ctx.presetName ? '<span><strong>Duration:</strong> ' + ctx.presetName + '</span>' : '') +
        (ctx.gotra ? '<span><strong>Gotra:</strong> ' + escapeHtml(ctx.gotra) + '</span>' : '') +
        (ctx.relation ? '<span><strong>Relation:</strong> ' + escapeHtml(ctx.relation) + '</span>' : '') +
        (ctx.occasion ? '<span><strong>Purpose:</strong> ' + escapeHtml(ctx.occasion) + '</span>' : '') +
        '</div>' +
        '</div>';
    } else if (!isPuja && cart.context && cart.context.specialInstructions) {
      contextHtml = '<div class="checkout-context-info">' +
        '<h4>Notes</h4>' +
        '<p>' + escapeHtml(cart.context.specialInstructions) + '</p>' +
        '</div>';
    }

    // Apply date multiplier for puja
    var totalAmount = subtotal;
    if (isPuja && cart.context && cart.context.dateMultiplier) {
      totalAmount = subtotal * cart.context.dateMultiplier;
    }

    var modeLabel = isPuja ? 'Puja' : 'Donation';
    var modeIcon = isPuja ? 'fa-om' : 'fa-hand-holding-heart';

    appEl.innerHTML = '<div class="checkout-layout">' +
      // Left column
      '<div>' +
      '<div class="checkout-section">' +
      '<h3><i class="fas ' + modeIcon + '"></i> ' + escapeHtml(cart.source ? cart.source.title : '') + '</h3>' +
      '<div class="checkout-source-badge"><i class="fas ' + modeIcon + '"></i> ' + modeLabel + '</div>' +
      itemsHtml +
      '<div class="checkout-totals">' +
      '<div class="checkout-total-row"><span>Subtotal</span><span class="amount">₹' + subtotal.toLocaleString('en-IN') + '</span></div>' +
      (isPuja && cart.context && cart.context.dateMultiplier > 1 ? '<div class="checkout-total-row"><span>Duration multiplier</span><span class="amount">×' + cart.context.dateMultiplier + '</span></div>' : '') +
      '<div class="checkout-total-row grand-total"><span>Total</span><span class="amount">₹' + totalAmount.toLocaleString('en-IN') + '</span></div>' +
      '</div>' +
      '<button type="button" class="btn btn-outline-dark btn-sm" onclick="clearCheckoutCart()" style="margin-top:var(--space-md);">' +
      '<i class="fas fa-trash-alt"></i> Clear Cart' +
      '</button>' +
      '</div>' +
      contextHtml +
      '</div>' +

      // Right column - donor form
      '<div>' +
      '<div class="checkout-section checkout-form">' +
      '<h3><i class="fas fa-user"></i> Your Details</h3>' +
      '<form id="checkoutForm">' +
      '<div class="form-group">' +
      '<label for="chkName">Full Name *</label>' +
      '<input type="text" id="chkName" name="donor_name" placeholder="Enter your full name" required>' +
      '</div>' +
      '<div class="form-row">' +
      '<div class="form-group">' +
      '<label for="chkEmail">Email *</label>' +
      '<input type="email" id="chkEmail" name="donor_email" placeholder="name@domain.com" required>' +
      '</div>' +
      '<div class="form-group">' +
      '<label for="chkPhone">Phone *</label>' +
      '<input type="tel" id="chkPhone" name="donor_phone" placeholder="+91-98765" required>' +
      '</div>' +
      '</div>' +
      '<div class="form-group">' +
      '<label for="chkPan">PAN Card <span style="color:var(--text-light);font-weight:400;font-size:11px;">(optional, for 80G receipt)</span></label>' +
      '<input type="text" id="chkPan" name="pan_number" placeholder="e.g. ABCDE1234F" maxlength="10" style="text-transform:uppercase;">' +
      '</div>' +
      (isPuja ? '' : '<div class="form-group">' +
        '<label for="chkPurpose">Purpose (optional)</label>' +
        '<textarea id="chkPurpose" name="occasion" rows="2" maxlength="100" placeholder="e.g. Birthday blessings, Good health"></textarea>' +
        '</div>') +
      '<input type="hidden" id="chkTotalAmount" value="' + totalAmount + '">' +
      '<input type="hidden" id="chkCartMode" value="' + cart.mode + '">' +
      '<button type="submit" class="btn btn-primary btn-lg checkout-pay-btn" id="checkoutPayBtn">' +
      '<i class="fas fa-lock"></i> Pay ₹' + totalAmount.toLocaleString('en-IN') +
      '</button>' +
      '<div class="checkout-secure">' +
      '<i class="fas fa-shield-alt"></i> Secured by <strong>Razorpay</strong> — 128-bit SSL Encrypted' +
      '</div>' +
      '</form>' +
      '</div>' +
      '</div>' +
      '</div>';

    // Bind form submit
    document.getElementById('checkoutForm').addEventListener('submit', handleCheckoutSubmit);
  }

  function handleCheckoutSubmit(e) {
    e.preventDefault();

    var name = document.getElementById('chkName');
    var email = document.getElementById('chkEmail');
    var phone = document.getElementById('chkPhone');
    var pan = document.getElementById('chkPan');

    if (!name.value.trim()) {
      alert('Please enter your name.');
      return;
    }
    if (!email.value.trim()) {
      alert('Please enter your email address.');
      return;
    }
    if (!phone.value.trim()) {
      alert('Please enter your phone number.');
      return;
    }

    var cart = ISJMCart.get();
    if (!cart.mode || !cart.items.length) {
      alert('Your cart is empty. Please add items before checkout.');
      return;
    }

    var totalAmount = parseInt(document.getElementById('chkTotalAmount').value, 10);
    if (totalAmount <= 0) {
      alert('Invalid total amount.');
      return;
    }

    var payBtn = document.getElementById('checkoutPayBtn');
    payBtn.disabled = true;
    payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    // Prepare items for API
    var itemsPayload = cart.items.map(function(item) {
      return {
        key: item.key,
        itemId: item.itemId,
        name: item.name,
        qty: item.qty,
        unitAmount: item.unitAmount,
        lineTotal: item.lineTotal
      };
    });

    var payload = {
      mode: cart.mode,
      source: cart.source,
      items: itemsPayload,
      context: cart.context,
      totalAmount: totalAmount * 100, // paise
      donor_name: name.value.trim(),
      donor_email: email.value.trim(),
      donor_phone: phone.value.trim(),
      pan_number: pan ? pan.value.trim() : '',
      purpose: document.getElementById('chkPurpose') ? document.getElementById('chkPurpose').value.trim() : ''
    };

    fetch('../api/create-cart-order.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(function(res) {
        if (!res.ok) {
          return res.json().then(function(err) {
            throw new Error(err.error || 'Failed to create order');
          });
        }
        return res.json();
      })
      .then(function(result) {
        var options = {
          key: RAZORPAY_CONFIG.keyId,
          amount: result.amount,
          currency: result.currency,
          name: RAZORPAY_CONFIG.siteName,
          description: (cart.source ? cart.source.title : 'Seva') + ' - Cart',
          order_id: result.order_id,
          prefill: {
            name: name.value.trim(),
            email: email.value.trim(),
            contact: phone.value.trim()
          },
          theme: {
            color: '#c86b1f'
          },
          handler: function(response) {
            verifyCartPayment(response, cart, totalAmount * 100, name.value.trim(), email.value.trim(), phone.value.trim());
          },
          modal: {
            ondismiss: function() {
              payBtn.disabled = false;
              payBtn.innerHTML = '<i class=\"fas fa-lock\"></i> Pay ₹' + totalAmount.toLocaleString('en-IN');
            }
          }
        };

        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function() {
          window.location.href = '../donate/payment-failed.php?cause=' + encodeURIComponent(cart.source ? cart.source.title : 'cart');
        });
        rzp.open();
      })
      .catch(function(error) {
        payBtn.disabled = false;
        payBtn.innerHTML = '<i class=\"fas fa-lock\"></i> Pay ₹' + totalAmount.toLocaleString('en-IN');
        window.location.href = '../donate/payment-failed.php?payment_id=' + encodeURIComponent(response && response.razorpay_payment_id ? response.razorpay_payment_id : 'unknown');
      });
  }

  function verifyCartPayment(response, cart, amount, name, email, phone) {
    var payload = {
      razorpay_order_id: response.razorpay_order_id,
      razorpay_payment_id: response.razorpay_payment_id,
      razorpay_signature: response.razorpay_signature,
      cart_mode: cart.mode,
      source_slug: cart.source ? cart.source.slug : '',
      source_title: cart.source ? cart.source.title : '',
      amount: amount,
      donor_name: name,
      donor_email: email,
      donor_phone: phone,
      items: cart.items
    };

    fetch('../api/verify-payment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(function(res) {
        if (!res.ok) {
          window.location.href = '../donate/payment-failed.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id);
          return null;
        }
        return res.json();
      })
      .then(function(data) {
        if (data && data.success) {
          // Clear cart on success
          ISJMCart.clear();
          window.location.href = '../donate/payment-success.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id) +
            '&order_id=' + encodeURIComponent(response.razorpay_order_id) +
            '&amount=' + encodeURIComponent(amount) +
            '&cause=' + encodeURIComponent(cart.source ? cart.source.slug : 'cart');
        } else if (data) {
          window.location.href = '../donate/payment-failed.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id);
        }
      })
      .catch(function() {
        // On error, show failure to avoid exposing unverified payments as successful
        window.location.href = '../donate/payment-failed.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id) +
          '&order_id=' + encodeURIComponent(response.razorpay_order_id);
      });
  }

  function removeCheckoutItem(itemKey) {
    ISJMCart.removeItem(itemKey);
    ISJMCart.syncBadge();
    // Re-render
    var cart = ISJMCart.get();
    if (ISJMCart.getCount() === 0) {
      // Reload page to show empty state
      location.reload();
    } else {
      renderCheckout(cart);
      showCheckoutToast('Item removed from cart');
    }
  }

  function clearCheckoutCart() {
    if (confirm('Clear your entire cart?')) {
      ISJMCart.clear();
      ISJMCart.syncBadge();
      location.reload();
    }
  }

  function showCheckoutToast(message) {
    var existing = document.querySelector('.checkout-toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.className = 'checkout-toast show';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function() {
      toast.classList.remove('show');
      setTimeout(function() {
        toast.remove();
      }, 300);
    }, 2000);
  }

  function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
</script>

<?php include '../partials/footer.php'; ?>