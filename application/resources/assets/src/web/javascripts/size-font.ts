const elementHtml = document.querySelector('html');
const elementBtnIncreaseFont = document.getElementById('increase-font') as HTMLInputElement;
const elementBtnDecreaseFont = document.getElementById('decrease-font') as HTMLInputElement;

const minFontSize = 62.5;
const maxFontSizeDesktop = 81.5;
const maxFontSizeMobile = 74.5;
const percentFontChange = 3.2;

const getFontSizeFromStorage = () => {
  return parseFloat(window.localStorage.getItem('font-size') ?? minFontSize.toString());
};

const changeFontSizeHtml = () => {
  const fontSize = getFontSizeFromStorage();
  elementHtml!.style.fontSize = `${fontSize}%`;
};

const setFontSizeToStorage = (fontSize: number) => {
  window.localStorage.setItem('font-size', fontSize.toString());
  changeFontSizeHtml();
};

elementBtnIncreaseFont!.addEventListener('click', function () {
  const actualFontSize = getFontSizeFromStorage() + percentFontChange;

  if (actualFontSize >= maxFontSizeDesktop || (window.innerWidth <= 500 && actualFontSize >= maxFontSizeMobile)) {
    setFontSizeToStorage(maxFontSizeDesktop);

    return;
  }

  setFontSizeToStorage(actualFontSize);
});

elementBtnDecreaseFont!.addEventListener('click', function () {
  const actualFontSize = getFontSizeFromStorage() - percentFontChange;
  setFontSizeToStorage(actualFontSize < minFontSize ? minFontSize : actualFontSize);
});

document.addEventListener('DOMContentLoaded', changeFontSizeHtml);
