export function calcCsssRem(value) {
  return `${parseFloat(value / 16)}rem`;
}

export function formatMoney(money) {
  const formatter = Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  return formatter.format(money);
}

export function normalizeMoney(money) {
  return Number(String(money).replace(/[^0-9-]/g, '')) / 100;
}

export function parsedHtmlDataset(dataset) {
  if (Object.prototype.toString.call(dataset) !== '[object DOMStringMap]') {
    return {};
  }

  return Object.entries(dataset).reduce((obj, [key, value]) => {
    try {
      value = JSON.parse(String(value));
    } catch (e) {}

    obj[key] = value;

    return obj;
  }, {});
}

/**
 * @param {String} path
 * @param {RequestInit} init
 *
 * @returns {Promise<any>}
 */
export function fetchAsync(path, init) {
  return new Promise((resolve, reject) => {
    fetch(path, init)
      .then(async response => {
        const body = await response.json();
        body['statusTex'] = response.statusTex;
        body['statusCode'] = response.status;

        if (response.ok === false || response.status >= 400) {
          reject(body);
        } else {
          resolve(body);
        }
      })
      .catch(reject);
  });
}
