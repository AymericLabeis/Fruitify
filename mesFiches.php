<?php declare(strict_types=1);
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location: error404.php');
    exit(); 
}
$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');
$id_utilisateur = $_SESSION['id'];

$query = "SELECT fl.* FROM fruits_legumes fl
          INNER JOIN users u ON fl.id_users = u.id
          WHERE u.id = :id
          ORDER BY libelle ASC";

$fruit_legume = $pdo->prepare($query);
$fruit_legume->bindParam(':id', $id_utilisateur, PDO::PARAM_INT);
$fruit_legume->execute();
$fruits_legumes = $fruit_legume->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_fruits_legumes'])) {
    $id_fruits_legumes = $_POST['id_fruits_legumes'];

    $img_query = "SELECT img FROM fruits_legumes WHERE id_fruits_legumes = :id_fruits_legumes";
    $select_img = $pdo->prepare($img_query);
    $select_img->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);
    $select_img->execute();

    if ($select_img->rowCount() > 0) {
        $row = $select_img->fetch(PDO::FETCH_ASSOC);
        $img = $row['img'];
        $delete_query = "DELETE FROM fruits_legumes WHERE id_fruits_legumes = :id_fruits_legumes";
        $delete = $pdo->prepare($delete_query);
        $delete->bindParam(':id_fruits_legumes', $id_fruits_legumes, PDO::PARAM_INT);

        if ($delete->execute()) {
            
            if (!empty($img)) {
                $file_img = "ressources/FL/$img";
                if (file_exists($file_img)) {
                    unlink($file_img);
                }
            }
            header('Location: mesFiches.php'); 
            exit();
        } else {
            echo "Erreur lors de la suppression de la fiche.";
        }
    } else {
        echo "Fiche introuvable.";
    }
}

?>
<!DOCTYPE html>
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
<a href="add_card.php"><button type="submit" class="ajouter">Ajouter une fiche</button></a>
<div class="box-donnees">
<?php if (empty($fruits_legumes)) : ?>
    <h3 class="errorR">Pas encore de cartes !!! </h3>
<?php else : ?>
    <?php foreach ($fruits_legumes as $fruit_legume):  ?>
        <div class="mesDonnees">
            <h2><?php echo $fruit_legume['libelle']; ?></h2>
            <div class="btn_upt">
            <a href="edit_card.php?id_fruits_legumes=<?php echo $fruit_legume['id_fruits_legumes']; ?>"><button type="submit" class="modifier">Modifier</button></a>
             <form method="POST">
               <input type="hidden" name="id_fruits_legumes" value="<?php echo $fruit_legume['id_fruits_legumes']; ?>">
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
    <script src="appCompte.js"></script>
</footer>
</body>
</html>
