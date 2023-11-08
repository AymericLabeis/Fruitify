<?php declare(strict_types=1);
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');

if (!isset($_SESSION['id'])) {
  header('Location: error404.php');
  exit();
}

$libelle = "";
$prix = "";
$vitamines = "";
$mineraux = "";
$type = "";
$kilo_piece = "";
$error_img = "";
$errorMonth = "";
$error_libelle = "";
$error_prix = "";
$ids_mois = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Récupération des données du formulaire
  $libelle = htmlspecialchars($_POST['libelle'], ENT_QUOTES, 'UTF-8');
  $type = htmlspecialchars($_POST['type'], ENT_QUOTES, 'UTF-8');
  $prix = floatval($_POST['prix']); 
  $kilo_piece = htmlspecialchars($_POST['kilo_piece']); 
  $vitamines = htmlspecialchars($_POST['vitamines'], ENT_QUOTES, 'UTF-8');
  $mineraux = htmlspecialchars($_POST['mineraux'], ENT_QUOTES, 'UTF-8');
  $id_users = $_SESSION['id'];
  
  // Vérification du libellé
if (strlen($libelle) < 2 || strlen($libelle) > 20) {
  $error_libelle = 'Le titre doit comporter entre 2 et 20 caractères.';
} else {
  if ($prix <= 0) {
    $error_prix = 'Le prix doit être supérieur à zéro.';
  } else {
    // Vérification si mois_ids est défini et n'est pas vide
    if (isset($_POST['mois_ids']) && is_array($_POST['mois_ids']) && count($_POST['mois_ids']) > 0) {
      $ids_mois = $_POST['mois_ids']; // Tableau des IDs des mois sélectionnés

      // Vérification du téléchargement des images
      if (isset($_FILES['imageFL']) && $_FILES['imageFL']['error'] === UPLOAD_ERR_OK &&
        isset($_FILES['imageD']) && $_FILES['imageD']['error'] === UPLOAD_ERR_OK) {

        // Téléchargement des images réussi
        $file_imageFL = basename($_FILES['imageFL']['name']);
        $file_imageD = basename($_FILES['imageD']['name']);

        if (move_uploaded_file($_FILES['imageFL']['tmp_name'], "ressources/FL/$file_imageFL") &&
          move_uploaded_file($_FILES['imageD']['tmp_name'], "ressources/img_dispo/$file_imageD")) {

          try {
            $pdo->beginTransaction();

            // Enregistrement des données dans la table fruits_legumes
            $query = "INSERT INTO fruits_legumes(libelle, prix, vitamines, mineraux, img, img_dispo, id_users, type, kilo_piece)
              VALUES (:libelle, :prix, :vitamines, :mineraux, :img, :img_dispo, :id_users, :type, :kilo_piece)";

            $req_FL = $pdo->prepare($query);
            $req_FL->bindParam(':libelle', $libelle);
            $req_FL->bindParam(':prix', $prix);
            $req_FL->bindParam(':vitamines', $vitamines);
            $req_FL->bindParam(':mineraux', $mineraux);
            $req_FL->bindParam(':img', $file_imageFL);
            $req_FL->bindParam(':img_dispo', $file_imageD);
            $req_FL->bindParam(':id_users', $id_users);
            $req_FL->bindParam(':type', $type);
            $req_FL->bindParam(':kilo_piece', $kilo_piece);
            $req_FL->execute();

            // Récupération de l'ID du fruit/légume inséré
            $fruit_legume_id = $pdo->lastInsertId();

            // Enregistrement des relations dans la table fruits_legumes_mois
            foreach ($ids_mois as $id_mois) {
              $query = "INSERT INTO fruits_legumes_mois(id_fruits_legumes, id_mois) VALUES (:id_fruits_legumes, :id_mois)";
              $req_FL = $pdo->prepare($query);
              $req_FL->bindParam(':id_fruits_legumes', $fruit_legume_id);
              $req_FL->bindParam(':id_mois', $id_mois, PDO::PARAM_INT);
              $req_FL->execute();
            }

            $pdo->commit();
            $success = 'Fiche créée avec succès.';
            $libelle = "";
            $prix = "";
          } catch (PDOException $e) {
            $pdo->rollBack();
            echo 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
          }
        } else {
          $error_img = 'Envoi des images échoué.';
        }
      } else {
        $error_img = 'Veuillez insérer les 2 images (5Mo max chacune)';
      }
    } else {
      $errorMonth = 'Veuillez sélectionner au moins un mois.';
    }
  }
}
}

