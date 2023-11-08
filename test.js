const storedMonth = localStorage.getItem('selectedMonth');
const moisActuel = storedMonth ? parseInt(storedMonth) : new Date().getMonth() + 1;
const moisActuelList= document.getElementById('mois');
const rotationSpeedFactor = 0.8; 
var activeButton = null;
var initialX = 0;
var initialRotation = 180; 
var angleRotation = 0;
var rotationIncrement = 30;

moisActuelList.value = (moisActuel < 10 ? '0' : '') + moisActuel;

function CurrentMonthRoue() {
    var currentMonthIndex = moisActuel;
    var currentButtonIndex = (-currentMonthIndex) % 12;
    var degrees = initialRotation + rotationIncrement * currentButtonIndex;
    Roue.style.webkitTransform  = 'rotate(' + degrees + 'deg)';
    Roue.style.transform = 'rotate(' + degrees + 'deg)';
    angleRotation = degrees;
  }
  CurrentMonthRoue();

  function dragMove(event) {
    if (activeButton) {
      var deltaX = event.clientX - initialX;
      var degrees = Math.floor(deltaX / rotationIncrement) * rotationIncrement;
      var rotationSpeed = deltaX * rotationSpeedFactor;
      Roue.style.webkitTransform  = 'rotate(' + degrees + 'deg)';
      Roue.style.transform = 'rotate(' + degrees + 'deg)';
      Roue.style.transition = 'transform ' + Math.abs(rotationSpeed) + 'ms linear';
    }
  }