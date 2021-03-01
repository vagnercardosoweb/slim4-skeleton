/**
 * Formata número similar ao php
 *
 * @param numero
 * @param decimal
 * @param decimal_separador
 * @param milhar_separador
 *
 * @returns {string|*}
 */

export function number_format(
  numero,
  decimal,
  decimal_separador,
  milhar_separador
) {
  numero = (numero + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+numero) ? 0 : +numero,
    prec = !isFinite(+decimal) ? 0 : Math.abs(decimal),
    sep = typeof milhar_separador === 'undefined' ? ',' : milhar_separador,
    dec = typeof decimal_separador === 'undefined' ? '.' : decimal_separador,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };

  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');

  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }

  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }

  return s.join(dec);
}

/**
 * Retorna apenas número
 *
 * @param evt
 *
 * @returns {boolean}
 */

export function isNumeric(evt) {
  var charCode = evt.which ? evt.which : event.keyCode;

  return !(charCode > 31 && (charCode < 48 || charCode > 57));
}

/**
 * Verifica o máximo de caracteres
 *
 * @param element
 * @param length
 *
 * @returns {string|jQuery}
 */

export function isLength(element, length) {
  if ($(element).val().length >= length) {
    return $(element).val(
      $(element)
        .val()
        .substr(0, length - 1)
    );
  }
}

(function(window) {
  /**
   * Passado pro escopo global do `window` para poder usar
   * o Storage em qualquer lugar
   *
   * @type {
   *    {
   *      set: Window.Storage.set,
   *      setObject: Window.Storage.setObject,
   *      get: Window.Storage.get,
   *      getObject: Window.Storage.
   *      getObject, r
   *      emove: Window.Storage.remove
   *    }
   *  }
   */
  window.MyLocalStorage = {
    set: function(key, value) {
      window.localStorage[key] = value;

      return window.localStorage[key];
    },

    setObject: function(key, value) {
      window.localStorage[key] = JSON.stringify(value);

      return this.getObject(key);
    },

    get: function(key) {
      return window.localStorage[key] || false;
    },

    getObject: function(key) {
      return JSON.parse(window.localStorage[key] || null);
    },

    remove: function(key) {
      window.localStorage.removeItem(key);
    },
  };
})(window);

/**
 * Juntos dois objetos
 *
 * @param object
 * @param source
 *
 * @returns {*}
 */

export function mergeObject(object, source) {
  for (var key in source) {
    if (source.hasOwnProperty(key)) {
      object[key] = source[key];
    }
  }

  return object;
}

/**
 * @param input
 * @param find
 */

export function previewImage(input, find) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function(e) {
      $(find).attr('src', e.target.result);
    };

    reader.readAsDataURL(input.files[0]);
  }
}

if (typeof jQuery !== 'undefined') {
  $(document).on('change', '*[data-preview]', function(event) {
    event.preventDefault();
    previewImage($(this)[0], $(this).data('preview'));
  });
}

/**
 * Calcula o tempo para o upload
 *
 * @param duration
 * @returns {string}
 */

export function calculateTimeUpload(duration) {
  if (!Number.isFinite(duration)) {
    return 'calculando tempo...';
  }

  var seconds = parseInt((duration / 1000) % 60),
    minutes = parseInt((duration / (1000 * 60)) % 60),
    hours = parseInt((duration / (1000 * 60 * 60)) % 24);

  if (hours > 0) {
    return hours + ' horas, ' + minutes + ' minutos e ' + seconds + ' segundos';
  }

  if (minutes > 0) {
    return minutes + ' minutos e ' + seconds + ' segundos';
  }

  if (seconds > 0) {
    return seconds + ' segundos';
  }

  return '-';
}

export function parseJSON(json) {
  if (typeof json !== 'string') {
    json = JSON.stringify(json);
  }

  try {
    json = JSON.parse(json);
  } catch (e) {
    return false;
  }

  if (typeof json === 'object' && json !== null) {
    return json;
  }

  return false;
}
