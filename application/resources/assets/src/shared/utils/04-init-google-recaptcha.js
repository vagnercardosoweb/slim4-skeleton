/**
 * Inicia as configurações do GOOGLE RECAPTCHA V2
 *
 * https://developers.google.com/recaptcha/docs/display
 */

var initGoogleRecaptcha = function () {
  var sitekey = $('meta[name="recaptcha-sitekey"]').attr('content');
  var recaptchas = $('*[data-toggle="recaptcha"]');

  /* Verifica se existe recaptcha */
  if (recaptchas.length) {
    $.each(recaptchas, function (index, element) {
      $(element).html('');

      if (sitekey !== undefined && sitekey !== '') {
        try {
          grecaptcha.render(element, {
            'sitekey': sitekey,
            'theme': $(element).data('theme') !== undefined ? $(element).data('theme') : 'light',
            'size': $(element).data('size') !== undefined ? $(element).data('size') : 'compact ',
          });
        } catch (e) {
          grecaptcha.reset(index);
        }
      } else {
        $(element).html('<p style="margin: 10px 0;" class="alert alert-danger">Não foi possível carregar o google recaptcha.</p>');
      }
    });
  }
};

/* Carrega o documento */
$(document).ready(function () {
  $.getScript('https://www.google.com/recaptcha/api.js?onload=initGoogleRecaptcha&render=explicit');
});
