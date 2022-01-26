import { parseJson } from './parse-json';

export const parseDatasetToObject = (dataset: DOMStringMap) => {
  if (Object.prototype.toString.call(dataset) !== '[object DOMStringMap]') {
    return {};
  }

  return Object.entries(dataset).reduce((accumulator: any, [key, value]) => {
    accumulator[key] = null;

    if (value) {
      try {
        accumulator[key] = parseJson(value);
      } catch (e) {}
    }

    return accumulator;
  }, {});
};
