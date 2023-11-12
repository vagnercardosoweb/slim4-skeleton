export async function fetchAsync<T = any>(url: string, init?: RequestInit): Promise<T> {
  init = init ?? {};
  init.method = init.method ?? 'GET';
  init.headers = { ...init.headers, accept: 'application/json' };
  const response = await fetch(url, init);
  const result = await response.json();
  if (response.status >= 400) {
    console.error('fetchAsync', { url, init, response });
    if (result?.message) throw new Error(result.message);
    throw new Error(`(${init?.method} ${url}) error with statusCode "${response.status}".`);
  }
  return result as T;
}
