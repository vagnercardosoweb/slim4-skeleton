let openned = false;

window.addEventListener('DOMContentLoaded', () => {
  const dataToggleEls = document.querySelectorAll<HTMLElement>('[data-toggle]');

  const showInit = () =>
    document.querySelectorAll<HTMLElement>('[data-toggle-init]').forEach(el => {
      el.classList.add('active');
      el.style.maxHeight = el.scrollHeight + 'px';
    });

  showInit();
  document.addEventListener('toggle_class_init', showInit);

  dataToggleEls.forEach(el => {
    el.addEventListener('click', () => {
      const toggleId = el.dataset.toggle;
      if (!toggleId) {
        return;
      }

      const toggleSelector = `[data-toggle-id="${toggleId}"]`;
      const maxHeightSelector = el.dataset.maxHeight ?? toggleSelector;

      if (el.classList.contains('scroll-up')) {
        document.body.classList.toggle('no-scroll');
      }

      document.querySelectorAll<HTMLElement>(toggleSelector).forEach((showEl, index) => {
        const closeParentIdEls = showEl.dataset.parentId;
        if (closeParentIdEls && index === 0) {
          document.querySelectorAll<HTMLElement>(`[data-parent-id="${closeParentIdEls}"]`).forEach(parentIdEl => {
            if (showEl !== parentIdEl) {
              parentIdEl.classList.remove('active');
            }
            (parentIdEl as any).style.maxHeight = null;
          });
        }

        showEl.classList.toggle('active');

        // if (showEl.classList.contains('active')) {
        //   document.querySelectorAll<HTMLDivElement>(maxHeightSelector).forEach(mhEl => {
        //     (mhEl as any).style.maxHeight = null;
        //   });
        // }

        (el.dataset.maxHeight ? showEl : document)
          .querySelectorAll<HTMLDivElement>(maxHeightSelector)
          .forEach(receivedEl => {
            (<any>receivedEl).style.maxHeight = receivedEl.style.maxHeight ? null : `${receivedEl.scrollHeight}px`;
          });
      });
    });
  });
});
