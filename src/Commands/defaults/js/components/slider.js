import Swiper, { Navigation } from 'swiper';

const sliders = document.querySelector('.banners');
if (sliders) {
  new Swiper('.banners .swiper', {
    modules: [Navigation],
    loop: true,
    navigation: {
      nextEl: '.banners .swiper-button-next',
      prevEl: '.banners .swiper-button-prev',
    },
  });
}
