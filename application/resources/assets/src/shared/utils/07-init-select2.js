import { parseJSON } from "./00-functions";

/**
 * Inicia as configurações do select2
 *
 * @param {Object} selects2
 *
 * https://select2.org/
 */

export function initSelect2(selects2) {
  if (selects2.length) {
    $.each(selects2, function (key, element) {
      var json = parseJSON(($(element).data('json') || $(element).data('option'))) || {};

      var options = new Object({
        width: 'resolve',
        language: 'pt-BR',
        placeholder: json.placeholder !== undefined ? json.placeholder : '.:: Selecione ::.',
        /*dropdownParent: $(element).parent(),*/
        /*minimumResultsForSearch: -1,*/
      });

      /* Configurações do AJAX */
      if (json && json.url !== undefined) {
        Object.assign(options, {
          placeholder: json.placeholder !== undefined ? json.placeholder : '.:: Pesquise ::.',
          minimumInputLength: json.minimumInputLength !== undefined ? json.minimumInputLength : 0,

          ajax: {
            url: json.url,
            type: json.type !== undefined ? json.type : 'POST',
            dataType: json.dataType !== undefined ? json.dataType : 'json',
            delay: json.delay !== undefined ? json.delay : 250,
            cache: json.cache !== undefined ? json.cache : false,
            headers: { 'X-Csrf-Token': $('meta[name="_csrfToken"]').attr('content') || '' },

            data: function (param) {
              var params = {
                term: param.term || '',
                page: param.page || 1,
              };

              /* Monta data vindo das opções */
              if (json.data !== undefined && (json.data === '' || json.data)) {
                var jsonData = json.data;

                for (var key in jsonData) {
                  if (jsonData.hasOwnProperty(key)) {
                    params[key] = jsonData[key];
                  }
                }
              }

              return params;
            },
          },

          escapeMarkup: function (markup) {
            return markup;
          },

          templateResult: function (state) {
            if (state.loading) {
              return state.text;
            }

            return state.name || state.text;
          },

          templateSelection: function (state) {
            return state.name || state.text;
          },
        });
      }

      /* Inicia o select2 */
      $(element).select2(options);
    });
  }
};

/* Carrega o documento */
$(document).ready(function () {
  /* INIT :: Select2 */
  var selects2 = $(document).find('*[data-toggle="select2"]');

  if (typeof onLoadHtmlSuccess !== 'undefined' && typeof onLoadHtmlSuccess === 'function') {
    onLoadHtmlSuccess(function () {
      initSelect2(selects2);
    });
  } else {
    initSelect2(selects2);
  }
});
