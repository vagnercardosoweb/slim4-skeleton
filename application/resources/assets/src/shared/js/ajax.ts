import { parseJson } from './utils';

function checkFormRequired(form: JQuery<HTMLFormElement>) {
  let totalErrors = 0;

  form.find<HTMLInputElement>(':input[required]').each(function (_, element) {
    let value = $(element).val() || false;
    let message = '';

    if ($(element).prop('tagName').toLowerCase() === 'textarena' && !value) {
      value = $(element).html();
    }

    // Removes error classes
    if ($(element).hasClass('is-invalid')) {
      $(element)
        .removeClass('is-invalid')
        .parent()
        .find('.invalid-feedback')
        .remove();
    }

    // Add error classes
    if (!value && value !== '0') {
      totalErrors++;

      $(element).addClass('is-invalid');

      message = element.getAttribute('required') || 'Campo obrigatório.';

      if (message.trim()) {
        $(element).after(`<div class="invalid-feedback">${message}</div>`);
      }
    }
  });

  if (totalErrors > 0) {
    form.find('.is-invalid:first').focus();
    totalErrors = 0;

    return false;
  }

  return true;
}

type ShowMessage = {
  element: JQuery<HTMLDivElement>;
  message: string;
  type: 'danger' | 'warning' | 'info' | 'success';
};

const showMessage = ({ element, message, type = 'danger' }: ShowMessage) => {
  if (element.length === 0) {
    return false;
  }

  let html = `<div class="alert alert-${type}">${message}</div>`;

  element.html(html).fadeIn(0);
};

const hiddenMessage = (element: JQuery) => {
  if (element.length === 0) {
    return false;
  }

  element.fadeOut(0).empty();
};

const request = (
  elementClicked: JQuery<any>,
  elementForm: JQuery<HTMLFormElement>,
) => {
  let method = elementForm.attr('method')!.toUpperCase();
  let elementAjaxMessage = $(elementForm.find<HTMLDivElement>('#ajax-message'));
  let loadingMessageText = elementClicked.attr('ajax-loading');
  let originalElementClickedHtml = elementClicked.html();

  let ajaxRequest = $.ajax({
    url: elementForm.attr('action'),
    data: new FormData(elementForm[0]),
    dataType: 'json',
    type: method,
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
    },

    complete: () => {
      elementClicked.html(originalElementClickedHtml);
      elementClicked.attr('disabled', 'false');
    },

    error: xhr => {
      let responseToJson = parseJson(xhr.responseText);

      if (!responseToJson) {
        responseToJson = {
          error: {
            typeClass: 'danger',
            message:
              'Não conseguimos identificar o erro! Favor entre em contato para que possamos verificar.',
          },
        };
      }

      showMessage({
        type: responseToJson.error.typeClass ?? 'danger',
        element: elementAjaxMessage,
        message: responseToJson.error.message,
      });
    },
  });

  $(document).on('click', '[ajax-abort]', function () {
    ajaxRequest.abort();
  });
};

$(document).on('click', '[ajax-form]:not(form[ajax-form])', event => {
  let elementClicked = $(event.currentTarget);

  if (elementClicked.attr('disabled')) {
    return false;
  }

  let elementForm: JQuery<HTMLFormElement> = $(elementClicked.closest('form'));

  if (elementForm.length === 0) {
    throw new Error('Parent form not exists.');
  }

  if (!checkFormRequired(elementForm)) {
    return false;
  }

  request(elementClicked, elementForm);

  return event;
});
