document.addEventListener('DOMContentLoaded', function() {
  localStorage.clear();
});
function previewImageFL(event) {
    var reader = new FileReader();
    reader.onload = function() {
    var preview = document.getElementById('previewFL');
    preview.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }
  
  function previewImageDispo(event) {
    var reader = new FileReader();
    reader.onload = function() {
    var preview = document.getElementById('previewDispo');
    preview.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }
  