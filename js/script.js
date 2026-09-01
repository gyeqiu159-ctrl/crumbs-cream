/* =========================================================
   CRUMB & CREAM — Graham Bars Landing Page
   Script
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {

  /* -------------------- Sticky / Blurred Navbar -------------------- */
  var navbar = document.getElementById('navbar');

  function handleNavbarScroll() {
    if (window.scrollY > 40) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  }
  handleNavbarScroll();
  window.addEventListener('scroll', handleNavbarScroll);

  /* -------------------- Mobile Hamburger Menu -------------------- */
  var hamburger = document.getElementById('hamburger');
  var navLinks = document.getElementById('navLinks');

  function closeMenu() {
    hamburger.classList.remove('active');
    navLinks.classList.remove('active');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  function toggleMenu() {
    var isOpen = navLinks.classList.toggle('active');
    hamburger.classList.toggle('active', isOpen);
    hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    document.body.style.overflow = isOpen ? 'hidden' : '';
  }

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', toggleMenu);

    // Close mobile menu when a nav link is clicked
    document.querySelectorAll('.nav-link').forEach(function (link) {
      link.addEventListener('click', closeMenu);
    });
  }

  /* -------------------- FAQ Accordion -------------------- */
  var faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(function (item) {
    var question = item.querySelector('.faq-question');

    question.addEventListener('click', function () {
      var isActive = item.classList.contains('active');

      // Close all other items for a clean single-open accordion
      faqItems.forEach(function (other) {
        other.classList.remove('active');
      });

      if (!isActive) {
        item.classList.add('active');
      }
    });
  });

  /* -------------------- Product Size Selector -------------------- */
  var sizeOptions = document.querySelectorAll('.size-option');

  sizeOptions.forEach(function (option) {
    option.addEventListener('click', function () {
      sizeOptions.forEach(function (o) { o.classList.remove('active'); });
      option.classList.add('active');
    });
  });

  /* -------------------- Quantity Selector (visual only) -------------------- */
  var qtyValue = document.getElementById('qtyValue');
  var qtyMinus = document.getElementById('qtyMinus');
  var qtyPlus = document.getElementById('qtyPlus');
  var quantity = 1;
  var MIN_QTY = 1;
  var MAX_QTY = 20;

  function updateQtyDisplay() {
    qtyValue.textContent = quantity;
  }

  if (qtyMinus && qtyPlus && qtyValue) {
    qtyMinus.addEventListener('click', function () {
      if (quantity > MIN_QTY) {
        quantity -= 1;
        updateQtyDisplay();
      }
    });

    qtyPlus.addEventListener('click', function () {
      if (quantity < MAX_QTY) {
        quantity += 1;
        updateQtyDisplay();
      }
    });
  }

  /* -------------------- Scroll Reveal Animations -------------------- */
  var revealEls = document.querySelectorAll('.reveal');
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (prefersReducedMotion) {
    revealEls.forEach(function (el) { el.classList.add('in-view'); });
  } else if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

    revealEls.forEach(function (el) { observer.observe(el); });
  } else {
    // Fallback for browsers without IntersectionObserver support
    revealEls.forEach(function (el) { el.classList.add('in-view'); });
  }

  /* -------------------- Smooth Scroll for Anchor Links -------------------- */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var targetId = this.getAttribute('href');
      if (targetId.length > 1) {
        var targetEl = document.querySelector(targetId);
        if (targetEl) {
          e.preventDefault();
          var offset = 90;
          var top = targetEl.getBoundingClientRect().top + window.pageYOffset - offset;
          window.scrollTo({ top: top, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
        }
      }
    });
  });

  /* -------------------- Flavors Carousel -------------------- */
  var carouselTrack = document.getElementById('carouselTrack');
  var carouselSlides = document.querySelectorAll('.carousel-slide');
  var carouselPrev = document.getElementById('carouselPrev');
  var carouselNext = document.getElementById('carouselNext');
  var carouselIndicators = document.querySelectorAll('.carousel-indicators .indicator');
  var currentSlide = 0;

  function updateCarousel() {
    var offset = -currentSlide * 100;
    carouselTrack.style.transform = 'translateX(' + offset + '%)';
    
    carouselSlides.forEach(function (slide, index) {
      slide.classList.toggle('active', index === currentSlide);
    });
    
    carouselIndicators.forEach(function (indicator, index) {
      indicator.classList.toggle('active', index === currentSlide);
    });
  }

  if (carouselPrev && carouselNext) {
    carouselPrev.addEventListener('click', function () {
      currentSlide = (currentSlide - 1 + carouselSlides.length) % carouselSlides.length;
      updateCarousel();
    });

    carouselNext.addEventListener('click', function () {
      currentSlide = (currentSlide + 1) % carouselSlides.length;
      updateCarousel();
    });
  }

  carouselIndicators.forEach(function (indicator) {
    indicator.addEventListener('click', function () {
      currentSlide = parseInt(this.getAttribute('data-slide'));
      updateCarousel();
    });
  });

});
