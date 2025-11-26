/**
 * Main
 */

'use strict';

let menu, animate;

(function () {
  // Wait for Helpers to be available
  const initMenu = () => {
    if (typeof window.Helpers === 'undefined') {
      // If Helpers isn't available yet, wait and try again
      setTimeout(initMenu, 100);
      return;
    }

    // Initialize menu
    //-----------------

    let layoutMenuEl = document.querySelectorAll('#layout-menu');
    layoutMenuEl.forEach(function (element) {
      menu = new Menu(element, {
        orientation: 'vertical',
        closeChildren: false
      });
      // Change parameter to true if you want scroll animation
      if (window.Helpers && window.Helpers.scrollToActive) {
        window.Helpers.scrollToActive((animate = false));
      }
      if (window.Helpers) {
        window.Helpers.mainMenu = menu;
      }
    });

    // Initialize menu togglers and bind click on each
    let menuToggler = document.querySelectorAll('.layout-menu-toggle');
    menuToggler.forEach(item => {
      item.addEventListener('click', event => {
        event.preventDefault();
        if (window.Helpers && window.Helpers.toggleCollapsed) {
          window.Helpers.toggleCollapsed();
        }
      });
    });

    // Display menu toggle (layout-menu-toggle) on hover with delay
    let delay = function (elem, callback) {
      let timeout = null;
      elem.onmouseenter = function () {
        // Set timeout to be a timer which will invoke callback after 300ms (not for small screen)
        if (window.Helpers && !window.Helpers.isSmallScreen()) {
          timeout = setTimeout(callback, 300);
        } else {
          timeout = setTimeout(callback, 0);
        }
      };

      elem.onmouseleave = function () {
        // Clear any timers set to timeout
        let toggle = document.querySelector('.layout-menu-toggle');
        if (toggle) toggle.classList.remove('d-block');
        clearTimeout(timeout);
      };
    };
    
    if (document.getElementById('layout-menu')) {
      delay(document.getElementById('layout-menu'), function () {
        // not for small screen
        if (window.Helpers && !window.Helpers.isSmallScreen()) {
          let toggle = document.querySelector('.layout-menu-toggle');
          if (toggle) toggle.classList.add('d-block');
        }
      });
    }

    // Display in main menu when menu scrolls
    let menuInnerContainer = document.getElementsByClassName('menu-inner'),
      menuInnerShadow = document.getElementsByClassName('menu-inner-shadow')[0];
    if (menuInnerContainer.length > 0 && menuInnerShadow) {
      menuInnerContainer[0].addEventListener('ps-scroll-y', function () {
        if (this.querySelector('.ps__thumb-y').offsetTop) {
          menuInnerShadow.style.display = 'block';
        } else {
          menuInnerShadow.style.display = 'none';
        }
      });
    }

    // Init helpers & misc
    // --------------------

    // Init BS Tooltip
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Accordion active class
    const accordionActiveFunction = function (e) {
      if (e.type == 'show.bs.collapse' || e.type == 'show.bs.collapse') {
        e.target.closest('.accordion-item').classList.add('active');
      } else {
        e.target.closest('.accordion-item').classList.remove('active');
      }
    };

    const accordionTriggerList = [].slice.call(document.querySelectorAll('.accordion'));
    const accordionList = accordionTriggerList.map(function (accordionTriggerEl) {
      accordionTriggerEl.addEventListener('show.bs.collapse', accordionActiveFunction);
      accordionTriggerEl.addEventListener('hide.bs.collapse', accordionActiveFunction);
    });

    // Auto update layout based on screen size
    if (window.Helpers && window.Helpers.setAutoUpdate) {
      window.Helpers.setAutoUpdate(true);
    }

    // Toggle Password Visibility
    if (window.Helpers && window.Helpers.initPasswordToggle) {
      window.Helpers.initPasswordToggle();
    }

    // Speech To Text
    if (window.Helpers && window.Helpers.initSpeechToText) {
      window.Helpers.initSpeechToText();
    }

    // Manage menu expanded/collapsed with templateCustomizer & local storage
    //------------------------------------------------------------------

    // If current layout is horizontal OR current window screen is small (overlay menu) than return from here
    if (window.Helpers && window.Helpers.isSmallScreen()) {
      return;
    }

    // If current layout is vertical and current window screen is > small

    // Auto update menu collapsed/expanded based on the themeConfig
    if (window.Helpers && window.Helpers.setCollapsed) {
      window.Helpers.setCollapsed(true, false);
    }
  };

  // Start initialization
  initMenu();
})();