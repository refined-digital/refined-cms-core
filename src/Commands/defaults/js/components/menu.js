const menuClass = 'mobile-menu';
const trigger = document.querySelector(`.${menuClass}__trigger`);
const body = document.querySelector('body');

if (trigger && body) {
  trigger.addEventListener('click', () => {
    body.classList.toggle(`${menuClass}--active`);
  });
}

window.addEventListener('load', () => {
  const menu = document.querySelector(`.${menuClass}`);
  if (menu) {
    menu.classList.add(`${menuClass}--loaded`);
  }
});
