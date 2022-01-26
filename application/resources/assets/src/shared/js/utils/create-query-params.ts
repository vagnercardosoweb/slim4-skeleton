export const createQueryParams = (
  params: Record<string, any>,
  mapperParams: Record<string, any> = {},
): URLSearchParams => {
  const urlSearchParams = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value) {
      let parseKey = key;

      if (key in mapperParams) {
        parseKey = mapperParams[key];
      }

      urlSearchParams.append(parseKey, `${value}`);
    }
  });

  return urlSearchParams;
};
