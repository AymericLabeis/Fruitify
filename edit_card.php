<?php declare(strict_types=1);
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: error404.php');
    exit(); 
  }

$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');

if (isset($_GET['id_fruits_legumes'])) {
    $id_fruits_legumes = $_GET['id_fruits_legumes'];

    $query = "SELECT * FROM fruits_legumes WHERE id_fruits_legumes = :id_fruits_legumes";
    $upt_req_FL = $pdo->prepare($query);
    $upt_req_FL->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
    $upt_req_FL->execute();

    // Mettre à jour $fruit_legume avec les données de la base de données
    $fruit_legume = $upt_req_FL->fetch(PDO::FETCH_ASSOC);

    // Préremplir les valeurs du formulaire avec les données actuelles
    $libelle = $fruit_legume['libelle'];
    $prix = $fruit_legume['prix'];
    $vitamines = $fruit_legume['vitamines'];
    $mineraux = $fruit_legume['mineraux'];
    $type = $fruit_legume['type'];
    $kilo_piece = $fruit_legume['kilo_piece'];

    // Sélectionner les mois associés à ce fruit ou légume
    $query = "SELECT id_mois FROM fruits_legumes_mois WHERE id_fruits_legumes = :id_fruits_legumes";
    $upt_req_FL = $pdo->prepare($query);
    $upt_req_FL->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
    $upt_req_FL->execute();
    $selected_months = $upt_req_FL->fetchAll(PDO::FETCH_COLUMN);

    // Mettre à jour les valeurs des images
    $img = $fruit_legume['img'];
    $img_dispo = $fruit_legume['img_dispo'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_fruits_legumes'])) {
    $id_fruits_legumes = $_POST['id_fruits_legumes'];

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
        $error_libelle = 'Le libellé doit comporter entre 2 et 20 caractères.';
    }

    // Vérification du prix
    if (!is_numeric($prix) || $prix <= 0) {
        $error_prix = 'Le prix doit supérieur à zéro.';
    } else {
        // Convertir le prix en nombre à virgule flottante
        $prix = (float)$prix;

        // Si les vérifications du libellé et du prix sont passées, procéder à la mise à jour
        if (!isset($error_libelle) && !isset($error_prix)) {
            // Gestion des images uniquement si de nouvelles images ont été téléchargées
            if (isset($_FILES['nouvelle_img']) && $_FILES['nouvelle_img']['error'] === UPLOAD_ERR_OK) {
                $new_img = basename($_FILES['nouvelle_img']['name']);

                // Supprimer l'ancienne image si elle existe
                if (!empty($img)) {
                    $chemin_image = "ressources/FL/$img";
                    if (file_exists($chemin_image)) {
                        unlink($chemin_image);
                    }
                }

                // Déplacer le fichier image vers le dossier de destination
                $destination_img = "ressources/FL/$new_img";
                if (move_uploaded_file($_FILES['nouvelle_img']['tmp_name'], $destination_img)) {
                    $img = $new_img;
                } else {
                    $error_img = 'Erreur lors du déplacement de la nouvelle image du fruit.';
                }
            }

            if (isset($_FILES['nouvelle_img_dispo']) && $_FILES['nouvelle_img_dispo']['error'] === UPLOAD_ERR_OK) {
                $new_img_dispo = basename($_FILES['nouvelle_img_dispo']['name']);

                // Déplacer le fichier image de disponibilité vers le dossier de destination
                $destination_img_dispo = "ressources/img_dispo/$new_img_dispo";
                if (move_uploaded_file($_FILES['nouvelle_img_dispo']['tmp_name'], $destination_img_dispo)) {
                    $img_dispo = $new_img_dispo;
                } else {
                    $error_img_dispo = 'Erreur lors du déplacement de la nouvelle image de disponibilité.';
                }
            }

            // Vérification si mois_ids est défini et n'est pas vide
            if (isset($_POST['mois_ids']) && is_array($_POST['mois_ids']) && count($_POST['mois_ids']) > 0) {
                $ids_mois = $_POST['mois_ids']; // Tableau des IDs des mois sélectionnés

                try {
                    $pdo->beginTransaction();

                    // C'est une modification
                    $query = "UPDATE fruits_legumes
                              SET libelle = :libelle, prix = :prix, vitamines = :vitamines, mineraux = :mineraux,
                                  id_users = :id_users, type = :type, kilo_piece = :kilo_piece,
                                  img = :img";

                    // Mettez à jour l'image de disponibilité uniquement si une nouvelle image est téléchargée
                    if (isset($_FILES['nouvelle_img_dispo']) && $_FILES['nouvelle_img_dispo']['error'] === UPLOAD_ERR_OK) {
                        $query .= ", img_dispo = :img_dispo";
                    }

                    $query .= " WHERE id_fruits_legumes = :id_fruits_legumes";

                    $upt_req_FL = $pdo->prepare($query);
                    $upt_req_FL->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
                    $upt_req_FL->bindParam(':libelle', $libelle);
                    $upt_req_FL->bindParam(':prix', $prix);
                    $upt_req_FL->bindParam(':vitamines', $vitamines);
                    $upt_req_FL->bindParam(':mineraux', $mineraux);
                    $upt_req_FL->bindParam(':id_users', $id_users, PDO::PARAM_INT);
                    $upt_req_FL->bindParam(':type', $type);
                    $upt_req_FL->bindParam(':kilo_piece', $kilo_piece);
                    $upt_req_FL->bindParam(':img', $img);

                    // Mettez à jour l'image de disponibilité uniquement si une nouvelle image est téléchargée
                    if (isset($_FILES['nouvelle_img_dispo']) && $_FILES['nouvelle_img_dispo']['error'] === UPLOAD_ERR_OK) {
                        $upt_req_FL->bindParam(':img_dispo', $img_dispo);
                    }

                    $upt_req_FL->execute();

                    // Supprimez les anciennes relations dans la table fruits_legumes_mois
                    $query = "DELETE FROM fruits_legumes_mois WHERE id_fruits_legumes = :id_fruits_legumes";
                    $upt_req_FL = $pdo->prepare($query);
                    $upt_req_FL->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
                    $upt_req_FL->execute();

                    // Enregistrement des nouvelles relations dans la table fruits_legumes_mois
                    foreach ($ids_mois as $id_mois) {
                        $query = "INSERT INTO fruits_legumes_mois(id_fruits_legumes, id_mois) VALUES (:id_fruits_legumes, :id_mois)";
                        $upt_req_FL = $pdo->prepare($query);
                        $upt_req_FL->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
                        $upt_req_FL->bindParam(':id_mois', $id_mois, PDO::PARAM_INT);
                        $upt_req_FL->execute();
                    }

                    $pdo->commit();
                    $success = 'Fiche mise à jour avec succès.';
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    echo 'Erreur lors de la modification : ' . $e->getMessage();
                }
            } else {
                $errorMonth = 'Veuillez sélectionner au moins un mois.';
            }
        }
    }
}


