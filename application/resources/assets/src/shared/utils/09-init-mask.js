/**
 * Inicia as máscara para os forms
 *
 * https://igorescobar.github.io/jQuery-Mask-Plugin/
 */

export function initMaskInput() {
  $('.maskYear').mask('0000', { placeholder: '____' });
  $('.maskTime').mask('00:00', { placeholder: '__:__' });
  $('.maskDate').mask('00/00/0000', { placeholder: '__/__/____' });

  $('.maskCardCvv').mask('0009', { placeholder: '____' });
  $('.maskCardExpiration').mask('00/0000', { placeholder: '__/____' });
  $('.maskCreditCard').mask('0000 0000 0000 0000', {
    placeholder: '____ ____ ____ ____',
  });

  $('.maskDateTime').mask('00/00/0000 00:00', {
    placeholder: '__/__/____ __:__',
  });

  $('.maskMoney').mask('#.##0,00', { reverse: true });
  $('.maskFloat').mask('#.##0,00', { reverse: true });
  $('.maskNumber').mask('#00', { reverse: true });

  $('.maskCpf').mask('000.000.000-00', {
    reverse: true,
    placeholder: '___.___.___-__',
  });

  $('.maskRg').mask('00.000.000-0', {
    reverse: true,
    placeholder: '__.___.___-_',
  });

  $('.maskCnpj').mask('00.000.000/0000-00', {
    reverse: true,
    placeholder: '__.___.___/____-__',
  });

  $('.maskCep').mask('00000-000', {
    onKeyPress: function(value, e, field, options) {
      var masks = ['00000-000', '0-00-00-00'];
      var mask = value.length > 7 ? masks[0] : masks[1];

      $(field[0]).mask(mask, options);
    },

    placeholder: '_____-___',
  });

  const MaskCpfAndCnpj = val =>
    val.replace(/\D/g, '').length > 11
      ? '00.000.000/0000-00'
      : '000.000.000-009';

  $('.maskCpfAndCnpj').mask(MaskCpfAndCnpj, {
    onKeyPress: function(value, e, field, options) {
      field.mask(MaskCpfAndCnpj(value), options);
    },
  });

  /**
   * @return {string}
   */
  var SPMaskBehavior = function(val) {
      return val.replace(/\D/g, '').length === 11
        ? '(00) 00000-0000'
        : '(00) 0000-00009';
    },
    spOptions = {
      onKeyPress: function(val, e, field, options) {
        field.mask(SPMaskBehavior.apply({}, arguments), options);
      },

      placeholder: '(__) 9____-____',
    };

  $('.maskPhone').mask(SPMaskBehavior, spOptions);
  $('.maskTelephone').mask('(00) 0000-0000', { placeholder: '(__) ____-____' });
}

window.initMaskInput = initMaskInput;

/* Carrega o documento */
$(document).ready(function() {
  /* INIT :: Mask Input */
  if (
    typeof onLoadHtmlSuccess !== 'undefined' &&
    typeof onLoadHtmlSuccess === 'function'
  ) {
    onLoadHtmlSuccess(function() {
      initMaskInput();
    });
  } else {
    initMaskInput();
  }
});
