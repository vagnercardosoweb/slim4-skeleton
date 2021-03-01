/**
 * Inicia as configurações do CKEditor 4.
 *
 * @param {Object} ckeditors
 *
 * Resolve problema de path
 * window.CKEDITOR_BASEPATH = '/assets/.../bower_components/ckeditor4.11.2/';
 *
 * @see https://ckeditor.com/docs/ckeditor4/latest/index.html
 */

var initCKEditor = function (ckeditors) {
  if (ckeditors.length) {
    $.each(ckeditors, function (index, elementEditor) {
      if (typeof CKEDITOR === 'object') {
        if (CKEDITOR.instances[$(elementEditor).attr('name')]) {
          CKEDITOR.instances[$(elementEditor).attr('name')].destroy();
        }

        CKEDITOR.replace(elementEditor, {
          customConfig: '',
        });
      } else {
        $(elementEditor).html('<p class="alert alert-warning">CKEditor not installed in application.</p>');
      }
    });
  }
};

/* Carrega o documento */
$(document).ready(function () {
  /* INIT :: CKEditor */
  var ckeditors = $(document).find('*[data-toggle="ckeditor"]');

  if (typeof onLoadHtmlSuccess !== 'undefined' && typeof onLoadHtmlSuccess === 'function') {
    onLoadHtmlSuccess(function () {
      initCKEditor(ckeditors);
    });
  } else {
    initCKEditor(ckeditors);
  }
});
