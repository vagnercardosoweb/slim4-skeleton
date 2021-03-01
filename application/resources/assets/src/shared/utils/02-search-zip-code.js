import { parseJSON } from "./00-functions";

/* Carrega o documento */
$(document).ready(function () {
  function beforeSend(text, increment) {
    $('#cep-logradouro' + increment).val(text);
    $('#cep-complemento' + increment).val(text);
    $('#cep-bairro' + increment).val(text);
    $('#cep-localidade' + increment).val(text);
    $('#cep-uf' + increment).val(text);
    $('#cep-unidade' + increment).val(text);
    $('#cep-ibge' + increment).val(text);
    $('#cep-gia' + increment).val(text);
    $('#cep-latitude' + increment).val('');
    $('#cep-longitude' + increment).val('');
  }

  /* Realiza a pesquisa do dados */
  $(document).on('change', '*[data-cep]', function (event) {
    /* Variáveis */
    var elementValue = $(event.currentTarget).val().replace(/\D/g, '');
    var elementJson = parseJSON($(this).data('cep')) || {};
    elementJson.increment = (elementJson.increment !== undefined ? elementJson.increment : '');

    if (elementValue.length === 8 && /^[0-9]{8}$/.test(elementValue)) {
      beforeSend('Aguarde....', elementJson.increment);

      $.get('/api/util/zipcode/' + elementValue, function (retornoJson) {
        if (!retornoJson.error) {
          $.each(retornoJson, function (index, value) {
            /* Trata lat e long */
            if (['latitude', 'longitude'].includes(index)) {
              value = value.replace(',', '.');
            }

            /* Atribue os valores para os ids */
            $('#cep-' + index + elementJson.increment)
              .val(value)
              .attr('disabled', (
                value !== '' && index !== 'cep'
              ));

            /* Atribue os valores no json */
            elementJson[index] = value;
          });

          /* Autocompleta o estado e cidade */
          if (elementJson['complete-state'] !== undefined && elementJson.uf !== undefined) {
            elementJson.uf = elementJson.uf.toString().toLowerCase();
            var elementState = $(document).find('#' + elementJson['complete-state'] + ' option[data-uf="' + elementJson.uf + '"]');

            if (elementState !== undefined) {
              var elementStateValue = elementState.val().split('::', 2);

              elementState
                .val(elementStateValue[0] + '::' + (elementJson.ibge || null))
                .prop('selected', true)
                .trigger('change');
            }
          }

          /* Monta o mapa */
          if (elementJson['change-position'] !== undefined && (elementJson.latitude !== undefined && elementJson.longitude !== undefined)) {
            initMapChangePosition(elementJson);
          }
        } else {
          beforeSend('', elementJson.increment);

          alert(json.error.message);
        }
      }, 'json').catch(function (error) {
        /* Trata erro */
        beforeSend('', elementJson.increment);
        var response = parseJSON(error.responseText);

        if (response.error.message !== undefined) {
          alert(response.error.message);
        } else {
          alert('Problema ao pesquisar cep.');
        }
      });
    } else {
      beforeSend('', elementJson.increment);
    }
  });
});

/**
 * Inicia o mapa para mudar a lat/lng
 *
 * @param {Object} json
 */

function initMapChangePosition(json) {
  try {
    /* Latitude e longitude */
    var position = new google.maps.LatLng(
      json.latitude.replace(',', '.'),
      json.longitude.replace(',', '.'),
    );

    /* Mapa */
    var map = new google.maps.Map(document.getElementById(json['change-position']), {
      center: position,
      zoom: 18,
    });

    /* Marcador */
    var marker = new google.maps.Marker({
      position: position,
      map: map,
      /*icon: '',*/
      title: '',
      draggable: true,
    });

    /* Evento ao mover o marcador */
    marker.addListener('dragend', function (event) {
      $('#cep-latitude' + json.increment).val(event.latLng.lat());
      $('#cep-longitude' + json.increment).val(event.latLng.lng());
    });

    /* Tamanho do mapa */
    $(document).find('#' + json['change-position']).css('height', (json['change-position-height'] || 350) + 'px');
  } catch (e) {
    $(document)
      .find('#' + json['change-position'])
      .html('<div class="alert alert-danger mb-0"><p>Google Maps not installed in application.</p>');
  }
}
