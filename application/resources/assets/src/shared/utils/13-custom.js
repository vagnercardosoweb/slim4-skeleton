/* Carrega o documento */
$(document).ready(function () {
  /* HREF Custom */
  $(document).on('click', '*[data-href]', function () {
    var location = $(this).data('href');

    if (
      location !== undefined &&
      location.length &&
      !location.match(/javascript/g)
    ) {
      window.location.href = location;
    }
  });
});
