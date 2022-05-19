const observer = (entries, klass) => {
  entries.forEach((entry, index) => {
    if (entry.isIntersecting && !entry.target.classList.contains(klass)) {
      const time = setTimeout(() => {
        entry.target.classList.add(klass);
        clearTimeout(time);
      }, 250 * (index + 1));
    }
  });
};
const observerConfig = {
  root: null,
  threshold: 0.25,
};

const time = setTimeout(() => {
  const fadeInObserver = new IntersectionObserver(function (entries) {
    observer(entries, 'fade-in--active');
  }, observerConfig);
  const fadeIn = document.querySelectorAll('.fade-in');
  fadeIn.forEach((element) => fadeInObserver.observe(element));

  const fadeInUpObserver = new IntersectionObserver(function (entries) {
    observer(entries, 'fade-in-up--active');
  }, observerConfig);
  const fadeInUp = document.querySelectorAll('.fade-in-up');
  fadeInUp.forEach((element) => fadeInUpObserver.observe(element));

  clearTimeout(time);
}, 200);
