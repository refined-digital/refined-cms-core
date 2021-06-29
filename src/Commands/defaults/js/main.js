window.$ = window.jQuery = require('jquery');

// load in cycle
require('jquery.cycle2');
require('jquery.cycle2.swipe');


const headerHeight = $('.page__top').height();

window.scrollToBlock = function(element){
  const pos = element.offset().top - headerHeight;
  $('html, body').scrollTop(pos);
};

$('.mobile-menu__trigger, .mobile-menu__close').on('click', function() {
  $('.mobile-menu').toggleClass('mobile-menu--active');
})

$('.scroll-to').on('click', function(e) {
  e.preventDefault();
  scrollToBlock($($(this).attr('href')));
});
