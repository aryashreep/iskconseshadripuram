/**
 * Puja Booking Detail Page JavaScript
 * 
 * Reads config from #pujaDetailApp data-config attribute.
 * Depends on: cart.js (ISJMCart global), Razorpay checkout.js
 */

(function () {
  "use strict";

  // Read config from data attribute (set by PHP)
  var appEl = document.getElementById("pujaDetailApp");
  if (!appEl) return;

  var configStr = appEl.getAttribute("data-config");
  if (!configStr) {
    console.error("puja-detail.js: missing data-config attribute");
    return;
  }

  var CONFIG = JSON.parse(configStr);
  var RAZORPAY_CONFIG = CONFIG.razorpay;
  var currentPujaSlug = CONFIG.pujaSlug;
  var currentPujaName = CONFIG.pujaName;

  // State
  var selectedOfferings = {};
  var dateMultiplier = 1;
  var presetName = "";

  // Sync local offerings to global cart
  function syncPujaCart() {
    var source = {
      mode: "puja",
      type: "puja",
      slug: currentPujaSlug,
      title: currentPujaName,
      formType: "puja",
    };

    var items = [];
    for (var key in selectedOfferings) {
      items.push({
        key: key,
        itemId: key,
        name: selectedOfferings[key].name,
        qty: 1,
        unitAmount: selectedOfferings[key].price,
        lineTotal: selectedOfferings[key].price * dateMultiplier,
      });
    }

    var context = {
      dateMultiplier: dateMultiplier,
      presetName: presetName || "",
      pujaDate: document.getElementById("pujaDate") ? document.getElementById("pujaDate").value : "",
      gotra: document.getElementById("gotra") ? document.getElementById("gotra").value : "",
      relation: document.getElementById("relation") ? document.getElementById("relation").value : "Self",
      occasion: document.getElementById("occasion") ? document.getElementById("occasion").value : "",
    };

    if (typeof ISJMCart !== "undefined") {
      var currentCart = ISJMCart.get();
      if (currentCart.mode && !ISJMCart.isCompatible(source)) {
        if (!confirm("Your cart already contains items from another seva. Clear cart and add this puja?")) {
          return false;
        }
        ISJMCart.clear();
      }
      if (items.length > 0) {
        ISJMCart.replaceFromPage(source, items, context);
      }
    }
    return true;
  }

  window.toggleOffering = function (id, name, price) {
    var btn = document.getElementById("btn-" + id);
    if (selectedOfferings[id]) {
      delete selectedOfferings[id];
      btn.classList.remove("added");
      btn.textContent = "Add to Puja";
    } else {
      selectedOfferings[id] = { name: name, price: price };
      btn.classList.add("added");
      btn.textContent = "Added";
    }
    recalculateTotal();
    syncPujaCart();
  };

  window.applyDatePreset = function (days, label) {
    document.querySelectorAll(".preset-btn").forEach(function (btn) {
      btn.classList.remove("active");
    });

    if (dateMultiplier === days && presetName === label) {
      dateMultiplier = 1;
      presetName = "";
    } else {
      dateMultiplier = days;
      presetName = label;
      if (event && event.target) event.target.classList.add("active");
    }

    var dateField = document.getElementById("pujaDate");
    if (!dateField.value) {
      var today = new Date().toISOString().split("T")[0];
      dateField.value = today;
    }

    recalculateTotal();
    syncPujaCart();
  };

  function recalculateTotal() {
    var baseTotal = 0;
    var itemsList = [];

    for (var key in selectedOfferings) {
      baseTotal += selectedOfferings[key].price;
      itemsList.push(selectedOfferings[key].name);
    }

    var finalTotal = baseTotal * dateMultiplier;
    document.getElementById("totalAmountVal").value = finalTotal;
    document.getElementById("totalAmountLabel").textContent = "₹" + finalTotal.toLocaleString("en-IN");

    var summaryWrap = document.getElementById("cartSummaryWrap");
    var listElement = document.getElementById("cartItemsList");
    var instructionsInput = document.getElementById("selectedOfferingsList");

    if (itemsList.length > 0) {
      summaryWrap.style.display = "block";
      listElement.innerHTML = "";
      itemsList.forEach(function (name) {
        var li = document.createElement("li");
        li.textContent = name;
        listElement.appendChild(li);
      });
      var instructions = itemsList.join(", ");
      if (presetName) instructions += " (" + presetName + " duration)";
      instructionsInput.value = instructions;

      var bookBtn = document.getElementById("bookBtn");
      bookBtn.disabled = false;
      bookBtn.textContent = "Book & Pay ₹" + finalTotal.toLocaleString("en-IN");
    } else {
      summaryWrap.style.display = "none";
      instructionsInput.value = "";
      var bookBtn = document.getElementById("bookBtn");
      bookBtn.disabled = true;
      bookBtn.textContent = "Choose Any Offering";
    }
  }

  // Form submission
  document.getElementById("pujaCartBookingForm").addEventListener("submit", function (e) {
    e.preventDefault();

    var bookLoading = document.getElementById("bookLoading");
    var bookBtn = document.getElementById("bookBtn");

    var pujaName = document.getElementById("selectedPujaName").value;
    var finalAmount = parseInt(document.getElementById("totalAmountVal").value, 10);
    var dateVal = document.getElementById("pujaDate").value;
    var occasionVal = document.getElementById("occasion").value;
    var gotraVal = document.getElementById("gotra").value;
    var relationVal = document.getElementById("relation").value;
    var specialInstructionsVal = document.getElementById("selectedOfferingsList").value;
    var donorNameVal = document.getElementById("donorName").value;
    var donorEmailVal = document.getElementById("donorEmail").value;
    var donorPhoneVal = document.getElementById("donorPhone").value;
    var panNumberVal = document.getElementById("panNumber") ? document.getElementById("panNumber").value.trim() : "";

    if (finalAmount <= 0) { alert("Please select at least one puja offering."); return; }
    if (!dateVal) { alert("Please select a starting date."); return; }

    if (bookLoading) bookLoading.style.display = "flex";
    if (bookBtn) bookBtn.disabled = true;

    var payload = {
      amount: finalAmount * 100,
      donor_name: donorNameVal.trim(),
      donor_email: donorEmailVal.trim(),
      donor_phone: donorPhoneVal.trim(),
      pan_number: panNumberVal,
      puja_type: pujaName,
      puja_date: dateVal,
      occasion: occasionVal ? relationVal + ": " + occasionVal : relationVal + " offering",
      person_name: donorNameVal.trim(),
      gotra: gotraVal.trim(),
      rashi: relationVal,
      nakshatra: presetName || "Standard",
      special_instructions: specialInstructionsVal,
    };

    fetch("../../api/create-booking-order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then(function (response) {
        if (!response.ok) return response.json().then(function (err) { throw new Error(err.error || "Failed to create order"); });
        return response.json();
      })
      .then(function (result) {
        var options = {
          key: RAZORPAY_CONFIG.keyId,
          amount: result.amount,
          currency: result.currency,
          name: RAZORPAY_CONFIG.siteName,
          description: pujaName,
          order_id: result.order_id,
          prefill: { name: donorNameVal.trim(), email: donorEmailVal.trim(), contact: donorPhoneVal.trim() },
          theme: { color: "#c86b1f" },
          handler: function (response) {
            verifyBookingPayment(response, pujaName, finalAmount * 100, donorNameVal.trim(), donorEmailVal.trim(), donorPhoneVal.trim());
          },
          modal: {
            ondismiss: function () {
              if (bookLoading) bookLoading.style.display = "none";
              if (bookBtn) bookBtn.disabled = false;
            },
          },
        };
        var rzp1 = new Razorpay(options);
        rzp1.on("payment.failed", function () {
          window.location.href = "../../donate/payment-failed.php?cause=" + encodeURIComponent(pujaName);
        });
        rzp1.open();
      })
      .catch(function (error) {
        if (bookLoading) bookLoading.style.display = "none";
        if (bookBtn) bookBtn.disabled = false;
        alert("Error: " + error.message);
      });
  });

  function verifyBookingPayment(response, pujaName, amount, name, email, phone) {
    fetch("../../api/verify-payment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        razorpay_order_id: response.razorpay_order_id,
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_signature: response.razorpay_signature,
        cause_slug: "booking-puja",
        seva_id: null,
        donation_mode: "one_time",
        amount: amount,
        donor_name: name,
        donor_email: email,
        donor_phone: phone,
      }),
    })
      .then(function (res) {
        if (!res.ok) { window.location.href = "../../donate/payment-failed.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id); return null; }
        return res.json();
      })
      .then(function (data) {
        if (data && data.success) {
          window.location.href =
            "../../donate/payment-success.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id) +
            "&order_id=" + encodeURIComponent(response.razorpay_order_id) +
            "&amount=" + encodeURIComponent(amount) +
            "&cause=" + encodeURIComponent(pujaName);
        } else if (data) {
          window.location.href = "../../donate/payment-failed.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id);
        }
      })
      .catch(function () {
        window.location.href =
          "../../donate/payment-failed.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id) +
          "&order_id=" + encodeURIComponent(response.razorpay_order_id);
      });
  }
})();
