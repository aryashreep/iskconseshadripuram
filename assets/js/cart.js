/* ============================================
   ISJMCart — Global Cart Module
   localStorage-based single-source cart
   ============================================ */
(function () {
  'use strict';

  var STORAGE_KEY = 'isjm_cart';
  var CART_VERSION = 1;

  // Default empty cart
  function emptyCart() {
    return {
      version: CART_VERSION,
      mode: null,           // "donation" | "puja" | null
      source: null,         // { type, slug, title, formType } | null
      items: [],
      context: {},          // puja context or donation notes
      updatedAt: Date.now()
    };
  }

  // Read cart from localStorage
  function getCart() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return emptyCart();
      var cart = JSON.parse(raw);
      // Validate
      if (!cart || typeof cart !== 'object' || cart.version !== CART_VERSION) {
        return emptyCart();
      }
      return cart;
    } catch (e) {
      return emptyCart();
    }
  }

  // Save cart to localStorage
  function saveCart(cart) {
    cart.updatedAt = Date.now();
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    } catch (e) {
      console.warn('ISJMCart: Failed to save cart', e);
    }
    notify(cart);
  }

  // Notify listeners
  function notify(cart) {
    try {
      window.dispatchEvent(new CustomEvent('isjm:cart-updated', { detail: cart }));
    } catch (e) { /* ignore */ }
  }

  // ==========================================
  // PUBLIC API
  // ==========================================
  window.ISJMCart = {

    /** Get full cart object */
    get: getCart,

    /** Save/replace cart entirely */
    save: saveCart,

    /** Clear cart */
    clear: function () {
      saveCart(emptyCart());
      return true;
    },

    /** Get number of items in cart (count of lines with qty > 0) */
    getCount: function () {
      var cart = getCart();
      if (!cart.items || !cart.items.length) return 0;
      // For simple toggle/select items, count items
      if (cart.mode === 'puja') return cart.items.length;
      // For donation, count items where qty > 0
      return cart.items.filter(function (item) { return item.qty > 0; }).length;
    },

    /** Get subtotal from cart items */
    getSubtotal: function () {
      var cart = getCart();
      if (!cart.items || !cart.items.length) return 0;
      var total = 0;
      cart.items.forEach(function (item) {
        total += item.lineTotal || (item.unitAmount * item.qty);
      });
      return total;
    },

    /** Check if a new source is compatible with current cart */
    isCompatible: function (source) {
      var cart = getCart();
      if (!cart.mode || !cart.source) return true; // empty cart, always compatible
      if (cart.mode !== source.mode) return false;  // donation vs puja mismatch
      return cart.source.slug === source.slug;      // same slug OK
    },

    /**
     * Add item to cart. Replaces cart if source is different.
     * @param {object} source - { mode, type, slug, title, formType }
     * @param {object} item - { key, itemId, name, qty, unitAmount, lineTotal, meta? }
     */
    addItem: function (source, item) {
      var cart = getCart();

      // If empty cart, set mode + source
      if (!cart.mode || !cart.source) {
        cart.mode = source.mode;
        cart.source = {
          type: source.type,
          slug: source.slug,
          title: source.title,
          formType: source.formType || 'cart'
        };
      }

      // Check if same item exists — update qty/lineTotal
      var existing = null;
      for (var i = 0; i < cart.items.length; i++) {
        if (cart.items[i].key === item.key) {
          existing = cart.items[i];
          break;
        }
      }

      if (existing) {
        existing.qty = item.qty;
        existing.lineTotal = item.lineTotal || (item.unitAmount * item.qty);
        if (item.meta) existing.meta = item.meta;
      } else {
        cart.items.push({
          key: item.key,
          itemId: item.itemId || item.key,
          name: item.name,
          qty: item.qty,
          unitAmount: item.unitAmount,
          lineTotal: item.lineTotal || (item.unitAmount * item.qty),
          meta: item.meta || {}
        });
      }

      saveCart(cart);
      return true;
    },

    /** Remove item by key */
    removeItem: function (itemKey) {
      var cart = getCart();
      cart.items = cart.items.filter(function (item) { return item.key !== itemKey; });
      if (!cart.items.length) {
        // If no items left, clear completely
        cart.mode = null;
        cart.source = null;
        cart.context = {};
      }
      saveCart(cart);
    },

    /** Update context fields */
    setContext: function (partialContext) {
      var cart = getCart();
      for (var key in partialContext) {
        if (partialContext.hasOwnProperty(key)) {
          cart.context[key] = partialContext[key];
        }
      }
      saveCart(cart);
    },

    /** Update a specific item's fields */
    updateItem: function (itemKey, updates) {
      var cart = getCart();
      for (var i = 0; i < cart.items.length; i++) {
        if (cart.items[i].key === itemKey) {
          for (var key in updates) {
            if (updates.hasOwnProperty(key)) {
              cart.items[i][key] = updates[key];
            }
          }
          // Recalculate line total if qty or unitAmount changed
          if (updates.qty !== undefined || updates.unitAmount !== undefined) {
            cart.items[i].lineTotal = cart.items[i].qty * cart.items[i].unitAmount;
          }
          break;
        }
      }
      saveCart(cart);
    },

    /** Replace entire cart from a donation/puja page */
    replaceFromPage: function (source, items, context) {
      var cart = emptyCart();
      cart.mode = source.mode;
      cart.source = {
        type: source.type,
        slug: source.slug,
        title: source.title,
        formType: source.formType || 'cart'
      };
      cart.items = items.map(function (item) {
        return {
          key: item.key,
          itemId: item.itemId || item.key,
          name: item.name,
          qty: item.qty,
          unitAmount: item.unitAmount,
          lineTotal: item.lineTotal || (item.unitAmount * item.qty),
          meta: item.meta || {}
        };
      });
      cart.context = context || {};
      saveCart(cart);
    },

    /** Sync cart badge in header */
    syncBadge: function () {
      var count = window.ISJMCart.getCount();
      var badges = document.querySelectorAll('[data-cart-count]');
      badges.forEach(function (badge) {
        if (count > 0) {
          badge.textContent = count > 99 ? '99+' : count;
          badge.removeAttribute('hidden');
        } else {
          badge.setAttribute('hidden', '');
        }
      });
    },

    /** Subscribe to cart changes */
    subscribe: function (callback) {
      window.addEventListener('isjm:cart-updated', function (e) {
        callback(e.detail);
      });
    },

    /** Checkout URL */
    checkoutUrl: function () {
      return 'checkout/';
    }
  };

  // ==========================================
  // AUTO-INIT: sync badge on page load
  // ==========================================
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      window.ISJMCart.syncBadge();
    });
  } else {
    window.ISJMCart.syncBadge();
  }

  // Listen for cart updates to sync badge
  window.ISJMCart.subscribe(function () {
    window.ISJMCart.syncBadge();
  });

})();
