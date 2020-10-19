jQuery(document).ready(function($) {
  var PRELOAD_CLASS = 'js-preload';
  var MENU_ITEM_OPEN_CLASS = 'menu-item--open';
  var prevFocus;

  $(document.body).addClass(PRELOAD_CLASS);
  window.addEventListener("load", function() {
    $(document.body).removeClass(PRELOAD_CLASS);
  });

  // Main menu button
  $('#nav-main button').on('click', function() {
    $(document.body).toggleClass("menu-open");
    this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') !== 'true');
  });

  // Open the submenu on focus
  $(document).on('focus', '#nav-main a', function(event) {
    var target = $(this);
    event.stopPropagation();

    if (prevFocus) {
      if (target.parents('#menu > ul > li').get(0) !== prevFocus.get(0)) {
        prevFocus.removeClass(MENU_ITEM_OPEN_CLASS);
      }
    }

    if (target.is(".menu-item-has-children > a")) {
      prevFocus = target.parent();
      //target.parent().find('ul').show();
      prevFocus.addClass(MENU_ITEM_OPEN_CLASS);
    }
  });

  // Open the menu on tablet tap (when hover isn't working)
  $(document).on('click', '.menu-item-has-children > a', function(event) {
    var target = $(event.target);

    if (target.is(':hover') || noTouchTap === event.target) {
      // Menu already open; Continue with default action
      return;
    }

    if (prevFocus) {
      if (target.parents('#menu > ul > li').get(0) !== prevFocus.get(0)) {
        prevFocus.removeClass(MENU_ITEM_OPEN_CLASS);
      }
    }

    prevFocus = target.parent();
    prevFocus.addClass(MENU_ITEM_OPEN_CLASS);
    noTouchTap = event.target;

    event.preventDefault();
  });

  // Hide menu when escape key pressed
  $(document).on('keyup', function(event) {
    if (event.key === 'Escape') {
      $('.' + MENU_ITEM_OPEN_CLASS).removeClass(MENU_ITEM_OPEN_CLASS);
    }
  });

  // Hide menu when the user clicks outside
  $(document).on('mousedown', function(event) {
    var target = $(event.target);

    if (target.parents('#menu').length === 0) {
      $('.' + MENU_ITEM_OPEN_CLASS).removeClass(MENU_ITEM_OPEN_CLASS);
    }
  });

  // Allow the user to navigate between top level menu items using the
  // arrow keys
  $(document).on('keydown', '#menu > ul > li > a', function(event) {
    var $parent = $(this).parent();
    var item;

    switch (event.key) {
      case 'ArrowRight':
        item = $parent.next();

      break;

      case 'ArrowLeft':
        item = $parent.prev();

      break;

      case 'ArrowDown':
        // Go into submenu
        item = $parent.find('ul li').first();

      break;
    }

    if (item) {
      event.preventDefault();
      // Set focus on the first anchor child
      item.find('> a').focus();
    }
  });

  // Submenu keyboard navigation
  $(document).on('keydown', '#menu ul ul a', function(event) {
    var $parent = $(this).parent();
    var item;

    switch (event.key) {
      case 'ArrowUp':
        item = $parent.prev();

        if (!item.length) {
          // Go back up to the main menu
          item = $parent.parent().parent();
        }

      break;

      case 'ArrowDown':
        item = $parent.next();

        if (!item.length) {
          // Wrap back around to the first item
          item = $parent.parent().find('li').first();
        }

      break;
    }

    if (item) {
      event.preventDefault();
      // Set focus on the first anchor child
      item.find('> a').focus();
    }
  });
});
