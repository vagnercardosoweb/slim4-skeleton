//############## TimeRemaining
function getTimeRemaining(endtime) {
  let time = new Date().getTime();
  endtime = Number.isInteger(endtime) ? endtime : Date.parse(endtime);

  let total = (endtime - time) / 100;
  let days = 0;
  let hours = 0;
  let minutes = 0;
  let seconds = 0;

  if (total <= 0) {
    total = 0;
  } else {
    days = Math.floor(total / (24 * 60 * 60));
    hours = Math.floor((total / (60 * 60)) % 24);
    minutes = Math.floor((total / 60) % 60);
    seconds = Math.floor(total % 60);
  }

  return { total, days, hours, minutes, seconds };
}

function countdown(element, callback) {
  let endtime = element.data('countdown');
  let interval = setInterval(function init() {
    let t = getTimeRemaining(endtime);

    t.days = ('0' + t.days).slice(-2);
    t.hours = ('0' + t.hours).slice(-2);
    t.minutes = ('0' + t.minutes).slice(-2);
    t.seconds = ('0' + t.seconds).slice(-2);

    callback(t);

    if (t.total <= 0) {
      clearInterval(interval);
    }
  }, 1000);
}

$(document).ready(function() {});
