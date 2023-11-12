export { pxToRem } from './px-to-rem';
export { formattedMoney } from './formatted-money';
export { normalizeMoney } from './normalize-money';
export { formattedDate } from './formatted-date';
export { bytesToSize } from './bytes-to-size';
export { parseJson } from './parse-json';
export { unmask, maskValue, maskCpf } from './mask-value';
export { sleep } from './sleep';
export { clipboard } from './clipboard';
export { strLimit } from './str-limit';
export { ucFirst } from './uc-first';
export { createQueryParams } from './create-query-params';
export { uniqIdentifier } from './uniq-identifier';
export { parseDatasetToObject } from './parse-dataset-to-obj';
export { fetchAsync } from './fetch-async';

export const onlyNumber = (value: string) => {
  return value.replace(/[^\d]/g, '');
};
