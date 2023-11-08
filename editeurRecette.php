<?php declare(strict_types=1);
session_start(); 

if (!isset($_SESSION['id'])) {
  header('Location: error404.php');
  exit(); 
}

$nom = '';
$duree = '';
$ingredients = '';
$etapes = '';
$error_nom = '';
$error_duree = '';
$error_ingredients = '';
$error_etapes = '';
$error_img = '';
$success= '';

if (!empty($_POST)) {
  // Vérification des données du formulaire
  $nom = $_POST['nom'];
  $duree = $_POST['duree'];
  $ingredients = $_POST['ingredients'];
  $etapes = $_POST['etapes'];

  // Vérification de la longueur du nom de la recette
  if (strlen($nom) < 3 || strlen($nom) > 100) {
    $error_nom = 'Le titre doit comporter entre 3 et 100 caractères';
  } else {
      // Vérification de la durée
      if ($duree > 0) {
          // Vérification de l'existence et de la réussite de l'envoi de l'image
          if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
              // Vérification du type de fichier
              $file_info = getimagesize($_FILES['img']['tmp_name']);

              if ($file_info === false) {
                  $error_img = 'Le fichier n\'est pas une image valide.';
              } else {
                  // Vérification de l'extension du fichier
                  $extension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);

                  if (!in_array(strtolower($extension), array('jpg', 'jpeg', 'png', 'gif'))) {
                      $error_img = 'Seules les images avec les extensions JPG, JPEG, PNG et GIF sont autorisées.';
                  } else {
                      // Téléchargement de l'image réussi
                      $filename = basename($_FILES['img']['name']);

                      if (move_uploaded_file($_FILES['img']['tmp_name'], "ressources/img_recette/$filename")) {
                          // Envoi réussi, enregistrement du nom de l'image en base de données
                          $pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');
                          $req_recettes = $pdo->prepare('INSERT INTO recettes (nom, ingredients, duree, etapes, id_categories, id_users, img) VALUES (:nom, :ingredients, :duree, :etapes, 1, :id_users, :img)');
                          $req_recettes->bindParam(':nom', $nom);
                          $req_recettes->bindParam(':duree', $duree);
                          $req_recettes->bindParam(':ingredients', $ingredients);
                          $req_recettes->bindParam(':etapes', $etapes);
                          $req_recettes->bindParam(':id_users', $_SESSION['id']); // Utilisez la variable de session pour l'ID de l'utilisateur
                          $req_recettes->bindParam(':img', $filename);
                          $req_recettes->execute();

                          if ($req_recettes->rowCount() > 0) {
                              $success = 'Recette modifiée avec succès';
                              // Réinitialisation des valeurs des champs
                              $nom = '';
                              $duree = '';
                              $ingredients = '';
                              $etapes = '';
                          } else {
                              $error_img = 'Une erreur s\'est produite lors de la création de la recette.';
                          }
                      } else {
                          $error_img = 'Envoi échoué.';
                      }
                  }
              }
          } else {
              $error_img = 'Insérer une photo (5Mo max)';
          }
      } else {
          $error_duree = 'La durée doit être supérieure à zéro.';
      }
  }
}
?>
<!DOCTYPE html >
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="editeur recette fruis et légumes">
    <title>Editeur recette</title>
    <link href="https://fonts.googleapis.com/css2?family=Spicy+Rice&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Marck+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Paprika&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style_editeurRecette.css">
</head>
  <header>
  <div class="box-login">
    <?php if (isset($_SESSION['pseudo'])): ?>
        <h2>Bienvenue <?php echo $_SESSION['pseudo']; ?> (ID: <?php echo $_SESSION['id']; ?>)</h2>
    <?php else: ?>
        <h2>non connecté</h2>
    <?php endif; ?>
</div>
  <div class="logo_title">
      <a  href="index.php"><img class= "logo" src="ressources/logo2.png" alt="Image du logo"></a>
      <h1 class="titre">Mes fruits et légumes de saison</h1> 
    </div>
  </header> 
  
 
  <div class="formRecette">
    <form action="#" method="post" enctype="multipart/form-data">
      <label for="nom">Nom de la recette:</label>
      <input type="text" id="nom" name="nom" value="<?php echo $nom; ?>" autofocus="autofocus" required>
    
      <?php if (!empty($error_nom)) { ?>
        <div class="error"><?php echo $error_nom; ?></div>
      <?php } ?>

      <label for="image">Insérer une photo</label>
      <input type="file" id="image" name="img" accept="image/*" onchange="previewImage(event)">
     
      <?php if (!empty($error_img)) { ?>
        <div class="error"><?php echo $error_img; ?></div>
      <?php } ?>
      
      <img id="preview" class="preview-image" src="#" alt="">

      <label for="duree">Durée (en minutes):</label>
      <input type="number" id="duree" name="duree" min="1" value="<?php echo $duree; ?>" required>
      <?php if (!empty($error_duree)) { ?>
        <div class="error"><?php echo $error_duree; ?></div>
      <?php } ?>
      
      <label for="ingredients">Ingrédients:</label>
      <textarea id="ingredients" name="ingredients" placeholder="ex: -100g de noisettes" required><?php echo $ingredients; ?></textarea>
      
      <label for="etapes">Etapes de préparation:</label>
      <textarea id="etapes" name="etapes" placeholder="ex: 1) Faites torréfier les noisettes environ 15 minutes au four à 160°C." required><?php echo $etapes; ?></textarea>

      <button id="submitRecette" type="submit">Créer la recette</button>
    </form>
    
    </div>

    <?php if (!empty($success)) { ?>
        <div class="success"><?php echo $success; ?></div>
      <?php } ?>

      <footer class="footerDesktop">
      <h2>Aymeric LABEIS copyright 2023</h2>
      </footer>
  <footer class="footerMobil">
      <div class="footerL">
        <a href="index.php">Accueil</a>
      </div>
      <div class="footerR">
        <a href="Recettes.php">Recettes</a>
      </div>
  </footer>
  <script src="appRecette.js"></script>
</body>
</html>