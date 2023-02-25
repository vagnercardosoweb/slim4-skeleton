export const fetchAsync = async (path: RequestInfo, init: RequestInit | undefined) => {
  const response = await fetch(path, {
    ...init,
    headers: {
      ...init?.headers,
      accept: 'application/json',
    },
  });

  const responseToJson = await response.json();

  responseToJson['failed'] = !response.ok || response.status >= 400;
  responseToJson['statusCode'] = response.status;
  responseToJson['statusText'] = response.statusText;

  return responseToJson;
};