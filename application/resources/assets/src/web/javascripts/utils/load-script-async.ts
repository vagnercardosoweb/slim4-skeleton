export const loadScriptAsync = (url: string) => {
  return new Promise((resolve, reject) => {
    const selector = `script[src="${url}"]`;

    if (document.querySelector(selector)) {
      return;
    }

    const script = document.createElement('script');
    script.src = url;

    script.onload = resolve;
    script.onerror = reject;

    document.body.appendChild(script);
  });
};
