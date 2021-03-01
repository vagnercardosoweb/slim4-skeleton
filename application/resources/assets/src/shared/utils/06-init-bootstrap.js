import { vcAjax } from './01-request-form';
import { parseJSON } from "./00-functions";

/* Carrega o documento */
$(document).ready(function () {
  /* INIT :: Tooltip */

  $('*[data-toggle="tooltip"]').tooltip();

  /* INIT :: Modal */

  /*
   * USAGE TWIG TEMPLATE
   *
   * {% set modalId = {
   *   'id':'#modalId',
   *   'url':'',
   *   'data':{
   *     'param':'',
   *     'param1':''
   *   }
   * } %}
   *
   * <button data-modal="{{ modalId|json_encode }}">---</button>
   */

  $(document).on('click', '*[data-modal]', function (event) {
    event.preventDefault();
    event.stopPropagation();

    /* Variáveis */
    var modal = parseJSON($(this).data('modal')) || {};
    var formData = new FormData();

    /* Verifica se a modal existe */
    if (modal.id !== undefined && $(modal.id).length) {
      /* Abre modal */
      $(modal.id).modal({
        backdrop: 'static', // 'static' caso não queira fechar ao clicar fora da modal
        show: true,
      });

      /* Verifica opções */
      if (modal.url !== undefined || modal.html !== undefined) {
        $(modal.id).find('.modal-body').html('<p class="text-center mb-0 mbottom-0">Aguarde carregando dados...</p>');

        /* Insere um html caso tenha */
        if (modal.html !== undefined) {
          $(modal.id).find('.modal-body').html(modal.html);
        }

        /* Configuração do AJAX */
        if (modal.url !== undefined) {
          /* FormData */
          var data = modal.data;

          if (data !== undefined) {
            for (var key in data) {
              if (data.hasOwnProperty(key)) {
                formData.append(key, (data[key] !== undefined ? data[key] : ''));
              }
            }
          }

          /* Realiza a requisição */
          if (modal.method !== undefined) {
            formData.append('_METHOD', modal.method);
          }

          vcAjax($(this), modal.url, formData, 'POST', {}, true, $(modal.id));
        }
      }
    }
  });
});
