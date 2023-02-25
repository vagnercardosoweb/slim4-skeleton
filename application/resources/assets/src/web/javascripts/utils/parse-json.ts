export const parseJson = (json: string | null): Record<string, any> => {
  if (typeof json !== 'string') {
    json = JSON.stringify(json);
  }

  try {
    json = JSON.parse(json);
  } catch (e) {
    return {};
  }

  if (typeof json === 'object' && json !== null) {
    return json;
  }

  return {};
};
