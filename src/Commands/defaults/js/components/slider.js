import Swiper, { Navigation, Pagination, Autoplay } from 'swiper';

const sliders = document.querySelectorAll('.banners .swiper');
if (sliders) {
  sliders.forEach((slide) => {
    new Swiper(slide, {
      modules: [Navigation, Pagination, Autoplay],
      loop: true,
      autoplay: true,
      speed: 1500,
      navigation: {
        nextEl: `#${slide.id} .swiper__button--next`,
        prevEl: `#${slide.id} .swiper__button--prev`,
      },
      pagination: {
        el: slide.querySelector('.swiper__pagination'),
        clickable: true,
      },
    });
  });
}
