const buttonModalOpenEls = document.querySelectorAll<HTMLButtonElement>('[data-modal-open]');

export const closeOpenedModals = () => {
  document.body.style.overflow = 'auto';
  const otherModelEls = document.querySelectorAll(`[data-modal-id]`);
  otherModelEls.forEach(el => el.classList.remove('active'));
};

export const openModalById = (id: string) => {
  document.body.style.overflow = 'hidden';
  const modalEl = document.querySelector(`[data-modal-id="${id}"]`);
  closeOpenedModals();
  modalEl?.classList.add('active');
};

buttonModalOpenEls.forEach(buttonEl => {
  buttonEl.addEventListener('click', event => {
    event.preventDefault();
    const currentId = buttonEl.dataset.modalOpen;
    if (!currentId) return;
    openModalById(currentId);
  });
});

const buttonModalCloseEls = document.querySelectorAll<HTMLButtonElement>('[data-modal-close]');

buttonModalCloseEls.forEach(element => {
  element.addEventListener('click', () => {
    document.body.style.overflow = 'auto';
    const selectorModal = `[data-modal-id="${element.dataset.modalClose}"]`;
    const modalEl = document.querySelector(selectorModal);
    modalEl?.classList.remove('active');
  });
});
