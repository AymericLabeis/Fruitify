const Roue = document.querySelector('.RoueSelect');
const buttonsRoue = document.querySelectorAll('.buttonR');
const cartes = document.querySelectorAll('.carte');
const storedMonth = localStorage.getItem('selectedMonth');
const moisActuel = storedMonth ? parseInt(storedMonth) : new Date().getMonth() + 1;
const moisActuelList= document.getElementById('mois');
const rotationSpeedFactor = 0.8; 
const postMonth = document.querySelector('.moisSelectionne');
var activeButton = null;
var initialX = 0;
var initialRotation = 180; 
var angleRotation = 0;
var rotationIncrement = 30;

// Mettre à jour la liste déroulante avec le mois actuel
moisActuelList.value = (moisActuel < 10 ? '0' : '') + moisActuel;
console.log('Index list', moisActuelList.value);

function rotateRoue(degrees) {
  angleRotation += degrees;
  Roue.style.transform = `rotate(${angleRotation}deg)`;
  // Augmenter ou diminuer la valeur de l'index de la liste déroulante d'une unité
  if (degrees > 0) {
    moisActuelList.selectedIndex = (moisActuelList.selectedIndex - 1 + moisActuelList.length) % moisActuelList.length;
  } else {
    moisActuelList.selectedIndex = (moisActuelList.selectedIndex + 1 + moisActuelList.length) % moisActuelList.length;
  }
  // Stocker la valeur sélectionnée dans le Local Storage
  localStorage.setItem('selectedMonth', moisActuelList.value);
}

// Position de la roue automatique en fonction du mois actuel
function CurrentMonthRoue() {
  var currentMonthIndex = moisActuel;
  console.log('Index mois en cours', currentMonthIndex);
  var currentButtonIndex = (-currentMonthIndex) % 12;
  //console.log('button index', currentButtonIndex);

  // Calculer l'angle de rotation pour placer le bouton en bas
  var degrees = initialRotation + rotationIncrement * currentButtonIndex;
  Roue.style.transform = 'rotate(' + degrees + 'deg)';
  angleRotation = degrees;
  console.log('rotation roue', angleRotation);
}
CurrentMonthRoue();

// Détecter l'envoi du formulaire et stocker le mois sélectionné dans le Local Storage
moisActuelList.addEventListener('change', function () {
  var selectedMonth = parseInt(moisActuelList.value);
  localStorage.setItem('selectedMonth', selectedMonth);
  bottomButton.classList.remove('agrandir-texte')
});

//Mouvement de la roue de selection       
function dragStart(event) {
  activeButton = event.target;
  initialX = event.clientX;
  
  document.addEventListener('mousemove', dragMove);
  document.addEventListener('mouseup', dragEnd);
}
function dragMove(event) {
  if (activeButton) {
    var deltaX = event.clientX - initialX;
    
    var degrees = Math.floor(deltaX / rotationIncrement) * rotationIncrement;

    // Calculer la vitesse de rotation basée sur le mouvement de la souris
    var rotationSpeed = deltaX * rotationSpeedFactor;

    // Appliquer la rotation avec la vitesse
    Roue.style.webkitTransform  = 'rotate(' + degrees + 'deg)';
    Roue.style.transform = 'rotate(' + degrees + 'deg)';
    Roue.style.transition = 'transform ' + Math.abs(rotationSpeed) + 'ms linear';
  }
}

// Fonction pour gérer la fin du glisser-déposer
function dragEnd() {
  if (activeButton) {
    var bottomButton = null;
    var bottomButtonY = -Infinity;
    // Loop through each button in the buttonsRoue array
    buttonsRoue.forEach(function(button) {
      var rect = button.getBoundingClientRect();
      var buttonY = rect.top + rect.height;

      if (buttonY > bottomButtonY) {
        bottomButton = button;
        bottomButtonY = buttonY;
      }
    });

    if (bottomButton) { 
      //Roue.style.backgroundColor = 'red';
      const monthRoue = bottomButton.value;
      console.log('Index Roue mois', monthRoue);
      document.getElementById('mois').value = monthRoue;
      localStorage.setItem('selectedMonth', monthRoue, );
      bottomButton.classList.add('agrandir-texte')
    }
  }
  activeButton = null;
  document.removeEventListener('mousemove', dragMove);
  document.removeEventListener('mouseup', dragEnd);
}

// affichage des cartes
let carteRetournee = false;
let carteA;
let verouillage = false;

cartes.forEach(carte => {
    carte.addEventListener('click', retourneCarte)
})

function retourneCarte(){
    if(verouillage)return; 
        this.childNodes[1].classList.toggle('active');
    if(!carteRetournee){
  carteRetournee = true;
        carteA = this;
        return;
    }
  carteRetournee = false;  
}

document.addEventListener('DOMContentLoaded', function () {
  let index = 0;
  function afficherCartes() {
      if (index < cartes.length) {
          cartes[index].style.display = 'block'; // Afficher la carte
          cartes[index].style.opacity = '1'; // Afficher la carte en ajustant l'opacité
          index++;
          setTimeout(afficherCartes, 50); // Afficher la prochaine carte après 2 secondes
      }
  }
  
  afficherCartes();
});

function myAccount() {
  var liste = document.getElementById("compte");

  if (liste.style.display === "none" || liste.style.display === "") {
    liste.style.display = "block";
  } else {
    liste.style.display = "none";
  }
}

//clear localStorage
document.addEventListener('DOMContentLoaded', function() {
  var deleteLS = document.getElementById('InitialMonth');
  deleteLS.addEventListener('click', function(event) {
  event.preventDefault();
  localStorage.clear();
  window.location.href = "index.php";
  });
});








