import Splide from '@splidejs/splide';

const sliders = document.querySelectorAll('.banners');
if (sliders.length) {
  sliders.forEach((element) => {
    new Splide(`#${element.id}`, {
      type: 'fade',
      pagination: false,
      rewind: true,
    }).mount();
  });
}
