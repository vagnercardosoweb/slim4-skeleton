export const parseDomStringMapToProps = (dataset: DOMStringMap) => {
  if (Object.prototype.toString.call(dataset) !== '[object DOMStringMap]') {
    return {};
  }

  return Object.entries(dataset).reduce(
    (obj: Record<string, any>, [key, value]) => {
      if (value !== undefined) {
        try {
          value = JSON.parse(value);
        } catch (e) {}
      }

      obj[key] = value;

      return obj;
    },
    {},
  );
};
