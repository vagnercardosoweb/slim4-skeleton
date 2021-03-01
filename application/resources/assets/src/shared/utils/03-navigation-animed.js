/**
 * Função para a animação
 *
 * @param {String} id
 */

var scrolling = function (id) {
  var location = $('#' + id);

  if (location !== undefined && location.length) {
    $('html,body').animate({
      scrollTop: (location.offset().top - 120),
    }, 500);
  }
};

/* Carrega o documento */
$(document).ready(function () {
  var hash = window.location.hash.replace('#', '');

  /* Navegação animada ao clicar */
  $(document).on('click', '*[data-goto]', function (event) {
    event.preventDefault();
    event.stopPropagation();

    scrolling($(this).data('goto'));
  });

  /* Navegação animada pelo hash da URL */
  if (hash) {
    window.history.pushState({}, '', '/');
  }

  $(window).load(function () {
    if (hash) {
      window.setTimeout(function () {
        scrolling(hash);
      }, 100);
    }
  });
});
