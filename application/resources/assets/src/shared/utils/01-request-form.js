import { initMaskInput } from './09-init-mask';
import { initSelect2 } from './07-init-select2';
import { parseJSON } from './00-functions';

/**
 * Recupera o url do elemento
 *
 * @param {Object} element
 * @param {String} http
 *
 * @returns {string}
 */
function checkElementUrl(element, http) {
  http = http || 'get';
  var url = element.attr('vc-' + http);
  var href = element.attr('href') || element.data('href') || '';
  var hash = href.substr(0, 1) === '#';

  if (!hash && href) {
    if (!href.match(/^#|javascript/g)) {
      url = href;
    }
  }

  return url;
}

/**
 * Mostra mensagem de retorno
 *
 * @param {Object} json
 * @param {Object} elementMessage
 *
 * @return {void}
 */
function showMessage(json, elementMessage) {
  /* Variávies */
  var type = '';
  var message = '';

  /* Verifica o retorno do json */
  if (json.trigger) {
    type = json.trigger.type || json.trigger[0];
    message = json.trigger.message || json.trigger[1];
  } else if (json.error) {
    type = json.error.type || 'danger';
    message = json.error.message;
  } else if ('error' in json && !json.error && json.message) {
    type = json.type || 'success';
    message = json.message;
  }

  /* Printa ou alerta a mensagem */
  if (message) {
    if (elementMessage !== undefined && elementMessage.length > 0) {
      elementMessage
        .html('<div class="alert alert-' + type + '">' + message + '</div>')
        .fadeIn(0);
    } else {
      alert(message);
    }
  }
}

/**
 * Verifica se o elemento tem confirmação
 *
 * @param {Object} element
 *
 * @return {boolean}
 */
function checkElementConfirm(element) {
  if (
    element.attr('vc-confirm') !== undefined &&
    (element.attr('vc-confirm') === '' || element.attr('vc-confirm'))
  ) {
    var verify = confirm(
      element.attr('vc-confirm').length > 0
        ? element.attr('vc-confirm')
        : 'Essa ação é irreversível.\nDeseja continuar?',
    );

    if (verify === false) {
      return false;
    }
  }

  return true;
}

/**
 * Verifica se contém :input obrigatórios
 *
 * @param {Object} form
 *
 * @return {boolean}
 */
function checkFormRequired(form) {
  /* Variáveis */
  var errors = 0;

  /* Verifica se existe campos obrigatório no formulário antes de enviar a requisição */
  form.find(':input[required]').each(function (key, element) {
    // Variávies
    var value = $(element).val() || false;
    var message = '';

    // Verifica se e textarea e caso não tenha valor
    // pega o html
    if (
      $(element)
        .prop('tagName')
        .toLowerCase() === 'textarena' &&
      !value
    ) {
      value = $(element).html();
    }

    // Remove as classe de erro
    if ($(element).hasClass('vc-error-field')) {
      $(element)
        .removeClass('vc-error-field')
        .parent()
        .removeClass('vc-error')
        .find('.vc-error-box')
        .remove();
    }

    // Adiciona as classe de erro
    if (!value && value !== '0') {
      errors++;
      message = $(element).data('message') ?? 'Campo obrigatório.';

      $(element)
        .addClass('vc-error-field')
        .after(`<div class="vc-error-box">${message}</div>`)
        .parent()
        .addClass('vc-error');
    }
  });

  /* Bloqueia o envio do ajax e foca no 1° input com erro */
  if (errors > 0) {
    form.find('.vc-error-field:first').focus();
    errors = 0;

    return false;
  }

  return true;
}

/**
 * Monta o form data no form
 *
 * @param {Object} form
 * @param {Object} formData
 *
 * @return {void}
 */
function mountFormData(form, formData) {
  /* Caso tenha o tinymce */
  if (typeof tinyMCE === 'object') {
    tinyMCE.triggerSave();
  }

  /* Percorre todos elemento */
  form.find('*').each(function (key, element) {
    if ($(element).attr('name')) {
      if ($(element).prop('type') === 'checkbox') {
        //if ($(element).prop('checked') !== false) {
        formData.append(
          $(element).attr('name'),
          $(element).prop('checked') !== false ? $(element).val() : '',
        );
        //}
      } else if ($(element).prop('type') === 'radio') {
        if ($(element).prop('checked') !== false) {
          formData.append($(element).attr('name'), $(element).val());
        }
      } else if ($(element).prop('type') === 'file') {
        var files = $(element).prop('files');

        if (files !== undefined && files.length > 0) {
          $.each(files, function (key, file) {
            formData.append($(element).attr('name'), file, file.name);
          });
        }
      } else {
        var name = $(element).attr('name');
        var value = $(element).val() || '';

        if (
          $(element)
            .prop('tagName')
            .toLowerCase() === 'textarea' &&
          value === ''
        ) {
          value = $(element).html();
        }

        /* CKEditor */
        if (
          typeof CKEDITOR === 'object' &&
          typeof CKEDITOR.instances[name] === 'object'
        ) {
          value = CKEDITOR.instances[name].getData();
        }

        formData.append(name, value);
      }
    }
  });
}

/**
 * Verifica se existe redirecionamento
 * ou atualizar a página
 *
 * @param {Object} json
 */
function redirectAndReload(json) {
  /* Redireciona para uma nova página */
  if (json.location) {
    if (
      typeof loadPage !== 'undefined' &&
      typeof loadPage === 'function' &&
      !json.noajaxpage
    ) {
      loadPage(
        (window.history.state && window.history.state.content) ||
        '#content-ajax',
        json.location,
        true,
        true,
      );
    } else {
      window.location.href = json.location;
    }
  }

  /* Recarrega a página atual */
  if (json.reload) {
    if (
      typeof loadPage !== 'undefined' &&
      typeof loadPage === 'function' &&
      !json.noajaxpage
    ) {
      loadPage(
        (window.history.state && window.history.state.content) ||
        '#content-ajax',
        '',
        true,
        true,
      );
    } else {
      window.location.reload();
    }
  }
}

/**
 * Função responsável por realizar as requisições ajax
 *
 * @param {Object} element
 * @param {string} url
 * @param {Object} formData
 * @param {string} method
 * @param {Object} form
 * @param {Boolean} change
 * @param {Object} modal
 *
 * @return {void}
 */
export function vcAjax(element, url, formData, method, form, change, modal) {
  /* Verifica URL */
  if (!url || url === '') {
    alert('URL Inválida para a requisiçao.');

    return;
  }

  /* Variáveis */
  var html = element.html();
  var loadding =
        element.attr('vc-loading') !== undefined
          ? element.attr('vc-loading')
          ? element.attr('vc-loading')
          : 'Aguarde...'
          : change
          ? false
          : html;
  var message;
  var headers = {};

  /* Upload file */
  var enableUpload = element.attr('vc-upload') !== undefined;

  /* Cancelar requisição */
  var ajaxAbort = element
    .parent()
    .parent()
    .parent()
    .find('*[vc-abort]');

  /* Message */
  if (modal) {
    message = modal.find('.modal-body');
  } else {
    if (form.length > 0) {
      message = form.find('#vc-message');

      if (message.length <= 0) {
        message = form
          .parent()
          .parent()
          .parent()
          .find('#vc-message');
      }
    } else if (element.attr('vc-message')) {
      message = $(document).find(element.attr('vc-message'));
    }
  }

  // Verifica se tem formData no element
  if (element.attr('vc-data') !== undefined) {
    var elementData = parseJSON(element.attr('vc-data'));

    if (elementData) {
      for (var key in elementData) {
        if (elementData.hasOwnProperty(key)) {
          formData.append(key, elementData[key]);
        }
      }
    }
  }

  /* Custom _METHOD */
  var _METHOD;

  if (formData.has('_METHOD')) {
    _METHOD = formData.get('_METHOD');

    formData.delete('_METHOD');
  } else {
    _METHOD = method;
  }

  /* CSRF Protect */
  var _csrfToken = '';

  if (formData.has('_csrfToken')) {
    _csrfToken = formData.get('_csrfToken');
    formData.delete('_csrfToken');
  } else {
    _csrfToken = $('meta[name="_csrfToken"]').attr('content') || '';
  }

  if (_csrfToken !== '') {
    headers['X-Csrf-Token'] = _csrfToken;
  }

  /* Headers */
  headers['X-Http-Method-Override'] = _METHOD.toUpperCase();

  if (formData.has('_HEADERS')) {
    var jsonHeader = parseJSON(formData.get('_HEADERS'));

    if (jsonHeader) {
      for (key in jsonHeader) {
        if (jsonHeader.hasOwnProperty(key)) {
          headers[key] = jsonHeader[key];
        }
      }
    }

    formData.delete('_HEADERS');
  }

  /* Ajax */
  var ajaxRequest = $.ajax({
    url: url,
    data: formData,
    dataType: 'json',
    type: method.toUpperCase(),
    enctype: 'multipart/form-data',
    headers: headers,
    cache: false,
    contentType: false,
    processData: false,

    xhr: function () {
      var xhr = $.ajaxSettings.xhr();

      /* Upload progress */
      if (enableUpload) {
        var startTime = new Date().getTime();

        xhr.upload.addEventListener(
          'progress',
          function (e) {
            if (e.lengthComputable && enableUpload) {
              var diffTime = new Date().getTime() - startTime;
              var uploadPercent = parseInt((e.loaded / e.total) * 100);
              var durationTime =
                    ((100 - uploadPercent) * diffTime) / uploadPercent;
              // var calculateTimeFormat = calculateTimeUpload(durationTime);

              if (uploadPercent === 100) {
                if (ajaxAbort !== undefined) {
                  ajaxAbort.fadeOut(0);
                }

                console.log(diffTime, uploadPercent, durationTime);
              }
            }
          },
          false,
        );
      }

      return xhr;
    },

    beforeSend: function () {
      /* Limpa mensagens */
      if (message !== undefined && message.length > 0 && !modal) {
        message.fadeOut(0).html('');
      }

      /* Adiciona mensagem do loadding */
      if (loadding) {
        element.html(loadding);
      }

      /* Desabilita o elemento clicado/modificado */
      element.attr('disabled', true);

      /* Mostra o botão/link para cancelar a requisição */
      if (ajaxAbort !== undefined) {
        ajaxAbort.fadeIn(0);
      }
    },

    success: function (json) {
      window.setTimeout(function () {
        /* Adiciona no localStorage */
        if (json.storage) {
          if (json.storage[0] === 'remove') {
            window.Storage.remove(json.storage[1]);
          } else {
            window.Storage.set(json.storage[0], json.storage[1]);
          }
        }

        /* Percore os id da div preenchendo seus dados */
        if (json.object) {
          element.attr('disabled', false);

          if (typeof json.object === 'object') {
            $.each(json.object, function (key, value) {
              if (modal) {
                modal.find('#' + key).html(value);
              } else {
                if ($(document).find('input[id="' + key + '"]').length > 0) {
                  $(document)
                    .find('input[id="' + key + '"]')
                    .val(value);
                } else {
                  $(document)
                    .find('#' + key)
                    .html(value);
                }
              }
            });

            /* Masks */
            if ($(document).find('*[class*="mask"]').length) {
              initMaskInput();
            }

            /* Select 2 */
            if ($(document).find('*[data-toggle="select2"]').length) {
              initSelect2($(document).find('*[data-toggle="select2"]'));
            }
          }

          /* Inicia plugins caso for a modal */
          if (modal) {
            //
          }
        }

        /* Limpa formulário */
        if (json.clear && form.length > 0) {
          form.trigger('reset');
          form.find('*[data-toggle="select2"]').trigger('change');
        }

        /* Mensagem de retorno ou erro */
        if (json.trigger || json.error || (!json.error && json.message)) {
          showMessage(json, message);
        }

        /* Ações diversas */
        if (json.switch) {
          if (typeof json.switch === 'object') {
            $.each(json.switch, function (key, value) {
              switch (key) {
                /* Hide */
                case 'scrolltop':
                  $('html,body').animate(
                    {
                      scrollTop: $(value).offset().top - 20,
                    },
                    500,
                  );
                  break;

                /* Hide */
                case 'hide':
                  $(value).hide();
                  break;

                /* Show */
                case 'show':
                  $(value).show();
                  break;

                /* Toggle */
                case 'toggle':
                  $(value).toggle();
                  break;

                /* Eval */
                case 'eval':
                  if (typeof value === 'object') {
                    $.each(value, function (k, v) {
                      eval(v);
                    });
                  } else {
                    eval(value);
                  }
                  break;
              }
            });
          }
        }

        /* Redirect & Reload */
        redirectAndReload(json);
      }, 0);
    },

    complete: function () {
      /* Adiciona o html padrão do elemento clicado/modificado */
      if (loadding) {
        element.html(html);
      }

      /* Habilita novamente o elemento clicado/modificado */
      element.attr('disabled', false);

      /* Oculta o botão/link para cancelar a requisição */
      if (ajaxAbort !== undefined) {
        ajaxAbort.fadeOut(0);
      }

      /* Carrega js armazenado */
      if (typeof loadHtmlSuccessCallbacks !== 'undefined') {
        loadHtmlSuccessCallbacks.forEach(function (callback) {
          callback();
        });
      }
    },

    error: function (xhr, exception) {
      var json = parseJSON(xhr.responseText);

      if (json) {
        showMessage(json, message);
        redirectAndReload(json);
      } else {
        var error;

        if (exception === 'timeout') {
          error = 'Limite de tempo esgotado para a requisição.';
        } else if (exception === 'abort') {
          error = 'Sua requisição foi cancelada.';
        } else if (xhr.status === 0) {
          error = 'Sem conexão com a internet! Favor verifique sua conexão.';
        } else if (xhr.status >= 400 && xhr.status <= 499) {
          error =
            '[' + xhr.status + '] Client error! Informe o código ao suporte.';
        } else if (xhr.status >= 500 && xhr.status <= 600) {
          error =
            '[' + xhr.status + '] Server error! Informe o código ao suporte.';
        } else {
          if (xhr.responseText) {
            error = xhr.responseText;
          } else {
            error =
              'Não conseguimos identificar o erro! Favor entre em contato para que possamos verificar.';
          }
        }

        console.log(xhr, exception);

        showMessage({ error: { message: error } }, message);
      }
    },
  });

  /* Aborta requisição */
  $(document).on('click', '*[vc-abort]', function () {
    /* Desativa o upload */
    enableUpload = false;

    /* Reseta formulário */
    if (form.length > 0) {
      form.trigger('reset');
    }

    /* Remove as mensagens caso tenha aparecido */
    if (message !== undefined && message.length > 0) {
      message.html('').fadeOut(0);
    }

    /* Volta os atributos do elemento clicado/modificado */
    element.attr('disabled', false).html(html);

    /* Oculta o botão/link para cancelar a requisição */
    if (ajaxAbort !== undefined) {
      ajaxAbort.fadeOut(0);
    }

    /* Aborta conexão */
    ajaxRequest.abort();
  });
}

/* Carrega o documento */
$(document).ready(function () {
  /* Dispara o request ao realizar uma mudança (change) */
  $(document).on('change', '*[vc-change]', function (event) {
    event.preventDefault();
    event.stopPropagation();

    /* Variáveis */
    var elementChange = $(this);
    var formData = new FormData();
    var json = parseJSON(elementChange.attr('vc-change'));

    /* Caso o json não seja válido, cria. */
    if (!json) {
      json = {};

      Object.assign(json, {
        url: checkElementUrl(elementChange, 'change'),
        method: elementChange.attr('vc-method')
          ? elementChange.attr('vc-method').toUpperCase()
          : 'POST',
        data: undefined,
        name: elementChange.attr('vc-param')
          ? elementChange.attr('vc-param')
          : 'value',
      });
    }

    var method = json.method || 'POST';

    if (json.data !== undefined) {
      elementChange.attr('vc-data', JSON.stringify(json.data));
    }

    /* FormData */
    formData.append(
      json.name || 'value',
      elementChange.val() !== undefined ? elementChange.val() : '',
    );
    formData.append('_METHOD', method);

    vcAjax(elementChange, json.url || '', formData, 'POST', {}, true, false);
  });

  /* Dispara o request ao submeter o formulário (submit) */
  $(document).on('submit', 'form[vc-form]', function (event) {
    /* Desabilita ações default */
    event.preventDefault(event);
    event.stopPropagation(event);

    /* Variávies */
    var elementForm = $(this);
    var formData = new FormData();

    /* Verifica se está desabilitado */
    if (!elementForm.attr('disabled')) {
      /* Verifica se tem mensagem de confirmação */
      if (!checkElementConfirm(elementForm)) {
        return false;
      }

      /* Verifica campos obrigatórios */
      if (!checkFormRequired(elementForm)) {
        return false;
      }

      /* Monta o formData */
      mountFormData(elementForm, formData);

      /* Dispara a requisição */
      vcAjax(
        elementForm,
        elementForm.attr('action') || '',
        formData,
        elementForm.attr('method') || 'POST',
        elementForm,
        false,
        false,
      );
    }
  });

  /* Dispara o request ao clicar (click) */
  $(document).on('click', '*:not(form[vc-form])', function (event) {
    /* Variavéis */
    var elementClicked = $(this);
    var formData = new FormData();

    /* Elementos desabilitados */
    if (elementClicked.attr('disabled')) {
      return false;
    }

    /* Verifica se tem mensagem de confirmação */
    if (!checkElementConfirm($(this))) {
      return false;
    }

    /* REQUEST :: FORM */
    if (
      elementClicked.attr('vc-form') !== undefined &&
      (elementClicked.attr('vc-form') === '' || elementClicked.attr('vc-form'))
    ) {
      event.preventDefault(event);
      event.stopPropagation(event);

      /* Form */
      var form =
            elementClicked.attr('vc-form') &&
            elementClicked.attr('vc-form').length > 0
              ? $('form[name="' + elementClicked.attr('vc-form') + '"]')
              : elementClicked.closest('form');

      /* Método */
      formData.append('_METHOD', form.attr('method') || 'POST');

      /* Verifica se existe o formulário */
      if (form.length <= 0) {
        alert(
          'Formulário com ([name="' +
          elementClicked.attr('vc-form') +
          '"]) não foi encontrado em seu documento html.',
        );

        return false;
      }

      /* Verifica campos obrigatórios */
      if (!checkFormRequired(form)) {
        return false;
      }

      /* Monta o formData */
      mountFormData(form, formData);

      /* Dispara a requisição */
      vcAjax(
        elementClicked,
        form.attr('action') || '',
        formData,
        'POST',
        form,
        false,
        false,
      );
    }

    /* REQUEST :: Verbos HTTP */
    $.each(['get', 'post', 'put', 'delete', 'options', 'ajax'], function (
      index,
      http,
    ) {
      var elementHttp = elementClicked.attr('vc-' + http);

      /* Verifica se pode presseguir */
      if (elementHttp !== undefined && (elementHttp === '' || elementHttp)) {
        event.preventDefault(event);
        event.stopPropagation(event);

        /* Variávies */
        var elementHttpJson = parseJSON(elementHttp);
        var method = http.toUpperCase();

        /* Verifica se não e um json e cria o padrão */
        if (!elementHttpJson) {
          elementHttpJson = {};

          /* Verifica método caso seja ajax padrão */
          if (http === 'ajax') {
            method = elementClicked.attr('vc-method') || 'POST';
          }

          Object.assign(elementHttpJson, {
            url: checkElementUrl(elementClicked, http.toString().toLowerCase()),
            method: method,
            data: undefined,
          });
        }

        /* Se for DELETE */
        if (http === 'delete') {
          var verify = confirm('Essa ação é irreversível.\nDeseja continuar?');

          if (verify === false) {
            return false;
          }
        }

        /* Monta o data se existir */
        if (elementHttpJson.data !== undefined) {
          elementClicked.attr('vc-data', JSON.stringify(elementHttpJson.data));
        }

        /* Método */
        formData.append('_METHOD', elementHttpJson.method || method);

        /* Requisição */
        vcAjax(
          elementClicked,
          elementHttpJson.url,
          formData,
          elementHttpJson.method !== 'GET' ? 'POST' : 'GET',
          {},
          false,
          false,
        );
      }
    });
  });
});

/* Executa apos o carregamento da página */
$(window).on('load', function () {
  /* Dispara o request após carregar a página (after load) */
  $.each($(document).find('*[vc-after-load]'), function (index, element) {
    /* FormData*/
    var formData = new FormData();

    /* Método */
    if (
      $(element).attr('vc-method') !== undefined &&
      $(element).attr('vc-method').length > 2
    ) {
      formData.append('_METHOD', element.attr('vc-method').toUpperCase());
    }

    /* Envia requisição */
    vcAjax(
      $(element),
      checkElementUrl($(element), 'after-load'),
      formData,
      'POST',
      {},
      false,
      false,
    );
  });
});