?>
<!DOCTYPE html >
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="fruits et légumes de saison">
    <title>MFLS</title>
    <link href="https://fonts.googleapis.com/css2?family=Spicy+Rice&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Marck+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Paprika&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style_cards.css">
    
</head>
<body>
  <header>
    <div class="logo_title">
      <a  href="index.php" id="InitialMonth"><img class= "logo" src="ressources/logo2.png" alt="Image du logo"></a>
      <h1 class="titre">Mes fruits et légumes de saison</h1>  
  </header>
  <a href="mesFiches.php" class="btn_MesFiches" >Mes fiches</a>
  <div class="formCard">
    <form action="#" method="post" enctype="multipart/form-data">
        <label for="nom">Nom et type de la fiche:</label>
        <input type="text" id="nom" name="libelle" value="<?php echo $libelle; ?>" autofocus="autofocus" required>
        <select id="type" name="type">
          <option value="fruit">Fruit</option>
          <option value="legume">Légume</option>
        </select>

        <?php if (!empty($error_libelle)) { ?>
        <div class="error"><?php echo $error_libelle; ?></div>
      <?php } ?>

        <label for="imageFL">Insérer l'image du fruit:</label>
        <input type="file" id="imageFL" name="imageFL" accept="image/*" onchange="previewImageFL(event)">
        <img id="previewFL" class="preview-image" src="#" alt="">

        <?php if (!empty($error_img)) { ?>
        <div class="error"><?php echo $error_img; ?></div>
      <?php } ?>

        <label for="imageD">Insérer l'image de disponibilité:</label>
        <input type="file" id="imageD" name="imageD" accept="image/*" onchange="previewImageDispo(event)">
        <img id="previewDispo" class="preview-image" src="#" alt="">
        
        

        <label for="mois">Saison:</label>
        <select id="mois_ids" name="mois_ids[]" require multiple>
            <option value="01">Janvier</option>
            <option value="02">Février</option>
            <option value="03">Mars</option>
            <option value="04">Avril</option>
            <option value="05">Mai</option>
            <option value="06">Juin</option>
            <option value="07">Juillet</option>
            <option value="08">Août</option>
            <option value="09">Septembre</option>
            <option value="10">Octobre</option>
            <option value="11">Novembre</option>
            <option value="12">Décembre</option>
        </select>

        <?php if (!empty($errorMonth)) { ?>
        <div class="error"><?php echo $errorMonth; ?></div>
      <?php } ?>
      
        <label for="prix">Prix:</label>
        <input type="number" id="prix" name="prix" value="<?php echo $prix; ?>" step="0.01" required>

        <select id="kilo_piece" name="kilo_piece">
          <option value="€/KG">€/KG</option>
          <option value="€ la pièce">€ la pièce</option>
        </select>

        <?php if (!empty($error_prix)) { ?>
        <div class="error"><?php echo $error_prix; ?></div>
      <?php } ?>

        <label for="vitamines">Vitamines:</label>
        <input type="text" id="vitamines" name="vitamines" value="<?php echo $vitamines; ?>" required>

        <label for="mineraux">Minéraux:</label>
        <input type="text" id="mineraux" name="mineraux" value="<?php echo $mineraux; ?>" required>

        <button id="submitCard" type="submit">Créer la fiche</button>
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="appCards.js"></script>
</body>
</html>
