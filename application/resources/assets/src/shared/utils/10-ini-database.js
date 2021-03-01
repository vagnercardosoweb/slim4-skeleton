import { mergeObject } from './00-functions';

/**
 * Inicia as configurações do datatable
 *
 * @param datatables
 *
 * https://datatables.net
 */

var initDatatable = function (datatables) {
  /* Verifica se existe datatable */
  if (datatables.length) {
    /* Percore as datatable encontradas */
    $.each(datatables, function (key, element) {
      var option = $(element).data('option');
      var options = {};

      /* Configurações customizadas */
      if (option !== undefined) {
        /* AJAX */
        if (option.url !== undefined && option.url !== '') {
          options = {
            'processing': true,
            'serverSide': true,
            'ajax': {
              'url': option.url,
              'type': 'POST',
              'data': option.data !== undefined ? option.data : {},
            },
          };
        }

        /* ORDER BY */
        if (option.order !== undefined) {
          options.order = [option.order];
        }

        /* Ativar ORDENAÇÃO */
        if (option.ordering !== undefined) {
          options.ordering = option.ordering;
        }
      }

      /* Inicia o datatable */
      $(element).DataTable(mergeObject({
        'destroy': true,
        'iDisplayLength': 50,
        'pagingType': 'full_numbers',
        'lengthMenu': [
          [10, 25, 50, 100, 150, 200, 250, 300, 500, 1000, '-1'],
          [10, 25, 50, 100, 150, 200, 250, 300, 500, 1000, 'Todos'],
        ],
        'language': {
          'sEmptyTable': 'Nenhum registro encontrado',
          'sInfo': 'Mostrando de _START_ até _END_ de _TOTAL_ registros',
          'sInfoEmpty': 'Mostrando 0 até 0 de 0 registros',
          'sInfoFiltered': '(Filtrados de _MAX_ registros)',
          'sInfoPostFix': '',
          'sInfoThousands': '.',
          'sLengthMenu': '_MENU_ resultados por página',
          'sLoadingRecords': 'Carregando...',
          'sProcessing': 'Processando...',
          'sZeroRecords': 'Nenhum registro encontrado',
          'sSearch': 'Pesquisar',
          'oPaginate': {
            'sNext': 'Próximo',
            'sPrevious': 'Anterior',
            'sFirst': 'Primeiro',
            'sLast': 'Último',
          },
          'oAria': {
            'sSortAscending': ': Ordenar colunas de forma ascendente',
            'sSortDescending': ': Ordenar colunas de forma descendente',
          },
          'select': {
            'rows': {
              '_': 'Selecionado %d linhas',
              '0': 'Nenhuma linha selecionada',
              '1': 'Selecionado 1 linha',
            },
          },
        },
      }, options));
    });
  }
};

/* Carrega o documento */
$(document).ready(function () {
  /* INIT :: Datatable */
  initDatatable($('*[data-toggle="datatable"]'));
});
