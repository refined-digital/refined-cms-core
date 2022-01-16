const top = document.querySelector('.page__top');
const activeTopClass = 'page__top--active';
const banner = document.querySelector('.banner');
const scrollAmount = banner ? banner.clientHeight / 2 : 200;

const scrollFn = function (event) {
  if (
    window.scrollY > scrollAmount &&
    !top.classList.contains(activeTopClass)
  ) {
    top.classList.add(activeTopClass);
  }

  if (window.scrollY < scrollAmount && top.classList.contains(activeTopClass)) {
    top.classList.remove(activeTopClass);
  }
};
if (top) {
  document.addEventListener('scroll', scrollFn);
}
