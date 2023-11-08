<?php declare(strict_types=1);
session_start(); 

if (!isset($_SESSION['id'])) {
  header('Location: error404.php');
  exit(); 
}
$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');

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
  $nom = htmlspecialchars($_POST['nom'], ENT_QUOTES, 'UTF-8');
  $duree = htmlspecialchars($_POST['duree'], ENT_QUOTES, 'UTF-8');
  $ingredients = htmlspecialchars($_POST['ingredients'], ENT_QUOTES, 'UTF-8');
  $etapes = htmlspecialchars($_POST['etapes'], ENT_QUOTES, 'UTF-8');

  // Vérification de la longueur du nom de la recette
  if (strlen($nom) < 3 || strlen($nom) > 100) {
    $error_nom = 'Le titre doit comporter entre 3 et 100 caractères';
  } else {
    // Vérification de la durée
    if ($duree > 0) {
      // Vérification de l'existence et de la réussite de l'envoi de l'image
      if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        // Vérification de la taille du fichier
        $fileSize = $_FILES['img']['size'];
        $maxFileSize = 5 * 1024 * 1024; // 5 Mo en octets

        if ($fileSize > $maxFileSize) {
          $error_img = 'Insérer une photo de 5 Mo maximum';
        } else {
          // Vérification de l'extension du fichier
          $extension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);

          if (in_array(strtolower($extension), array('jpg', 'jpeg', 'png', 'gif'))) {
            // C'est une image, vous pouvez traiter le téléchargement
            $filename = basename($_FILES['img']['name']);

            if (move_uploaded_file($_FILES['img']['tmp_name'], "ressources/img_recette/$filename")) {
              // Envoi réussi, enregistrement du nom de l'image en base de données
              $req_recettes = $pdo->prepare('INSERT INTO recettes (nom, ingredients, duree, etapes, id_categories, id_users, img) VALUES (:nom, :ingredients, :duree, :etapes, 1, :id_users, :img)');
              $req_recettes->bindParam(':nom', $nom);
              $req_recettes->bindParam(':duree', $duree);
              $req_recettes->bindParam(':ingredients', $ingredients);
              $req_recettes->bindParam(':etapes', $etapes);
              $req_recettes->bindParam(':id_users', $_SESSION['id']); // Utilisez la variable de session pour l'ID de l'utilisateur
              $req_recettes->bindParam(':img', $filename);
              $req_recettes->execute();

              if ($req_recettes->rowCount() > 0) {
                $success = 'Recette créée avec succès';
                $nom = '';
                $duree = '';
                $ingredients = '';
                $etapes = '';
              } else {
                $error_img = 'Une erreur s\'est produite lors de la création de la recette';
              }
            } else {
              $error_img = 'Envoi échoué';
            }
          } else {
            $error_img = 'Seules les photos au format JPG, JPEG, PNG et GIF sont autorisées';
          }
        }
      } else {
        $error_img = 'Veuillez insérer une photo';
      }
    } else {
      $error_duree = 'La durée doit être un entier supérieur à zéro';
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
  <div class="logo_title">
      <a  href="index.php"><img class= "logo" src="ressources/logo2.png" alt="Image du logo"></a>
      <h1 class="titre">Mes fruits et légumes de saison</h1> 
    </div>
  </header> 
  
  <a href="mesRecettes.php" class="btn_MesRecettes" >Mes recettes</a>
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