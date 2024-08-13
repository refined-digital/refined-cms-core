const accordions = document.querySelectorAll('.accordion__header');
const panel = '.accordion__panel';
const activeClass = `${panel.replace('.', '')}--open`;

const toggleAccordion = (pane) => {
  const paneIsOpen = pane.classList.contains(activeClass);

  if (paneIsOpen) {
    pane.classList.remove(activeClass);
  } else {
    pane.classList.add(activeClass);
  }
};

if (accordions) {
  accordions.forEach((accordion) => {
    accordion.addEventListener('click', (e) => {
      const activePanel = e.target.closest(panel);
      if (!activePanel) return;
      toggleAccordion(activePanel);
    });
  });
}
