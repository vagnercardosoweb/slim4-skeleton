import './jquery';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-scroll-to]').forEach(element => {
    element.addEventListener('click', e => {
      e.preventDefault();

      const target = e.target as HTMLAnchorElement;

      document.getElementById(target.dataset.scrollTo!)?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
      });
    });
  });

  document.querySelectorAll('[data-copy-value]').forEach(el => {
    el.addEventListener('click', async e => {
      e.preventDefault();

      const text = el.getAttribute('data-copy-value');

      let message = el.getAttribute('data-copy-message');
      if (message === null) message = 'Copiado com sucesso';

      if (text && 'clipboard' in window.navigator) {
        await window.navigator.clipboard.writeText(text);

        const oldText = el.innerHTML;
        el.innerHTML = message;

        window.setTimeout(() => {
          el.innerHTML = oldText;
        }, 1500);
      }
    });
  });
});
