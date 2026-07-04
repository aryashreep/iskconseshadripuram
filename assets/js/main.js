/* ============================================
   ISKCON Vrindavan Inspired - Main JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // ----- Elements -----
  const header = document.querySelector('.site-header');
  const hamburger = document.querySelector('.hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');
  const mobileOverlay = document.querySelector('.mobile-menu-overlay');
  const heroSection = document.querySelector('.hero-section');

  // ==========================================
  // 1. STICKY HEADER
  // ==========================================
  function handleHeaderScroll() {
    if (window.scrollY > 80) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }

  window.addEventListener('scroll', handleHeaderScroll, { passive: true });
  handleHeaderScroll(); // initial check

  // ==========================================
  // 2. MOBILE MENU
  // ==========================================
  function toggleMobileMenu(open) {
    const isOpen = open !== undefined ? open : !mobileMenu.classList.contains('active');
    mobileMenu.classList.toggle('active', isOpen);
    mobileOverlay.classList.toggle('active', isOpen);
    hamburger.classList.toggle('active', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
  }

  if (hamburger) {
    hamburger.addEventListener('click', function (e) {
      e.stopPropagation();
      toggleMobileMenu();
    });
  }

  if (mobileOverlay) {
    mobileOverlay.addEventListener('click', function () {
      toggleMobileMenu(false);
    });
  }

  // Close menu on escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('active')) {
      toggleMobileMenu(false);
    }
  });

  // Close menu on link click
  const mobileLinks = mobileMenu ? mobileMenu.querySelectorAll('a') : [];
  mobileLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      toggleMobileMenu(false);
    });
  });

  // ==========================================
  // 3. HERO SLIDER (Swiper)
  // ==========================================
  function initHeroSlider() {
    const heroSwiperEl = document.querySelector('.hero-slider');
    if (!heroSwiperEl || typeof Swiper === 'undefined') return;

    new Swiper(heroSwiperEl, {
      slidesPerView: 1,
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      speed: 1200,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      pagination: {
        el: '.hero-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.hero-button-next',
        prevEl: '.hero-button-prev',
      },
      loop: true,
      preloadImages: false,
      lazy: true,
    });
  }

  initHeroSlider();

  // ==========================================
  // 4. SCROLL REVEAL ANIMATIONS
  // ==========================================
  function initScrollReveal() {
    const elements = document.querySelectorAll('.reveal');

    if (elements.length === 0) return;

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    });

    elements.forEach(function (el) {
      observer.observe(el);
    });
  }

  initScrollReveal();

  // ==========================================
  // 5. SMOOTH SCROLL FOR ANCHOR LINKS
  // ==========================================
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        const headerOffset = header ? header.offsetHeight : 80;
        const elementPosition = target.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    });
  });

  // ==========================================
  // 6. COUNTER ANIMATION (for CTA stats)
  // ==========================================
  function initCounters() {
    const counters = document.querySelectorAll('.cta-stat-number');
    if (counters.length === 0) return;

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          const target = entry.target;
          const targetValue = parseInt(target.getAttribute('data-target'), 10);
          if (isNaN(targetValue)) return;

          let current = 0;
          const increment = Math.ceil(targetValue / 60);
          const duration = 1500;
          const stepTime = Math.floor(duration / (targetValue / increment));

          const timer = setInterval(function () {
            current += increment;
            if (current >= targetValue) {
              target.textContent = targetValue.toLocaleString() + '+';
              clearInterval(timer);
            } else {
              target.textContent = current.toLocaleString();
            }
          }, stepTime);

          observer.unobserve(target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach(function (counter) {
      observer.observe(counter);
    });
  }

  initCounters();

  // ==========================================
  // 7. ACTIVE NAV LINK HIGHLIGHT
  // ==========================================
  function highlightActiveNav() {
    const currentPath = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.main-nav-list a, .mobile-menu-list a');

    navLinks.forEach(function (link) {
      link.classList.remove('active');
      const href = link.getAttribute('href');
      if (href === currentPath) {
        link.classList.add('active');
      }
    });
  }

  highlightActiveNav();

  // ==========================================
  // 8. PRELOADER
  // ==========================================
  const preloader = document.querySelector('.preloader');
  if (preloader) {
    window.addEventListener('load', function () {
      preloader.classList.add('hidden');
      setTimeout(function () {
        preloader.style.display = 'none';
      }, 500);
    });
  }

  // ==========================================
  // 9. WEATHER / TEMPERATURE DISPLAY
  // ==========================================
  function fetchTemperature() {
    const weatherEl = document.getElementById('topBarWeather');
    if (!weatherEl) return;

    // Try caching to avoid hitting the API on every page load
    var cached = localStorage.getItem('isjm_weather');
    if (cached) {
      try {
        var parsed = JSON.parse(cached);
        // v3 cache check: must have text field
        if (parsed.text && Date.now() - parsed.timestamp < 30 * 60 * 1000) {
          weatherEl.innerHTML = '<i class="fas fa-cloud-sun"></i> ';
          weatherEl.appendChild(document.createTextNode(parsed.text));
          return;
        }
      } catch (e) { /* ignore */ }
    }

    // Fetch from wttr.in JSON API (clean data, no HTML)
    fetch('https://wttr.in/Bangalore?format=j1')
    .then(function(res) {
      if (!res.ok) throw new Error('Weather fetch failed');
      return res.json();
    })
    .then(function(json) {
      var cc = json.current_condition && json.current_condition[0];
      if (!cc) throw new Error('No weather data');

      var temp = cc.temp_C || '';
      var desc = (cc.weatherDesc && cc.weatherDesc[0] && cc.weatherDesc[0].value) || '';
      var tempSign = temp.charAt(0) === '-' ? '' : '+';
      var displayText = (desc + ' ' + tempSign + temp + '°C').trim();
      if (displayText === '+°C' || displayText === ' °C' || displayText === '°C') displayText = 'Bangalore';

      weatherEl.innerHTML = '<i class="fas fa-cloud-sun"></i> ';
      weatherEl.appendChild(document.createTextNode(displayText));

      // Cache
      try {
        localStorage.setItem('isjm_weather', JSON.stringify({
          text: displayText,
          timestamp: Date.now()
        }));
      } catch (e) { /* ignore */ }
    })
    .catch(function() {
      // Fallback: show a static location name
      weatherEl.innerHTML = '<i class="fas fa-cloud-sun"></i> Bangalore';
    });
  }

  fetchTemperature();

  // ==========================================
  // 10. BACK TO TOP BUTTON
  // ==========================================
  const backToTop = document.querySelector('.back-to-top');
  if (backToTop) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 400) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    }, { passive: true });

    backToTop.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

});
