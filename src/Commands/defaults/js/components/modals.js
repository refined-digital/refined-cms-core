const modalTriggers = document.querySelectorAll('.modal__trigger--button');
const modals = document.querySelectorAll('.modal');
const mobileNavLinks = document.querySelectorAll('.modal__inner .nav__item');
const body = document.querySelector('body');

const modalLookup = {};
if (modals.length) {
    modals.forEach((modal) => {
        modalLookup[modal.dataset.type] = modal;
    });
}

if (mobileNavLinks.length) {
    mobileNavLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const modal = link.closest('.modal');
            if (!link.classList.contains('nav__item--enquire')) {
                modal.classList.remove('modal--active');
            }
        });
    });
}

window.onload = () => {
    modals.forEach((modal) => {
        modal.classList.add('modal--has-loaded');
    });
};

const openModal = (type) => {
    const modal = modalLookup[type];
    if (!modal.classList.contains('modal--has-loaded')) {
        modal.classList.add('modal--has-loaded');
    }
    modal.classList.toggle('modal--active');
};

const modalFn = function (e) {
    e.preventDefault();
    const targetElement = e.target.closest('[data-type]');
    if (!targetElement.dataset.type) {
        return;
    }

    if (modalLookup[targetElement.dataset.type]) {
        openModal(targetElement.dataset.type);
    } else {
        const activeModals = document.querySelectorAll('.modal--active');
        if (activeModals.length) {
            // close the last one
            activeModals[activeModals.length - 1].classList.remove('modal--active');
        } else {
            // open the menu
            openModal('menu');
        }
    }
};

if (modalTriggers.length) {
    modalTriggers.forEach((element) => {
        element.addEventListener('click', modalFn);
    });
}