if (isset($success)) {
    // Réexécutez la requête pour récupérer les données mises à jour de la fiche
    $query = "SELECT * FROM fruits_legumes WHERE id_fruits_legumes = :id_fruits_legumes";
    $upt_req_FL = $pdo->prepare($query);
    $upt_req_FL->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
    $upt_req_FL->execute();

    // Mettre à jour $fruit_legume avec les données de la base de données
    $fruit_legume = $upt_req_FL->fetch(PDO::FETCH_ASSOC);

    // Mettez à jour les variables PHP pour préremplir les champs du formulaire avec les nouvelles données
    $libelle = $fruit_legume['libelle'];
    $prix = $fruit_legume['prix'];
    $vitamines = $fruit_legume['vitamines'];
    $mineraux = $fruit_legume['mineraux'];
    $type = $fruit_legume['type'];
    $kilo_piece = $fruit_legume['kilo_piece'];
    $img = $fruit_legume['img'];
    $img_dispo = $fruit_legume['img_dispo'];

    // Sélectionner les mois associés à ce fruit ou légume
    $query = "SELECT id_mois FROM fruits_legumes_mois WHERE id_fruits_legumes = :id_fruits_legumes";
    $upt_req_FL = $pdo->prepare($query);
    $upt_req_FL->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
    $upt_req_FL->execute();
    $selected_months = $upt_req_FL->fetchAll(PDO::FETCH_COLUMN);
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
        <a href="index.php" id="InitialMonth"><img class="logo" src="ressources/logo2.png" alt="Image du logo"></a>
        <h1 class="titre">Mes fruits et légumes de saison</h1>
    </div>
</header>
<a href="mesFiches.php" class="btn_MesFiches" >Mes fiches</a>
<div class="formCard">
    <form action="#" method="post" enctype="multipart/form-data">
        <?php if (isset($_GET['id_fruits_legumes'])) : ?>
            <input type="hidden" name="id_fruits_legumes" value="<?php echo $_GET['id_fruits_legumes']; ?>">
        <?php endif; ?>
        <label for="nom">Nom et type de la fiche:</label>
        <input type="text" id="nom" name="libelle" value="<?php echo $libelle; ?>" autofocus="autofocus" required>
        <select id="type" name="type">
            <option value="fruit" <?php if ($type === 'fruit') echo 'selected'; ?>>Fruit</option>
            <option value="legume" <?php if ($type === 'legume') echo 'selected'; ?>>Légume</option>
        </select>

        <?php if (!empty($error_libelle)) { ?>
        <div class="error"><?php echo $error_libelle; ?></div>
      <?php } ?>

        <label for="imageFL">Insérer l'image du fruit</label>
        <input type="file" id="imageFL" name="nouvelle_img" accept="image/*" onchange="previewImageFL(event)">
        <img id="previewFL" class="preview-image" src="#" alt="">

        <?php if (!empty($error_img)) { ?>
            <div class="error"><?php echo $error_img; ?></div>
        <?php } ?>

        <label for="imageD">Insérer l'image de disponibilité</label>
        <input type="file" id="imageD" name="nouvelle_img_dispo" accept="image/*" onchange="previewImageDispo(event)">
        <img id="previewDispo" class="preview-image" src="#" alt="">

        <label for="mois">Mois disponible</label>
        <select id="mois_ids" name="mois_ids[]" require multiple>
            <option value="01" <?php if (in_array('01', $selected_months)) echo 'selected'; ?>>Janvier</option>
            <option value="02" <?php if (in_array('02', $selected_months)) echo 'selected'; ?>>Février</option>
            <option value="03" <?php if (in_array('03', $selected_months)) echo 'selected'; ?>>Mars</option>
            <option value="04" <?php if (in_array('04', $selected_months)) echo 'selected'; ?>>Avril</option>
            <option value="05" <?php if (in_array('05', $selected_months)) echo 'selected'; ?>>Mai</option>
            <option value="06" <?php if (in_array('06', $selected_months)) echo 'selected'; ?>>Juin</option>
            <option value="07" <?php if (in_array('07', $selected_months)) echo 'selected'; ?>>Juillet</option>
            <option value="08" <?php if (in_array('08', $selected_months)) echo 'selected'; ?>>Août</option>
            <option value="09" <?php if (in_array('09', $selected_months)) echo 'selected'; ?>>Septembre</option>
            <option value="10" <?php if (in_array('10', $selected_months)) echo 'selected'; ?>>Octobre</option>
            <option value="11" <?php if (in_array('11', $selected_months)) echo 'selected'; ?>>Novembre</option>
            <option value="12" <?php if (in_array('12', $selected_months)) echo 'selected'; ?>>Décembre</option>
        </select>

        <?php if (!empty($errorMonth)) { ?>
            <div class="error"><?php echo $errorMonth; ?></div>
        <?php } ?>

        <label for="prix">Prix</label>
        <input type="number" id="prix" name="prix" value="<?php echo $prix; ?>" step="0.01" required>

        <select id="kilo_piece" name="kilo_piece">
            <option value="€/KG" <?php if ($kilo_piece === '€/KG') echo 'selected'; ?>>€/KG</option>
            <option value="€ la pièce" <?php if ($kilo_piece === '€ la pièce') echo 'selected'; ?>>€ la pièce</option>
        </select>

        <?php if (!empty($error_prix)) { ?>
        <div class="error"><?php echo $error_prix; ?></div>
      <?php } ?>
        <label for="vitamines">Vitamines:</label>
        <input type="text" id="vitamines" name="vitamines" value="<?php echo $vitamines; ?>" required>

        <label for="mineraux">Minéraux:</label>
        <input type="text" id="mineraux" name="mineraux" value="<?php echo $mineraux; ?>" required>

        <button id="submitCard" type="submit"><?php echo isset($_GET['id_fruits_legumes']) ? 'Modifier' : 'Créer'; ?> la fiche</button>
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
        <a href="fruits_legumes.php">fruits_legumes</a>
    </div>
</footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="appCards.js"></script>
</body>
</html>