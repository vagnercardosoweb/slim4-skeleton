type ClipboardCallback = (text: string) => void;

export const clipboard = (text: string, callback?: ClipboardCallback): void => {
  const textArea = document.createElement('textarea');

  textArea.innerText = text;
  document.body.appendChild(textArea);
  textArea.select();
  document.execCommand('copy');
  textArea.remove();

  if (typeof callback === 'function') {
    callback(text);
  }
};
