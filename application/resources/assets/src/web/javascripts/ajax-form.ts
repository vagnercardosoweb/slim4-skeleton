import { parseJson } from './utils';
import { closeOpenedModals, openModalById } from './modal';

const checkFormRequired = (form: JQuery<HTMLFormElement>) => {
  let totalErrors = 0;

  form.find<HTMLInputElement>(':input').each(function (_, element) {
    const minValue = $(element).prop('minLength');
    const hasRequired = $(element).prop('required');
    const inputType = $(element).prop('type').toLowerCase();

    if (!hasRequired && (!minValue || minValue < 0)) return;

    let value = $(element).val() as string;
    let message = '';

    const tagName = $(element).prop('tagName').toLowerCase();
    if (tagName === 'textarea' && !value?.trim()) value = $(element).html();

    if ($(element).hasClass('is-invalid')) {
      $(element).removeClass('is-invalid').parent().find('.invalid-feedback').remove();
    }

    if (hasRequired && value !== '0' && !value.trim()) {
      message = element.getAttribute('required') || 'Campo obrigatório.';
    }

    if (minValue && minValue > 0 && value && String(value).length < minValue) {
      message = `Valor precisa ter no minímo de ${minValue} caracteres.`;
    }

    if (inputType === 'email' && value.length > 0 && !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(value)) {
      message = 'O e-mail precisa ter um formato válido.';
    }

    if (message.trim()) {
      totalErrors++;
      $(element).addClass('is-invalid');
      $(element).after(`<div class="invalid-feedback">${message}</div>`);
    }
  });

  if (totalErrors > 0) {
    form.find('.is-invalid:first').focus();
    totalErrors = 0;
    return false;
  }

  return true;
};

type ShowMessage = {
  element: JQuery<HTMLDivElement>;
  message: string;
  type: 'danger' | 'warning' | 'info' | 'success';
};

const hiddenMessage = (element: JQuery) => {
  if (element.length === 0) return false;
  element.fadeOut(0).empty();
};

const showMessage = ({ element, message, type = 'danger' }: ShowMessage) => {
  if (element.length === 0) return;
  let html = `<div class="alert alert-${type}">${message}</div>`;
  element.html(html).fadeIn(0);
  if (element.attr('ajax-message-hidden')?.toString() !== undefined) {
    setTimeout(() => {
      hiddenMessage(element);
    }, 5 * 1000);
  }
};

const ajaxRequest = (elementClicked: JQuery<any>, elementForm: JQuery<HTMLFormElement>) => {
  const url = elementForm.attr('action');
  const method = elementForm.attr('method')!.toUpperCase();

  let elementAjaxMessage = $(elementForm.find<HTMLDivElement>('.ajax-message'));
  let loadingMessageText = elementClicked.attr('ajax-loading');
  let originalElementClickedHtml = elementClicked.html();

  if (typeof loadingMessageText !== 'undefined' && !loadingMessageText?.trim()) {
    loadingMessageText = 'Aguarde...';
  }

  return new Promise((resolve, reject) => {
    const ajaxRequest = $.ajax({
      url,
      data: new FormData(elementForm[0]),
      dataType: 'json',
      method: ['PUT', 'POST', 'PATCH'].includes(method) ? 'POST' : method,
      enctype: 'multipart/form-data',
      headers: {
        'X-Http-Method-Override': method,
        'X-Requested-With': 'XMLHttpRequest',
      },
      cache: false,
      contentType: false,
      processData: false,

      beforeSend: () => {
        elementClicked.attr('disabled', 'true');
        hiddenMessage(elementAjaxMessage);

        if (loadingMessageText && loadingMessageText.trim()) {
          elementClicked.html(loadingMessageText);
        }
      },

      success: response => {
        if (response.message) {
          showMessage({
            type: 'success',
            element: elementAjaxMessage,
            message: response.message,
          });
        }

        if (response['redirectTo']) {
          window.setTimeout(() => {
            window.location.href = response['redirectTo'];
          }, 500);
        }

        if (response['resetForm']) {
          elementForm.trigger('reset');
        }

        if (response['reloadPage']) {
          window.setTimeout(() => {
            window.location.reload();
          }, 500);
        }

        if (response['openModalId']) openModalById(response['openModalId']);
        if (response['closeOpenedModals']) closeOpenedModals();

        if (Array.isArray(response['removeContentSelectors'])) {
          response['removeContentSelectors'].forEach(s => $(s).remove());
        }

        if (Object.keys(response['addContentAppendSelectors'] ?? {}).length) {
          Object.entries(response['addContentAppendSelectors']).forEach(([selector, html]) => {
            const $contentDivEl = $(selector);
            if (!$contentDivEl) return;
            $contentDivEl.append(`${html}`).animate(
              {
                scrollTop: [...$contentDivEl].reduce((previousValue, currentValue) => {
                  if (currentValue.scrollHeight > 0) previousValue = currentValue.scrollHeight;
                  return previousValue;
                }, 0),
              },
              'slow',
            );
          });
        }

        resolve(response);
      },

      complete: () => {
        elementClicked.html(originalElementClickedHtml);
        elementClicked.removeAttr('disabled');
      },

      error: xhr => {
        let responseToJson = parseJson(xhr.responseText);

        if (!responseToJson) {
          responseToJson = {
            typeClass: 'danger',
            message: 'Não conseguimos identificar o erro! Favor entre em contato para que possamos verificar.',
          };
        }

        showMessage({
          type: responseToJson.typeClass ?? 'danger',
          element: elementAjaxMessage,
          message: responseToJson.message,
        });

        reject(responseToJson);
      },
    });

    $(document).on('click', '[ajax-abort]', function () {
      ajaxRequest.abort();
      reject(new Error('Ajax request aborted'));
    });
  });
};

export const handlerAjaxForm = async (event: any) => {
  event.preventDefault();

  let elementForm: JQuery<HTMLFormElement> | undefined;
  let elementClicked = $(event.target);

  if (elementClicked.prop('tagName') === 'FORM') {
    elementForm = elementClicked;

    if (event.originalEvent?.submitter?.nodeName === 'BUTTON') {
      elementClicked = $(event.originalEvent.submitter);
    }
  } else {
    elementForm = $(elementClicked.closest('form'));
  }

  if (!elementClicked) {
    throw new Error('Element click form not exists.');
  }

  if (!elementForm) {
    throw new Error('Parent form not exists.');
  }

  if (elementClicked.attr('disabled')) {
    return false;
  }

  if (!checkFormRequired(elementForm)) {
    return false;
  }

  await ajaxRequest(elementClicked, elementForm);

  return event;
};

$(() => {
  $(document).on('click', '[ajax-form]:not(form[ajax-form])', handlerAjaxForm);
  $(document).on('submit', 'form[ajax-form]', handlerAjaxForm);
});
