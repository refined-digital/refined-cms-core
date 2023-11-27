const modalTriggers = document.querySelectorAll('.modal__trigger--button');
const modals = document.querySelectorAll('.modal');
const mobileNavLinks = document.querySelectorAll('.modal__inner .nav__item');
const body = document.querySelector('body');

const modalLookup = {};
if (modals.length) {
    modals.forEach((modal) => {
        modalLookup[modal.dataset.type] = modal;
        const child = modal.querySelector('.modal__inner');
        modal.addEventListener('click', (e) => {
            if (e.target !== child) {
                closeModal(modal);
            }
        });

        child.addEventListener('click', (e) => {
            e.stopPropagation();
        });
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

const toggleModal = (element) => {
    const modal = modalLookup[element.dataset.type];
    if (!modal.classList.contains('modal--has-loaded')) {
        modal.classList.add('modal--has-loaded');
    }

    // first check if any is open, other than the current modal
    const activeModals = document.querySelectorAll(
        '.modal--active:not([data-type="' + element.dataset.type + '"])'
    );

    if (activeModals.length) {
        activeModals.forEach((el) => {
            closeModal(el);
        });
    }

    modal.classList.toggle('modal--active');
    element.classList.toggle('modal__trigger--active');
};

const closeModal = (modal) => {
    modal.classList.remove('modal--active');
    const trigger = document.querySelector(
        `.modal__trigger--button[data-type="${modal.dataset.type}"]`
    );
    if (trigger) {
        trigger.classList.remove('modal__trigger--active');
    }
};

const modalFn = (e) => {
    e.preventDefault();
    const targetElement = e.target.closest('[data-type]');
    if (!targetElement.dataset.type || !modalLookup[targetElement.dataset.type]) {
        return;
    }

    // toggle the modal
    toggleModal(targetElement);
};

if (modalTriggers.length) {
    modalTriggers.forEach((element) => {
        element.addEventListener('click', modalFn);
    });
}
