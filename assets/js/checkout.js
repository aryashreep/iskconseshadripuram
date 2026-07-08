/**
 * Checkout Page JavaScript
 * 
 * Reads config from #checkoutApp data-config attribute.
 * Depends on: cart.js (ISJMCart global), Razorpay checkout.js
 */

(function () {
  "use strict";

  // Read config from data attribute (set by PHP)
  var appEl = document.getElementById("checkoutApp");
  if (!appEl) return;

  var configStr = appEl.getAttribute("data-config");
  if (!configStr) {
    console.error("checkout.js: missing data-config attribute");
    return;
  }

  var CONFIG = JSON.parse(configStr);
  var RAZORPAY_CONFIG = CONFIG.razorpay;

  document.addEventListener("DOMContentLoaded", function () {
    // Ensure cart module is loaded
    if (typeof ISJMCart === "undefined") {
      appEl.innerHTML =
        '<div class="checkout-error">' +
        '<div style="font-size:64px;margin-bottom:var(--space-lg);">⚠️</div>' +
        "<h2>Cart not available</h2>" +
        "<p>The cart system could not be loaded. Please try again later.</p>" +
        '<a href="donate/" class="btn btn-primary">Go to Donate</a>' +
        "</div>";
      return;
    }

    var cart = ISJMCart.get();
    var count = ISJMCart.getCount();

    if (!cart.mode || count === 0) {
      appEl.innerHTML =
        '<div class="checkout-empty">' +
        '<div class="checkout-empty-icon"><i class="fas fa-cart-shopping"></i></div>' +
        "<h2>Your Cart is Empty</h2>" +
        "<p>You haven't selected any sevas or offerings yet. Browse our services to get started.</p>" +
        '<div class="empty-cta-grid">' +
        '<a href="donate/" class="empty-cta-card"><i class="fas fa-hand-holding-heart"></i><h4>Donate</h4><p>Support temple services</p></a>' +
        '<a href="booking/puja" class="empty-cta-card"><i class="fas fa-om"></i><h4>Book Puja</h4><p>Offer special prayers</p></a>' +
        '<a href="booking" class="empty-cta-card"><i class="fas fa-calendar-alt"></i><h4>All Bookings</h4><p>Explore all services</p></a>' +
        "</div>" +
        "</div>";
      return;
    }

    renderCheckout(cart);
  });

  function renderCheckout(cart) {
    var isPuja = cart.mode === "puja";
    var itemsHtml = "";
    var subtotal = 0;

    cart.items.forEach(function (item) {
      subtotal += item.lineTotal || item.unitAmount * item.qty;
      itemsHtml +=
        '<div class="checkout-item" data-key="' + item.key + '">' +
        '<div class="checkout-item-info">' +
        '<div class="checkout-item-name">' + escapeHtml(item.name) + "</div>" +
        '<div class="checkout-item-qty">' +
        (item.meta && item.meta.unit
          ? item.qty + " " + item.meta.unit
          : isPuja
          ? "1 offering"
          : "Qty: " + item.qty) +
        "</div></div>" +
        '<div style="display:flex;align-items:center;">' +
        '<div class="checkout-item-price">₹' +
        (item.lineTotal || item.unitAmount * item.qty).toLocaleString("en-IN") +
        "</div>" +
        '<button type="button" class="checkout-item-remove" onclick="removeCheckoutItem(\'' +
        item.key +
        '\')" title="Remove item"><i class="fas fa-times"></i></button>' +
        "</div></div>";
    });

    // Context info
    var contextHtml = "";
    if (isPuja && cart.context) {
      var ctx = cart.context;
      contextHtml =
        '<div class="checkout-context-info"><h4>Puja Details</h4><div class="context-row">' +
        (ctx.pujaDate ? "<span><strong>Date:</strong> " + ctx.pujaDate + "</span>" : "") +
        (ctx.presetName ? "<span><strong>Duration:</strong> " + ctx.presetName + "</span>" : "") +
        (ctx.gotra ? "<span><strong>Gotra:</strong> " + escapeHtml(ctx.gotra) + "</span>" : "") +
        (ctx.relation ? "<span><strong>Relation:</strong> " + escapeHtml(ctx.relation) + "</span>" : "") +
        (ctx.occasion ? "<span><strong>Purpose:</strong> " + escapeHtml(ctx.occasion) + "</span>" : "") +
        "</div></div>";
    } else if (!isPuja && cart.context && cart.context.specialInstructions) {
      contextHtml =
        '<div class="checkout-context-info"><h4>Notes</h4><p>' +
        escapeHtml(cart.context.specialInstructions) +
        "</p></div>";
    }

    var totalAmount = subtotal;
    if (isPuja && cart.context && cart.context.dateMultiplier) {
      totalAmount = subtotal * cart.context.dateMultiplier;
    }

    var modeLabel = isPuja ? "Puja" : "Donation";
    var modeIcon = isPuja ? "fa-om" : "fa-hand-holding-heart";

    appEl.innerHTML =
      '<div class="checkout-layout">' +
      "<div>" +
      '<div class="checkout-section">' +
      '<h3><i class="fas ' + modeIcon + '"></i> ' + escapeHtml(cart.source ? cart.source.title : "") + "</h3>" +
      '<div class="checkout-source-badge"><i class="fas ' + modeIcon + '"></i> ' + modeLabel + "</div>" +
      itemsHtml +
      '<div class="checkout-totals">' +
      '<div class="checkout-total-row"><span>Subtotal</span><span class="amount">₹' +
      subtotal.toLocaleString("en-IN") +
      "</span></div>" +
      (isPuja && cart.context && cart.context.dateMultiplier > 1
        ? '<div class="checkout-total-row"><span>Duration multiplier</span><span class="amount">×' +
          cart.context.dateMultiplier +
          "</span></div>"
        : "") +
      '<div class="checkout-total-row grand-total"><span>Total</span><span class="amount">₹' +
      totalAmount.toLocaleString("en-IN") +
      "</span></div></div>" +
      '<button type="button" class="btn btn-outline-dark btn-sm" onclick="clearCheckoutCart()" style="margin-top:var(--space-md);"><i class="fas fa-trash-alt"></i> Clear Cart</button>' +
      "</div>" +
      contextHtml +
      "</div>" +
      // Donor form
      "<div>" +
      '<div class="checkout-section checkout-form">' +
      '<h3><i class="fas fa-user"></i> Your Details</h3>' +
      '<form id="checkoutForm">' +
      '<div class="form-group"><label for="chkName">Full Name *</label><input type="text" id="chkName" name="donor_name" placeholder="Enter your full name" required></div>' +
      '<div class="form-row"><div class="form-group"><label for="chkEmail">Email *</label><input type="email" id="chkEmail" name="donor_email" placeholder="name@domain.com" required></div>' +
      '<div class="form-group"><label for="chkPhone">Phone *</label><input type="tel" id="chkPhone" name="donor_phone" placeholder="+91-98765" required></div></div>' +
      '<div class="form-group"><label for="chkPan">PAN Card <span style="color:var(--text-light);font-weight:400;font-size:11px;">(optional, for 80G receipt)</span></label><input type="text" id="chkPan" name="pan_number" placeholder="e.g. ABCDE1234F" maxlength="10" style="text-transform:uppercase;"></div>' +
      (isPuja
        ? ""
        : '<div class="form-group"><label for="chkPurpose">Purpose (optional)</label><textarea id="chkPurpose" name="occasion" rows="2" maxlength="100" placeholder="e.g. Birthday blessings, Good health"></textarea></div>') +
      '<input type="hidden" id="chkTotalAmount" value="' + totalAmount + '">' +
      '<input type="hidden" id="chkCartMode" value="' + cart.mode + '">' +
      '<button type="submit" class="btn btn-primary btn-lg checkout-pay-btn" id="checkoutPayBtn"><i class="fas fa-lock"></i> Pay ₹' +
      totalAmount.toLocaleString("en-IN") +
      "</button>" +
      '<div class="checkout-secure"><i class="fas fa-shield-alt"></i> Secured by <strong>Razorpay</strong> — 128-bit SSL Encrypted</div>' +
      "</form></div></div></div>";

    document.getElementById("checkoutForm").addEventListener("submit", handleCheckoutSubmit);
  }

  function handleCheckoutSubmit(e) {
    e.preventDefault();

    var name = document.getElementById("chkName");
    var email = document.getElementById("chkEmail");
    var phone = document.getElementById("chkPhone");
    var pan = document.getElementById("chkPan");

    if (!name.value.trim()) { alert("Please enter your name."); return; }
    if (!email.value.trim()) { alert("Please enter your email address."); return; }
    if (!phone.value.trim()) { alert("Please enter your phone number."); return; }

    var cart = ISJMCart.get();
    if (!cart.mode || !cart.items.length) {
      alert("Your cart is empty. Please add items before checkout.");
      return;
    }

    var totalAmount = parseInt(document.getElementById("chkTotalAmount").value, 10);
    if (totalAmount <= 0) { alert("Invalid total amount."); return; }

    var payBtn = document.getElementById("checkoutPayBtn");
    payBtn.disabled = true;
    payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    var itemsPayload = cart.items.map(function (item) {
      return { key: item.key, itemId: item.itemId, name: item.name, qty: item.qty, unitAmount: item.unitAmount, lineTotal: item.lineTotal };
    });

    var payload = {
      mode: cart.mode,
      source: cart.source,
      items: itemsPayload,
      context: cart.context,
      totalAmount: totalAmount * 100,
      donor_name: name.value.trim(),
      donor_email: email.value.trim(),
      donor_phone: phone.value.trim(),
      pan_number: pan ? pan.value.trim() : "",
      purpose: document.getElementById("chkPurpose") ? document.getElementById("chkPurpose").value.trim() : "",
    };

    fetch("../api/create-cart-order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then(function (res) {
        if (!res.ok) return res.json().then(function (err) { throw new Error(err.error || "Failed to create order"); });
        return res.json();
      })
      .then(function (result) {
        var options = {
          key: RAZORPAY_CONFIG.keyId,
          amount: result.amount,
          currency: result.currency,
          name: RAZORPAY_CONFIG.siteName,
          description: (cart.source ? cart.source.title : "Seva") + " - Cart",
          order_id: result.order_id,
          prefill: { name: name.value.trim(), email: email.value.trim(), contact: phone.value.trim() },
          theme: { color: "#c86b1f" },
          handler: function (response) {
            verifyCartPayment(response, cart, totalAmount * 100, name.value.trim(), email.value.trim(), phone.value.trim());
          },
          modal: {
            ondismiss: function () {
              payBtn.disabled = false;
              payBtn.innerHTML = '<i class="fas fa-lock"></i> Pay ₹' + totalAmount.toLocaleString("en-IN");
            },
          },
        };
        var rzp = new Razorpay(options);
        rzp.on("payment.failed", function () {
          window.location.href = "../donate/payment-failed.php?cause=" + encodeURIComponent(cart.source ? cart.source.title : "cart");
        });
        rzp.open();
      })
      .catch(function () {
        payBtn.disabled = false;
        payBtn.innerHTML = '<i class="fas fa-lock"></i> Pay ₹' + totalAmount.toLocaleString("en-IN");
        window.location.href = "../donate/payment-failed.php?payment_id=unknown";
      });
  }

  function verifyCartPayment(response, cart, amount, name, email, phone) {
    var payload = {
      razorpay_order_id: response.razorpay_order_id,
      razorpay_payment_id: response.razorpay_payment_id,
      razorpay_signature: response.razorpay_signature,
      cart_mode: cart.mode,
      source_slug: cart.source ? cart.source.slug : "",
      source_title: cart.source ? cart.source.title : "",
      amount: amount,
      donor_name: name,
      donor_email: email,
      donor_phone: phone,
      items: cart.items,
    };

    fetch("../api/verify-payment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then(function (res) {
        if (!res.ok) { window.location.href = "../donate/payment-failed.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id); return null; }
        return res.json();
      })
      .then(function (data) {
        if (data && data.success) {
          ISJMCart.clear();
          window.location.href =
            "../donate/payment-success.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id) +
            "&order_id=" + encodeURIComponent(response.razorpay_order_id) +
            "&amount=" + encodeURIComponent(amount) +
            "&cause=" + encodeURIComponent(cart.source ? cart.source.slug : "cart");
        } else if (data) {
          window.location.href = "../donate/payment-failed.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id);
        }
      })
      .catch(function () {
        window.location.href =
          "../donate/payment-failed.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id) +
          "&order_id=" + encodeURIComponent(response.razorpay_order_id);
      });
  }

  function removeCheckoutItem(itemKey) {
    ISJMCart.removeItem(itemKey);
    ISJMCart.syncBadge();
    if (ISJMCart.getCount() === 0) {
      location.reload();
    } else {
      renderCheckout(ISJMCart.get());
      showCheckoutToast("Item removed from cart");
    }
  }

  function clearCheckoutCart() {
    if (confirm("Clear your entire cart?")) {
      ISJMCart.clear();
      ISJMCart.syncBadge();
      location.reload();
    }
  }

  function showCheckoutToast(message) {
    var existing = document.querySelector(".checkout-toast");
    if (existing) existing.remove();
    var toast = document.createElement("div");
    toast.className = "checkout-toast show";
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function () {
      toast.classList.remove("show");
      setTimeout(function () { toast.remove(); }, 300);
    }, 2000);
  }

  function escapeHtml(str) {
    if (!str) return "";
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  // Expose to global scope for onclick handlers
  window.removeCheckoutItem = removeCheckoutItem;
  window.clearCheckoutCart = clearCheckoutCart;
})();
