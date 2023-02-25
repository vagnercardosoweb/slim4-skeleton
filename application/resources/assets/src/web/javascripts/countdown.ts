const padStart = (value: number) => {
  return value.toString().padStart(2, '0');
};

function countdown(timestamp: number) {
  const countDate = new Date(timestamp * 1000).getTime();

  const now = new Date().getTime();
  const gap = countDate - now;

  const second = 1000;
  const minute = second * 60;
  const hour = minute * 60;
  const day = hour * 24;

  const days = Math.floor(gap / day);
  const hours = Math.floor((gap % hour) / hour);
  const minutes = Math.floor((gap % hour) / minute);
  const seconds = Math.floor((gap % minute) / second);

  document.getElementById('day')!.innerText = padStart(days);
  document.getElementById('hours')!.innerText = padStart(hours);
  document.getElementById('minutes')!.innerText = padStart(minutes);
  document.getElementById('seconds')!.innerText = padStart(seconds);
}

window.addEventListener('DOMContentLoaded', () => {
  const el = document.querySelector<HTMLDivElement>('.page-plans--countdown');
  if (!el?.dataset.timestamp) {
    return;
  }
  setInterval(() => {
    countdown(Number(el.dataset.timestamp));
  }, 1000);
});
