/**
 * Scroll-triggered entrance animations.
 *
 * CSS owns both states and hides the targets from first paint; this only sets the
 * stagger index and toggles the reveal class. Selector list is mirrored in
 * resources/css/partials/_animations.css — keep the two in sync.
 */
const REVEAL_SELECTOR = '.page__block .grid > *, .fade-in, .fade-in-up';
const REVEALED_CLASS = 'is-revealed';

// past this many siblings every element shares the same delay, so a 12-item grid
// finishes in ~400ms instead of over a second
const STAGGER_CAP = 4;

const targets = document.querySelectorAll(REVEAL_SELECTOR);

targets.forEach((el) => {
  const siblings = el.parentElement ? [...el.parentElement.children] : [el];
  el.style.setProperty('--anim-i', Math.min(siblings.indexOf(el), STAGGER_CAP));
});

const observer = new IntersectionObserver(
  (entries, obs) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) {
        return;
      }
      entry.target.classList.add(REVEALED_CLASS);
      // animate once — also stops the observer running for the page lifetime
      obs.unobserve(entry.target);
    });
  },
  {
    root: null,
    // a block taller than the viewport can never reach a high visibility ratio,
    // so a fractional threshold makes tall blocks fire late or not at all
    threshold: 0,
    rootMargin: '0px 0px -10% 0px',
  }
);

targets.forEach((el) => observer.observe(el));
