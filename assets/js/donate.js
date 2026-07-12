/* ============================================
   Donate Page - Razorpay Checkout JavaScript
   Updated for DB-backed cause system
   ============================================ */

(function () {
  "use strict";

  const form = document.getElementById("donationForm");
  const donateBtn = document.getElementById("donateBtn");
  const loading = document.getElementById("donateLoading");
  const selectedAmountInput = document.getElementById("selectedAmount");
  const payAmountSpan = document.getElementById("payAmount");
  const causeSlugInput = document.getElementById("causeSlug");
  const causeIdInput = document.getElementById("causeId");
  const donationModeInput = document.getElementById("donationMode");
  const formTypeInput = document.getElementById("formType");

  // Read Razorpay config from data attribute (set by PHP)
  let RAZORPAY_CONFIG = {};
  if (form && form.hasAttribute("data-razorpay")) {
    try {
      RAZORPAY_CONFIG = JSON.parse(form.getAttribute("data-razorpay"));
    } catch (e) {
      console.error("donate.js: invalid data-razorpay JSON", e);
    }
  }
  // Fallback to global if data attribute missing (backward compat)
  if (!RAZORPAY_CONFIG.keyId && typeof window.RAZORPAY_CONFIG !== "undefined") {
    RAZORPAY_CONFIG = window.RAZORPAY_CONFIG;
  }

  // Amount state
  let currentAmount = selectedAmountInput
    ? parseInt(selectedAmountInput.value, 10)
    : 501;
  let selectedSevaId = null;
  let selectedSevas = {};
  let dateMultiplier = 1;
  let presetName = "";

  // Sync donation selections to global cart (inside IIFE for scope access)
  function syncDonationCart() {
    if (typeof ISJMCart === "undefined") return;

    var formType = formTypeInput ? formTypeInput.value : "tiers";
    var causeSlug = causeSlugInput ? causeSlugInput.value : "";

    // Only sync cart, quantity, multi_item form types
    if (["cart", "cart_qty", "quantity", "multi_item"].indexOf(formType) === -1) return;

    var source = {
      mode: "donation",
      type: "cause",
      slug: causeSlug,
      title: causeSlug.replace(/-/g, " ").replace(/\b\w/g, function (l) {
        return l.toUpperCase();
      }),
      formType: formType,
    };

    var items = [];

    if (formType === "cart") {
      // Collect selected sevas (toggle style) - access via closure
      for (var key in selectedSevas) {
        if (selectedSevas.hasOwnProperty(key)) {
          items.push({
            key: "seva-" + key,
            itemId: key,
            name: selectedSevas[key].name,
            qty: 1,
            unitAmount: selectedSevas[key].price,
            lineTotal: selectedSevas[key].price,
          });
        }
      }
    } else if (formType === "cart_qty") {
      // Collect sevas with +/- quantity controls
      for (var key in selectedSevas) {
        if (selectedSevas.hasOwnProperty(key)) {
          var sevaQty = selectedSevas[key].qty || 1;
          items.push({
            key: "seva-" + key,
            itemId: key,
            name: selectedSevas[key].name,
            qty: sevaQty,
            unitAmount: selectedSevas[key].price,
            lineTotal: selectedSevas[key].price * sevaQty,
          });
        }
      }
    } else if (formType === "quantity") {
      document.querySelectorAll(".qty-input").forEach(function (input) {
        var qty = parseInt(input.value, 10) || 0;
        if (qty > 0) {
          var price = parseInt(input.getAttribute("data-price"), 10) || 0;
          var itemId =
            input.getAttribute("data-id") ||
            input.id ||
            Math.random().toString(36).slice(2);
          items.push({
            key: "seva-" + itemId,
            itemId: itemId,
            name: input.getAttribute("data-name") || "Seva",
            qty: qty,
            unitAmount: price,
            lineTotal: qty * price,
          });
        }
      });
    } else if (formType === "multi_item") {
      document.querySelectorAll(".cart-qty").forEach(function (input) {
        var qty = parseFloat(input.value) || 0;
        if (qty > 0) {
          var rate = parseFloat(input.getAttribute("data-rate")) || 0;
          var itemId =
            input.getAttribute("data-seva-id") ||
            input.id ||
            Math.random().toString(36).slice(2);
          items.push({
            key: "seva-" + itemId,
            itemId: itemId,
            name: input.getAttribute("data-name") || "Seva",
            qty: qty,
            unitAmount: rate,
            lineTotal: qty * rate,
            meta: { unit: "kg" },
          });
        }
      });
    }

    if (items.length > 0) {
      var currentCart = ISJMCart.get();
      if (currentCart.mode && !ISJMCart.isCompatible(source)) {
        return;
      }
      var context = {
        specialInstructions: document.getElementById("selectedOfferingsList")
          ? document.getElementById("selectedOfferingsList").value
          : "",
      };
      ISJMCart.replaceFromPage(source, items, context);
    }
  }

  if (formTypeInput && ["cart", "cart_qty"].indexOf(formTypeInput.value) !== -1) {
    currentAmount = 0;
    window.addEventListener("DOMContentLoaded", function () {
      updatePayButton();
    });
  }

  // ==========================================
  // 1. AMOUNT SELECTION (Radio-style options)
  // ==========================================
  window.selectDonationOption = function (el) {
    // Remove active from all options
    document.querySelectorAll(".amount-option").forEach(function (opt) {
      opt.classList.remove("active");
    });

    // Activate clicked option
    el.classList.add("active");

    // Hide custom amount
    var customWrap = document.getElementById("customAmountWrap");
    if (customWrap) customWrap.classList.remove("active");
    var customRow = document.querySelector(".custom-amount-row");
    if (customRow) customRow.classList.remove("active");

    // Update amount and seva ID
    currentAmount = parseInt(el.getAttribute("data-amount"), 10);
    selectedSevaId = el.getAttribute("data-seva-id") || null;
    updatePayButton();
  };

  window.toggleCustomAmount = function () {
    var customWrap = document.getElementById("customAmountWrap");
    var customRow = document.querySelector(".custom-amount-row");

    if (!customWrap) return;

    var isActive = customWrap.classList.contains("active");

    if (isActive) {
      // Hide
      customWrap.classList.remove("active");
      if (customRow) customRow.classList.remove("active");
      // Re-select first option
      var firstOpt = document.querySelector(".amount-option");
      if (firstOpt) {
        selectDonationOption(firstOpt);
      }
    } else {
      // Show
      customWrap.classList.add("active");
      if (customRow) customRow.classList.add("active");

      // Deselect all preset options
      document.querySelectorAll(".amount-option").forEach(function (opt) {
        opt.classList.remove("active");
      });

      // Focus input
      var customInput = document.getElementById("customAmount");
      if (customInput) {
        customInput.focus();
        customInput.addEventListener("input", function () {
          var val = parseInt(this.value, 10);
          if (!isNaN(val) && val >= 10) {
            currentAmount = val;
          } else {
            currentAmount = 10;
          }
          selectedSevaId = null;
          updatePayButton();
        });
      }

      currentAmount = 10;
      selectedSevaId = null;
      updatePayButton();
    }
  };

  function updatePayButton() {
    var amountInr = currentAmount;
    if (selectedAmountInput) selectedAmountInput.value = amountInr;

    var formType = formTypeInput ? formTypeInput.value : "tiers";
    if (donateBtn) {
      if (formType === "cart" || formType === "cart_qty") {
        if (amountInr <= 0) {
          donateBtn.disabled = true;
          donateBtn.innerHTML = "Choose Any Offering";
        } else {
          donateBtn.disabled = false;
          var btnText =
            donationModeInput && donationModeInput.value === "monthly"
              ? "Subscribe "
              : "Pay ";
          donateBtn.innerHTML =
            '<i class="fas fa-lock"></i> ' +
            btnText +
            "\u20B9" +
            amountInr.toLocaleString("en-IN");
        }
      } else {
        donateBtn.disabled = false;
        var paySpan = document.getElementById("payAmount");
        if (!paySpan) {
          var btnText =
            donationModeInput && donationModeInput.value === "monthly"
              ? "Subscribe "
              : "Pay ";
          donateBtn.innerHTML =
            '<i class="fas fa-lock"></i> ' +
            btnText +
            '<span id="payAmount">\u20B9' +
            amountInr.toLocaleString("en-IN") +
            "</span>";
        } else {
          paySpan.textContent = "\u20B9" + amountInr.toLocaleString("en-IN");
        }
      }
    }

    // Update monthly display if present
    var monthlyDisplay = document.getElementById("monthlyAmountDisplay");
    if (monthlyDisplay) {
      monthlyDisplay.textContent = "\u20B9" + amountInr.toLocaleString("en-IN");
    }
  }

  // ==========================================
  // 2. MODE SWITCHING (one_time / monthly)
  // ==========================================
  window.switchMode = function (mode) {
    document.querySelectorAll(".mode-btn").forEach(function (btn) {
      btn.classList.toggle("active", btn.getAttribute("data-mode") === mode);
    });
    if (donationModeInput) donationModeInput.value = mode;

    var monthlyNotice = document.getElementById("monthlyNotice");
    if (monthlyNotice) {
      monthlyNotice.style.display = mode === "monthly" ? "flex" : "none";
    }

    // Update option descriptions
    document.querySelectorAll(".amount-option-desc").forEach(function (desc) {
      desc.textContent =
        mode === "monthly" ? "Monthly donation" : "One-time donation";
    });

    // Update button text
    if (donateBtn && payAmountSpan) {
      var btnText = mode === "monthly" ? "Subscribe " : "Pay ";
      donateBtn.querySelector("span").textContent =
        btnText + "\u20B9" + currentAmount.toLocaleString("en-IN");
    }
  };

  // ==========================================
  // 3. PAYMENT SUBMISSION
  // ==========================================
  if (form) {
    form.addEventListener("reset", function () {
      // Clear custom cart state
      selectedSevas = {};

      // Remove green 'added' class and restore button text
      document.querySelectorAll(".add-to-seva-btn").forEach(function (btn) {
        btn.classList.remove("added");
        btn.textContent = "Add Seva";
      });

      // Clear quantity inputs if any
      document.querySelectorAll(".qty-input").forEach(function (input) {
        input.value = 0;
        var totalSpan = input
          .closest(".quantity-item")
          .querySelector(".qty-total");
        if (totalSpan) totalSpan.textContent = "= \u20B90";
      });

      // Clear cart inputs if any
      document.querySelectorAll(".cart-qty").forEach(function (input) {
        input.value = 0;
        var totalSpan = input
          .closest(".cart-item")
          .querySelector(".cart-item-total");
        if (totalSpan) totalSpan.textContent = "\u20B90";
      });

      // Reset custom amount wrap
      var customWrap = document.getElementById("customAmountWrap");
      if (customWrap) customWrap.classList.remove("active");
      var customRow = document.querySelector(".custom-amount-row");
      if (customRow) customRow.classList.remove("active");

      // Reset amount to default
      currentAmount = selectedAmountInput
        ? parseInt(selectedAmountInput.getAttribute("value"), 10)
        : 501;
      selectedSevaId = null;
      dateMultiplier = 1;
      presetName = "";
      document.querySelectorAll(".preset-btn").forEach(function (btn) {
        btn.classList.remove("active");
      });

      // Re-select first preset option if tiers form type
      var firstOpt = document.querySelector(".amount-option");
      if (firstOpt) {
        setTimeout(function () {
          selectDonationOption(firstOpt);
        }, 10);
      }

      // Update cart UI summary
      recalculateDonationCartTotal();

      // Update quantity and cart grand totals
      var grandTotalSpan = document.querySelector(".grand-total-amount");
      if (grandTotalSpan) grandTotalSpan.textContent = "\u20B90";
      var cartGrandTotalSpan = document.querySelector(
        ".cart-grand-total-amount",
      );
      if (cartGrandTotalSpan) cartGrandTotalSpan.textContent = "\u20B90";
    });

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      // Validate
      var name = document.getElementById("donorName");
      var email = document.getElementById("donorEmail");
      var phone = document.getElementById("donorPhone");

      if (!name || !name.value.trim()) {
        alert("Please enter your name.");
        return;
      }
      if (!email || !email.value.trim()) {
        alert("Please enter your email address.");
        return;
      }
      if (!phone || !phone.value.trim()) {
        alert("Please enter your phone number.");
        return;
      }

      var amountPaise = currentAmount * 100; // Convert to paise
      var causeSlug = causeSlugInput
        ? causeSlugInput.value
        : "general-donation";
      var causeId = causeIdInput ? parseInt(causeIdInput.value, 10) : null;
      var donationMode = donationModeInput
        ? donationModeInput.value
        : "one_time";
      var formType = formTypeInput ? formTypeInput.value : "tiers";

      // Show loading
      if (loading) loading.classList.add("active");
      if (donateBtn) donateBtn.disabled = true;

      // Handle quantity and multi-item form types
      if (formType === "quantity") {
        // Calculate total from quantity inputs
        var qtyInputs = document.querySelectorAll(".qty-input");
        var totalAmount = 0;
        qtyInputs.forEach(function (input) {
          var qty = parseInt(input.value, 10) || 0;
          var price = parseInt(input.getAttribute("data-price"), 10) || 0;
          totalAmount += qty * price;
        });
        if (totalAmount <= 0) {
          alert("Please select at least one item and enter a quantity.");
          if (loading) loading.classList.remove("active");
          if (donateBtn) donateBtn.disabled = false;
          return;
        }
        currentAmount = totalAmount;
        amountPaise = totalAmount * 100;
      } else if (formType === "multi_item") {
        // Calculate total from cart inputs
        var cartInputs = document.querySelectorAll(".cart-qty");
        var totalAmount = 0;
        cartInputs.forEach(function (input) {
          var qty = parseFloat(input.value) || 0;
          var rate = parseFloat(input.getAttribute("data-rate")) || 0;
          totalAmount += qty * rate;
        });
        if (totalAmount <= 0) {
          alert("Please select at least one item and enter a weight.");
          if (loading) loading.classList.remove("active");
          if (donateBtn) donateBtn.disabled = false;
          return;
        }
        currentAmount = totalAmount;
        amountPaise = totalAmount * 100;
      } else if (formType === "cart" || formType === "cart_qty") {
        if (currentAmount <= 0) {
          alert("Please select at least one seva offering.");
          if (loading) loading.classList.remove("active");
          if (donateBtn) donateBtn.disabled = false;
          return;
        }
        amountPaise = currentAmount * 100;
      }

      var specialInstructions = document.getElementById("selectedOfferingsList")
        ? document.getElementById("selectedOfferingsList").value
        : "";

      var panNumber = document.getElementById("panNumber")
        ? document.getElementById("panNumber").value.trim()
        : "";

      // Build payload
      var payload = {
        amount: amountPaise,
        cause_id: causeId,
        cause_slug: causeSlug,
        seva_id: selectedSevaId,
        donation_mode: donationMode,
        form_type: formType,
        donor_name: name.value.trim(),
        donor_email: email.value.trim(),
        donor_phone: phone.value.trim(),
        pan_number: panNumber,
        special_instructions: specialInstructions,
        gotra: document.getElementById("gotra")
          ? document.getElementById("gotra").value.trim()
          : "",
        relation: document.getElementById("relation")
          ? document.getElementById("relation").value
          : "",
        occasion: document.getElementById("occasion")
          ? document.getElementById("occasion").value.trim()
          : "",
        seva_date: document.getElementById("sevaDate")
          ? document.getElementById("sevaDate").value
          : "",
        middle_name: document.getElementById("middleNameHP")
          ? document.getElementById("middleNameHP").value
          : "",
      };

      // Choose endpoint based on mode
      var endpoint =
        donationMode === "monthly"
          ? "../api/create-subscription.php"
          : "../api/create-order.php";

      // Create order/subscription via AJAX
      fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
        .then(function (response) {
          if (!response.ok) {
            return response.json().then(function (err) {
              throw new Error(err.error || "Failed to create order");
            });
          }
          return response.json();
        })
        .then(function (result) {
          if (donationMode === "monthly") {
            // Monthly subscription flow
            var options = {
              key: RAZORPAY_CONFIG.keyId,
              subscription_id: result.subscription_id,
              name: RAZORPAY_CONFIG.siteName,
              description: causeSlug.replace(/-/g, " ") + " (Monthly)",
              prefill: {
                name: name.value.trim(),
                email: email.value.trim(),
                contact: phone.value.trim(),
              },
              theme: {
                color: "#c86b1f",
              },
              handler: function (response) {
                // Redirect to success page
                window.location.href =
                  "payment-success.php?subscription_id=" +
                  encodeURIComponent(response.razorpay_subscription_id) +
                  "&payment_id=" +
                  encodeURIComponent(response.razorpay_payment_id) +
                  "&amount=" +
                  encodeURIComponent(amountPaise) +
                  "&mode=monthly";
              },
              modal: {
                ondismiss: function () {
                  if (loading) loading.classList.remove("active");
                  if (donateBtn) donateBtn.disabled = false;
                },
              },
            };

            var rzp1 = new Razorpay(options);
            rzp1.open();
          } else {
            // One-time order flow (existing)
            var options = {
              key: RAZORPAY_CONFIG.keyId,
              amount: result.amount,
              currency: result.currency,
              name: RAZORPAY_CONFIG.siteName,
              description: causeSlug.replace(/-/g, " ") + " Seva",
              order_id: result.order_id,
              prefill: {
                name: name.value.trim(),
                email: email.value.trim(),
                contact: phone.value.trim(),
              },
              theme: {
                color: "#c86b1f",
              },
              handler: function (response) {
                verifyPayment(
                  response,
                  causeSlug,
                  amountPaise,
                  name.value.trim(),
                  email.value.trim(),
                  phone.value.trim(),
                );
              },
              modal: {
                ondismiss: function () {
                  if (loading) loading.classList.remove("active");
                  if (donateBtn) donateBtn.disabled = false;
                },
              },
            };

            var rzp1 = new Razorpay(options);
            rzp1.on("payment.failed", function () {
              var cause = causeSlugInput ? causeSlugInput.value : "";
              var mode = donationModeInput
                ? donationModeInput.value
                : "one_time";
              var params = [];
              if (cause) params.push("cause=" + encodeURIComponent(cause));
              if (mode) params.push("mode=" + encodeURIComponent(mode));
              window.location.href =
                "payment-failed.php" +
                (params.length ? "?" + params.join("&") : "");
            });
            rzp1.open();
          }
        })
        .catch(function (error) {
          if (loading) loading.classList.remove("active");
          if (donateBtn) donateBtn.disabled = false;
          alert("Error: " + error.message);
        });
    });
  }

  // ==========================================
  // 4. VERIFY PAYMENT
  // ==========================================
  function verifyPayment(response, causeSlug, amount, name, email, phone) {
    fetch("../api/verify-payment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        razorpay_order_id: response.razorpay_order_id,
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_signature: response.razorpay_signature,
        cause_slug: causeSlug,
        seva_id: selectedSevaId,
        donation_mode: "one_time",
        amount: amount,
        donor_name: name,
        donor_email: email,
        donor_phone: phone,
      }),
    })
      .then(function (res) {
        if (!res.ok) {
          window.location.href =
            "payment-failed.php?payment_id=" +
            encodeURIComponent(response.razorpay_payment_id);
          return null;
        }
        return res.json();
      })
      .then(function (data) {
        if (data && data.success) {
          window.location.href =
            "payment-success.php?payment_id=" +
            encodeURIComponent(response.razorpay_payment_id) +
            "&order_id=" +
            encodeURIComponent(response.razorpay_order_id) +
            "&amount=" +
            encodeURIComponent(amount) +
            "&cause=" +
            encodeURIComponent(causeSlug);
        } else if (data) {
          window.location.href =
            "payment-failed.php?payment_id=" +
            encodeURIComponent(response.razorpay_payment_id);
        }
      })
      .catch(function () {
        window.location.href =
          "payment-failed.php?payment_id=" +
          encodeURIComponent(response.razorpay_payment_id) +
          "&order_id=" +
          encodeURIComponent(response.razorpay_order_id) +
          "&amount=" +
          encodeURIComponent(amount);
      });
  }

  // ==========================================
  // 5. QUANTITY / MULTI-ITEM FORM UPDATES
  // ==========================================
  // Quantity items
  document.querySelectorAll(".qty-input").forEach(function (input) {
    input.addEventListener("input", function () {
      var qty = parseInt(this.value, 10) || 0;
      var price = parseInt(this.getAttribute("data-price"), 10) || 0;
      var total = qty * price;
      var totalSpan =
        this.closest(".quantity-item").querySelector(".qty-total");
      if (totalSpan) {
        totalSpan.textContent = "= \u20B9" + total.toLocaleString("en-IN");
      }
      updateQuantityTotal();
      if (typeof syncDonationCart === "function") syncDonationCart();
    });
  });

  function updateQuantityTotal() {
    var grandTotal = 0;
    document.querySelectorAll(".qty-input").forEach(function (input) {
      var qty = parseInt(input.value, 10) || 0;
      var price = parseInt(input.getAttribute("data-price"), 10) || 0;
      grandTotal += qty * price;
    });
    var grandTotalSpan = document.querySelector(".grand-total-amount");
    if (grandTotalSpan) {
      grandTotalSpan.textContent =
        "\u20B9" + grandTotal.toLocaleString("en-IN");
    }
    currentAmount = grandTotal || currentAmount;
    updatePayButton();
  }

  // Multi-item cart
  document.querySelectorAll(".cart-qty").forEach(function (input) {
    input.addEventListener("input", function () {
      var qty = parseFloat(this.value) || 0;
      var rate = parseFloat(this.getAttribute("data-rate")) || 0;
      var total = qty * rate;
      var totalSpan =
        this.closest(".cart-item").querySelector(".cart-item-total");
      if (totalSpan) {
        totalSpan.textContent = "\u20B9" + total.toLocaleString("en-IN");
      }
      updateCartTotal();
      if (typeof syncDonationCart === "function") syncDonationCart();
    });
  });

  function updateCartTotal() {
    var grandTotal = 0;
    document.querySelectorAll(".cart-qty").forEach(function (input) {
      var qty = parseFloat(input.value) || 0;
      var rate = parseFloat(input.getAttribute("data-rate")) || 0;
      grandTotal += qty * rate;
    });
    var grandTotalSpan = document.querySelector(".cart-grand-total-amount");
    if (grandTotalSpan) {
      grandTotalSpan.textContent =
        "\u20B9" + grandTotal.toLocaleString("en-IN");
    }
    currentAmount = grandTotal || currentAmount;
    updatePayButton();
  }

  // ==========================================
  // 6. +/- QUANTITY BUTTONS (cart_qty form type)
  //     Uses event delegation on the form — no inline onclick needed.
  // ==========================================
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.qty-btn');
    if (!btn) return;
    
    // Only handle +/- buttons inside cart-qty-items
    var item = btn.closest('.cart-qty-item');
    if (!item) return;
    
    var delta = btn.classList.contains('qty-plus') ? 1 : -1;
    
    var countSpan = item.querySelector('.qty-count');
    var lineTotalSpan = item.querySelector('.cart-qty-line-total');
    var price = parseFloat(item.getAttribute('data-price')) || 0;
    var maxQty = parseInt(item.getAttribute('data-max')) || 99;
    var sevaId = item.getAttribute('data-seva-id');
    var name = item.getAttribute('data-name');
    
    var currentQty = parseInt(countSpan.textContent) || 0;
    var newQty = currentQty + delta;
    
    // Clamp between 0 and max
    if (newQty < 0) newQty = 0;
    if (newQty > maxQty) newQty = maxQty;
    
    countSpan.textContent = newQty;
    
    // Update line total
    var lineTotal = newQty * price;
    lineTotalSpan.textContent = '₹' + lineTotal.toLocaleString('en-IN');
    
    // Toggle active class for visual feedback
    if (newQty > 0) {
      item.classList.add('in-cart');
    } else {
      item.classList.remove('in-cart');
    }
    
    // Update selectedSevas
    if (newQty > 0) {
      selectedSevas[sevaId] = { name: name, price: price, qty: newQty };
    } else {
      delete selectedSevas[sevaId];
    }
    
    recalculateCartQtyTotal();
    if (typeof syncDonationCart === "function") syncDonationCart();
  });

  function recalculateCartQtyTotal() {
    var grandTotal = 0;
    var totalItems = 0;
    var itemsList = [];
    
    for (var key in selectedSevas) {
      if (selectedSevas.hasOwnProperty(key)) {
        var s = selectedSevas[key];
        var itemTotal = s.price * s.qty;
        grandTotal += itemTotal;
        totalItems += s.qty;
        itemsList.push({ name: s.name, qty: s.qty, total: itemTotal });
      }
    }
    
    currentAmount = grandTotal;
    
    // Update summary panel
    var summaryWrap = document.getElementById('cartQtySummary');
    var summaryItems = document.getElementById('cartSummaryItems');
    var totalItemsSpan = document.getElementById('cartTotalItems');
    var grandAmountSpan = document.getElementById('cartGrandAmount');
    
    if (totalItems > 0) {
      if (summaryWrap) summaryWrap.style.display = 'block';
      if (totalItemsSpan) totalItemsSpan.textContent = totalItems + ' item' + (totalItems > 1 ? 's' : '');
      if (grandAmountSpan) grandAmountSpan.textContent = '₹' + grandTotal.toLocaleString('en-IN');
      
      if (summaryItems) {
        summaryItems.innerHTML = '';
        itemsList.forEach(function(item) {
          var div = document.createElement('div');
          div.className = 'cart-qty-summary-item';
          div.innerHTML = '<span class="summary-item-name">' + item.qty + 'x ' + item.name + '</span>' +
            '<span class="summary-item-total">₹' + item.total.toLocaleString('en-IN') + '</span>';
          summaryItems.appendChild(div);
        });
      }
      
      // Update the special instructions
      var instructionsInput = document.getElementById('selectedOfferingsList');
      if (instructionsInput) {
        var text = '';
        itemsList.forEach(function(item) {
          text += item.qty + 'x ' + item.name + ', ';
        });
        instructionsInput.value = text.replace(/, $/, '');
      }
    } else {
      if (summaryWrap) summaryWrap.style.display = 'none';
      if (instructionsInput) instructionsInput.value = '';
    }
    
    updatePayButton();
    
    // Update cart summary box (legacy)
    var oldSummaryWrap = document.getElementById('cartSummaryWrap');
    var oldListElement = document.getElementById('cartItemsList');
    if (oldSummaryWrap && oldListElement) {
      var namesOnly = [];
      for (var k in selectedSevas) {
        if (selectedSevas.hasOwnProperty(k)) {
          namesOnly.push(selectedSevas[k].qty + 'x ' + selectedSevas[k].name);
        }
      }
      if (namesOnly.length > 0) {
        oldSummaryWrap.style.display = 'block';
        oldListElement.innerHTML = '';
        namesOnly.forEach(function(n) {
          var li = document.createElement('li');
          li.textContent = n;
          oldListElement.appendChild(li);
        });
      } else {
        oldSummaryWrap.style.display = 'none';
        oldListElement.innerHTML = '';
      }
    }
  }

  // Legacy toggle (kept for backward compatibility)
  window.toggleDonationSeva = function (id, name, price) {
    var btn = document.getElementById("btn-seva-" + id);
    if (!btn) return;

    if (selectedSevas[id]) {
      delete selectedSevas[id];
      btn.classList.remove("added");
      btn.textContent = "Add Seva";
    } else {
      selectedSevas[id] = { name: name, price: price };
      btn.classList.add("added");
      btn.textContent = "Added";
    }
    recalculateDonationCartTotal();
    if (typeof syncDonationCart === "function") syncDonationCart();
  };

  window.applyDonationDatePreset = function (days, label) {
    var buttons = document.querySelectorAll(".preset-btn");
    var target = event.currentTarget || event.target;

    buttons.forEach(function (btn) {
      btn.classList.remove("active");
    });

    if (dateMultiplier === days && presetName === label) {
      dateMultiplier = 1;
      presetName = "";
    } else {
      dateMultiplier = days;
      presetName = label;
      if (target) target.classList.add("active");
    }

    // Pre-fill date with today if empty
    var dateField = document.getElementById("sevaDate");
    if (dateField && !dateField.value) {
      dateField.value = new Date().toISOString().split("T")[0];
    }

    recalculateDonationCartTotal();
  };

  function recalculateDonationCartTotal() {
    var baseTotal = 0;
    var itemsList = [];

    for (var key in selectedSevas) {
      baseTotal += selectedSevas[key].price;
      itemsList.push(selectedSevas[key].name);
    }

    var finalTotal = baseTotal * dateMultiplier;
    currentAmount = finalTotal;
    updatePayButton();

    // Update cart summary box
    var summaryWrap = document.getElementById("cartSummaryWrap");
    var listElement = document.getElementById("cartItemsList");
    var instructionsInput = document.getElementById("selectedOfferingsList");

    if (itemsList.length > 0) {
      if (summaryWrap) summaryWrap.style.display = "block";
      if (listElement) {
        listElement.innerHTML = "";
        itemsList.forEach(function (name) {
          var li = document.createElement("li");
          li.textContent = name;
          listElement.appendChild(li);
        });
      }
      if (instructionsInput) {
        var instructionsText = itemsList.join(", ");
        if (presetName) {
          instructionsText += " (" + presetName + " duration)";
        }
        instructionsInput.value = instructionsText;
      }
    } else {
      if (summaryWrap) summaryWrap.style.display = "none";
      if (instructionsInput) instructionsInput.value = "";
    }
  }

  // ==========================================
  // 7. COPY TO CLIPBOARD
  // ==========================================
  window.copyToClipboard = function (text) {
    var input = document.createElement("input");
    input.value = text;
    document.body.appendChild(input);
    input.select();
    try {
      document.execCommand("copy");
      var tooltip = document.createElement("span");
      tooltip.textContent = "Copied!";
      tooltip.style.cssText =
        "position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#28a745;color:white;padding:8px 16px;border-radius:6px;font-size:13px;z-index:99999;box-shadow:0 4px 12px rgba(0,0,0,0.2);";
      document.body.appendChild(tooltip);
      setTimeout(function () {
        tooltip.remove();
      }, 2000);
    } catch (e) {
      alert("Copy to clipboard failed. Please copy manually: " + text);
    }
    document.body.removeChild(input);
  };
})();
