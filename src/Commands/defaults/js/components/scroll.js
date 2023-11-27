const links = document.querySelectorAll('.scroll-to');
window.scrollToBlock = function (hash) {
  const viewElement = document.querySelector(hash);
  if (viewElement) {
    viewElement.scrollIntoView();
  }
};

if (links.length) {
  links.forEach((element) => {
    element.addEventListener('click', (e) => {
      e.preventDefault();
      scrollToBlock(element.hash);
    });
  });
}
