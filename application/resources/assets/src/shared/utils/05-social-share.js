import { parseJSON } from "./00-functions";

/* Carrega o documento */
$(document).ready(function () {
  /**
   * Função para montar o link de compartilhamento
   *
   * @param {String} type
   * @param {String} link
   * @param {String} text
   * @param {String} hashtags
   * @returns {string}
   */
  var mountShareUrl = function (type, link, text, hashtags) {
    /* Variáveis */
    var twitterVia = $('*meta[property="twitter:site"]').attr('content') || '';
    var twitter = [];
    var mounted = '';

    /* Propriedades */
    link = encodeURIComponent(link);
    text = encodeURIComponent(text);
    hashtags = encodeURIComponent(hashtags);

    switch (type) {
      case 'facebook':
        mounted = 'https://www.facebook.com/sharer/sharer.php?u=' + link;
        break;
      case 'twitter':
        if (text) {
          twitter.push('text=' + text);
        }

        if (link) {
          twitter.push('url=' + link);
        }

        if (hashtags) {
          twitter.push('hashtags=' + hashtags);
        }

        if (twitterVia) {
          twitter.push('via=' + twitterVia.replace('@', ''));
        }

        mounted = 'https://twitter.com/intent/tweet?' + twitter.join('&');
        break;
      case 'whatsapp':
        mounted = 'https://api.whatsapp.com/send?text=' + text + ' ' + link;
        break;
      case 'google':
        mounted = 'https://plus.google.com/share?url=' + link + '&hl=pt-BR';
        break;
      case 'linkedin':
        mounted = `https://www.linkedin.com/shareArticle?url=${link}&title=${text}`;
        break;
    }

    return mounted;
  };

  // data-share="https://www.facebook.com/sharer/sharer.php?u=URL }}"
  // data-share="https://twitter.com/intent/tweet?text=TEXT&url=URL&hashtags=HASHTAG&via=VIA(NOME_PAGE)"
  // data-share="https://plus.google.com/share?url=URL&hl=pt-BR"
  // data-share="https://api.whatsapp.com/send?text=TEXT|URL_ENCODE"

  /* Realiza o compartilhamento para as redes sociais. */
  $(document).on('click', '*[data-share]', function (event) {
    event.preventDefault(event);

    /* Variáveis */
    var url = ($(this).attr('data-share') || $(this).attr('href') || $(this).data('href')) || '';
    var width = 600;
    var height = 600;

    /* Verifica json */
    var json = parseJSON(url);

    if (json) {
      var type = json.type || '';
      var link = json.link || '';
      var text = json.text || '';
      var hashtag = json.hashtag || '';

      url = mountShareUrl(type, link, text, hashtag);
    }

    if (!url || url.match(/^#|javascript/g)) {
      alert('URL de compartilhamento inválida.');

      return;
    }

    var leftPosition, topPosition;
    leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
    topPosition = (window.screen.height / 2) - ((height / 2) + 100);

    window.open(url, 'Window2',
      'status=no,height=' + height + ',width=' + width + ',resizable=yes,left='
      + leftPosition + ',top=' + topPosition + ',screenX=' + leftPosition + ',screenY='
      + topPosition + ',toolbar=no,menubar=no,scrollbars=no,location=no,directories=no');
  });
});
