<?php declare(strict_types=1);
session_start(); 

if (!isset($_SESSION['id'])) {
  header('Location: error404.php');
  exit(); 
}

$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');
$id_utilisateur = $_SESSION['id'];

$query = "SELECT r.* FROM recettes r
          INNER JOIN users u ON r.id_users = u.id
          WHERE u.id = :id
          ORDER BY id_recettes DESC";

$recette = $pdo->prepare($query);

// Liez la valeur de l'identifiant de l'utilisateur
$recette->bindParam(':id', $id_utilisateur, PDO::PARAM_INT);

// Exécutez la requête
$recette->execute();

// Récupérez les résultats
$recettes = $recette->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_recette'])) {
    $id_recette = $_POST['id_recette'];

    // Sélectionnez le nom de l'image associée à cette recette
    $img_query = "SELECT img FROM recettes WHERE id_recettes = :id_recette";
    $select_img = $pdo->prepare($img_query);
    $select_img->bindParam(':id_recette', $id_recette, PDO::PARAM_INT);
    $select_img->execute();

    if ($select_img->rowCount() > 0) {
        $row = $select_img->fetch(PDO::FETCH_ASSOC);
        $img = $row['img'];

        // Supprimez la recette de la base de données
        $delete_query = "DELETE FROM recettes WHERE id_recettes = :id_recette";
        $delete = $pdo->prepare($delete_query);
        $delete->bindParam(':id_recette', $id_recette, PDO::PARAM_INT);

        if ($delete->execute()) {
            // Suppression réussie, supprimez également l'image du dossier img_recettes
            if (!empty($img)) {
                $file_img = "ressources/img_recette/$img";
                if (file_exists($file_img)) {
                    unlink($file_img);
                }
            }
            
            header('Location: mesRecettes.php'); 
            exit();
        } else {
            // Gestion des erreurs de suppression de recette
            echo "Erreur lors de la suppression de la recette.";
        }
    } else {
        // La recette spécifiée n'existe pas
        echo "Recette introuvable.";
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
    <link rel="stylesheet" href="style_mesrecettes_fiches.css"> 
</head>
<body>
<header>
  <div class="box-login">
    <?php if (isset($_SESSION['pseudo'])): ?>
        <h2>Bienvenue <?php echo $_SESSION['pseudo']; ?> (ID: <?php echo $_SESSION['id']; ?>)</h2>
    <?php else: ?>
        <h2>non connecté</h2>
    <?php endif; ?>
</div>
    <div class="logo_title">
      <a  href="index.php" id="InitialMonth"><img class= "logo" src="ressources/logo2.png" alt="Image du logo"></a>
      <h1 class="titre">Mes fruits et légumes de saison</h1>  
    </div>
  </header>
  <a href="add_recette.php"><button type="submit" class="ajouter">Ajouter une recette</button></a>
  <div class="box-donnees">
    <?php if (empty($recettes)) : ?>
  <h3 class="errorR">Pas encore de recette!!! </h3>
    <?php else : ?>
    <?php foreach ($recettes as $recette):  ?>
    <div class="mesDonnees">
           <h2><?php echo $recette['nom']; ?></h2>
        <div class="btn_upt">
               <a href="edit_recette.php?id_recettes=<?php echo $recette['id_recettes']; ?>"><button type="submit" class="modifier">Modifier</button></a>
            <form method="POST">
                <input type="hidden" name="id_recette" value="<?php echo $recette['id_recettes']; ?>">
                <button type="submit" class="supprimer">Supprimer</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
    
    <footer class="footerMobil">
    <div class="footerL">
      <a href="index.php">Accueil</a>
    </div>
    <div class="footerR">
      <a href="Recettes.php">Recettes</a>
    </div>
    </footer>
    <script src="appCompte.js"></script>
</body>
</html>
