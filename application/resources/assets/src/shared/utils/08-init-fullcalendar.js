/**
 * Inicia as configurações do full calendar
 *
 * @param {Object} calendars
 *
 * https://fullcalendar.io/
 */

var initFullCalendar = function (calendars) {
  if (calendars.length) {
    $.each(calendars, function (index, element) {
      var option = $(element).data('option');
      var method = 'POST';

      /* Verifica a URL da requisição */
      if (option === undefined || option.url === undefined) {
        $(element)
          .empty()
          .html('<div class="alert alert-danger text-center mb-0">È Preciso passar a URL para o funcionamento do FULL CALENDAR.</div>');

        return;
      }

      /* Verifica método */
      if (option.method !== undefined && option.method !== '') {
        method = option.method;
      }

      /* Inicia o fullCalendar */
      $(element).fullCalendar({
        /*header: {
         left: 'prev,next today',
         center: 'title',
         right: 'month,agendaWeek,agendaDay,listWeek',
         },*/
        lang: 'pt-br',
        defaultDate: new Date(),
        navLinks: true,
        editable: false,
        eventLimit: true,
        eventStartEditable: false,
        events: {
          url: option.url,
          data: option.data !== undefined ? option.data : {},
          type: method.toUpperCase(),
          dataType: 'json',
          headers: {
            'X-Http-Method-Override': method.toUpperCase(),
          },
          cache: true,
          success: function (response) {
            /* Verifica se ocorreu erro */
            if (response.error) {
              $(element)
                .empty()
                .html('<div class="alert alert-danger text-center mb-0">' + response.error + '</div>');
            }
          },
          error: function () {
            $(element)
              .empty()
              .html(
                '<div class="alert alert-danger text-center mb-0">Não foi possível carregar o calendário, se o erro persistir entre em contato conosco.</div>');
          },
        },
      });
    });
  }
};

/* Carrega o documento */
$(document).ready(function () {
  /* INIT :: Full Calendar */
  var calendars = $(document).find('*[data-toggle="fullcalendar"]');

  if (typeof onLoadHtmlSuccess !== 'undefined' && typeof onLoadHtmlSuccess === 'function') {
    onLoadHtmlSuccess(function () {
      initFullCalendar(calendars);
    });
  } else {
    initFullCalendar(calendars);
  }
});
