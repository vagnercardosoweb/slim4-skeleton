export const unmask = (value: string): string => {
  // eslint-disable-next-line no-useless-escape
  return value.replace(/[\-\|\(\)\/\.\: ]/gm, '');
};

export const maskCpf = (value: string): string =>
  maskValue(value, '###.###.###-##');

export const maskValue = (value: string, mask: string): string => {
  const unmasked = unmask(value);
  const unmaskedLength = unmasked.length;
  const maskLength = mask.replace(/[^#]/gm, '').length;

  if (unmaskedLength > maskLength) {
    unmasked.slice(unmaskedLength, unmaskedLength - maskLength);
  }

  let maskedValue = '';
  let maskedIndex = 0;
  let unmaskedIndex = unmaskedLength;

  for (let i = 0; i < unmaskedIndex; i += 1) {
    if (mask[i] === '#' && typeof unmasked[maskedIndex] !== 'undefined') {
      maskedValue += unmasked[maskedIndex];
      maskedIndex += 1;
    } else if (typeof mask[i] !== 'undefined') {
      maskedValue += mask[i];
      unmaskedIndex += 1;
    }
  }

  return maskedValue;
};
