<?php declare(strict_types=1);
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: error404.php');
    exit(); 
  }

$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');


if (isset($_GET['id_recettes'])) {
    $id_recette = $_GET['id_recettes'];

    // Récupérez les détails de la recette en fonction de l'ID de la recette
    $query = "SELECT * FROM recettes WHERE id_recettes = :id_recette";
    $req_recettes = $pdo->prepare($query);
    $req_recettes->bindParam(':id_recette', $id_recette, PDO::PARAM_INT);
    $req_recettes->execute();

    // Vérifiez si la recette existe
    if ($req_recettes->rowCount() > 0) {
        $recette = $req_recettes->fetch(PDO::FETCH_ASSOC);

        // Maintenant, vous pouvez utiliser les données de la recette pour pré-remplir le formulaire de modification
        $nom = $recette['nom'];
        $duree = $recette['duree'];
        $ingredients = $recette['ingredients'];
        $etapes = $recette['etapes'];
        $img = $recette['img'];

        // Le formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Gestion du téléchargement de la nouvelle image
            if (isset($_FILES['nouvelle_img']) && $_FILES['nouvelle_img']['error'] === UPLOAD_ERR_OK) {
                // Vérification du poids du fichier
                $fileSize = $_FILES['nouvelle_img']['size'];
                $maxFileSize = 5 * 1024 * 1024; // 5 Mo en octets

                if ($fileSize > $maxFileSize) {
                    $error_img = 'Insérer une photo de 5 Mo maximum';
                } else {
                    // Téléchargement de la nouvelle image réussi
                    $new_img = basename($_FILES['nouvelle_img']['name']);

                    // Vérification du type de fichier
                    $file_info = getimagesize($_FILES['nouvelle_img']['tmp_name']);

                    if ($file_info === false) {
                        $error_img = 'Seules les photos au format JPG, JPEG, PNG et GIF sont autorisées';
                    } elseif (!in_array($file_info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
                        $error_img = 'Seules les photos au format JPG, JPEG, PNG et GIF sont autorisées';
                    } else {
                        // Supprimer l'ancienne image s'il y en a une
                        if (!empty($img)) {
                            unlink("ressources/img_recette/$img");
                        }

                        // Déplacez le nouveau fichier image vers le dossier de destination
                        $destination = "ressources/img_recette/$new_img";
                        if (move_uploaded_file($_FILES['nouvelle_img']['tmp_name'], $destination)) {
                            // Le déplacement du fichier a réussi, mettez à jour la base de données
                            $nom = htmlspecialchars($_POST['nom'], ENT_QUOTES, 'UTF-8');
                            $duree = htmlspecialchars($_POST['duree'], ENT_QUOTES, 'UTF-8');
                            $ingredients = htmlspecialchars($_POST['ingredients'], ENT_QUOTES, 'UTF-8');
                            $etapes = htmlspecialchars($_POST['etapes'], ENT_QUOTES, 'UTF-8');

                            $update_query = "UPDATE recettes SET nom = :nom, duree = :duree, ingredients = :ingredients, etapes = :etapes, img = :new_img WHERE id_recettes = :id_recette";
                            $update_req_recettes = $pdo->prepare($update_query);
                            $update_req_recettes->bindParam(':nom', $nom, PDO::PARAM_STR);
                            $update_req_recettes->bindParam(':duree', $duree, PDO::PARAM_INT);
                            $update_req_recettes->bindParam(':ingredients', $ingredients, PDO::PARAM_STR);
                            $update_req_recettes->bindParam(':etapes', $etapes, PDO::PARAM_STR);
                            $update_req_recettes->bindParam(':new_img', $new_img, PDO::PARAM_STR);
                            $update_req_recettes->bindParam(':id_recette', $id_recette, PDO::PARAM_INT);

                            if ($update_req_recettes->execute()) {
                                $success = 'Recette mise à jour avec succès';
                            } else {
                                $error_img = 'Une erreur s\'est produite lors de la mise à jour de la recette.';
                            }
                        } else {
                            $error_img = 'Erreur lors du déplacement du fichier vers le dossier de destination.';
                        }
                    }
                }
            } else {
                // L'utilisateur n'a pas téléchargé de nouvelle image, mettez à jour les autres champs de la recette
                if (strlen($nom) < 3 || strlen($nom) > 100) {
                    $error_nom = 'Le titre doit comporter entre 3 et 100 caractères';
                } elseif ($duree <= 0) {
                    $error_duree = 'La durée doit être supérieure à zéro.';
                } else {
                    $update_query = "UPDATE recettes SET nom = :nom, duree = :duree, ingredients = :ingredients, etapes = :etapes WHERE id_recettes = :id_recette";
                    $update_req_recettes = $pdo->prepare($update_query);
                    $update_req_recettes->bindParam(':nom', $nom, PDO::PARAM_STR);
                    $update_req_recettes->bindParam(':duree', $duree, PDO::PARAM_INT);
                    $update_req_recettes->bindParam(':ingredients', $ingredients, PDO::PARAM_STR);
                    $update_req_recettes->bindParam(':etapes', $etapes, PDO::PARAM_STR);
                    $update_req_recettes->bindParam(':id_recette', $id_recette, PDO::PARAM_INT);

                    if ($update_req_recettes->execute()) {
                        $success = 'Recette mise à jour avec succès';
                    } else {
                        $error_img = 'Une erreur s\'est produite lors de la mise à jour de la recette.';
                    }
                }
            }
        }
    }
} else {
    // Aucun ID de recette spécifié dans l'URL
    $error_img = 'Aucun ID de recette spécifié dans l\'URL.';
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
      <input type="file" id="image" name="nouvelle_img" accept="image/*" onchange="previewImage(event)">

     
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

      <button id="submitRecette" type="submit">Modifier la recette</button>
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