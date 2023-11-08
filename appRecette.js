const recettes = document.querySelectorAll('.recette');

function afficherRecetteA(element) {
  var show = element.querySelector('.recetteA');
  if (show.style.display === 'block') {
    show.style.display = 'none'; 
    setTimeout(afficherCartes, 1000);
  } else {
    show.style.display = 'block';  
  }
}
//previsualiser une image editeur
function previewImage(event) {
  var reader = new FileReader();
  reader.onload = function() {
  var preview = document.getElementById('preview');
  preview.src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}

document.addEventListener('DOMContentLoaded', function () {
  let index = 0;
  function afficherCartes() {
      if (index < recettes.length) {
          recettes[index].style.display = 'block'; 
          recettes[index].style.opacity = '1'; 
          index++;
          setTimeout(afficherCartes, 100); 
      }
  }
  
  afficherCartes();
});

function myCategories() {
  var liste = document.getElementById("listeC");

  if (liste.style.display === "none" || liste.style.display === "") {
    liste.style.display = "block";
  } else {
    liste.style.display = "none";
  }
}



document.addEventListener('DOMContentLoaded', function() {
  localStorage.clear();
});