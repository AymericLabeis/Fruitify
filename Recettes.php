<?php declare(strict_types=1);
$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');

$recette = $pdo->query('SELECT *
                        FROM recettes
                        ORDER BY  id_recettes DESC'); 
                       
$recettes=$recette->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['carte'])) {
    $selectedcarte = $_GET['carte']; // Récupérer le carte ou légume depuis l'URL

    // Utiliser une requête préparée pour éviter les problèmes de sécurité
    $query = 'SELECT *
              FROM recettes
              WHERE FIND_IN_SET(:selectedcarte, fl_principal) > 0;
              ORDER BY id_recettes DESC';

    $recette = $pdo->prepare($query);
    $recette->bindParam(':selectedcarte', $selectedcarte, PDO::PARAM_STR);
    $recette->execute();

    $recettes = $recette->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['recette'])) {
  
  // Requête SQL pour rechercher des recettes en fonction du terme de recherche
 $query = 'SELECT * FROM recettes WHERE nom LIKE :searchTerm or fl_principal LIKE :searchTerm
  ORDER BY id_recettes DESC';
  $recette = $pdo->prepare($query);

  // Paramètre du terme de recherche avec le caractère joker "%" pour rechercher des correspondances partielles
  $searchTerm = '%' . $_GET['recette'] . '%';
  $recette->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
  $recette->execute();

  // Récupération des résultats
  $recettes = $recette->fetchAll(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['categories'])) {
      $categorie = $_POST['categories'];

      // Établir une connexion à votre base de données (à configurer en fonction de votre environnement)
      $pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');

      // Préparez la requête SQL en fonction de la catégorie sélectionnée
      $sql = "SELECT * FROM categories WHERE id_categories = :categorie";

      // Préparez la requête
      $stmt = $pdo->prepare($sql);

      // Liez la valeur de la catégorie sélectionnée à la requête
      $stmt->bindParam(':categorie', $categorie, PDO::PARAM_STR);

      // Exécutez la requête
      $stmt->execute();

      // Récupérez les résultats
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      // Vous pouvez ensuite traiter les résultats et les afficher
      foreach ($result as $row) {
          // Traitez chaque ligne de résultat ici
      }
  }
}


?>
<!DOCTYPE html >
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="recettes fruits et légumes">
    <title>Recettes</title>
    <link href="https://fonts.googleapis.com/css2?family=Spicy+Rice&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Marck+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Paprika&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style_recettes.css">
</head>
<body> 

  <header>
    <div class="logo_title">
      <a  href="index.php" id="InitialMonth"><img class= "logo" src="ressources/logo2.png" alt="Image du logo"></a>
      <h1 class="titre">Mes recettes de fruits et légumes</h1> 
    </div>
  </header>

  <nav>
   <div class="menu">
    <form class="recherche_Recette" method="get">
        <input type="text" name="recette" placeholder="Rechercher une recette" value="<?= htmlspecialchars($_GET['recette'] ?? '') ?>">
        <button class="rechercher" type="submit">Rechercher</button>
    </form>
        <form class="recherche_Categories" method="post">
        <button class="categories" type="button" onclick=" myCategories()">Catégories</button>
        <ul id="listeC" name="categories">
          <li value="01">Nouveauté</li>
          <li value="02">Fruits</li>
          <li value="03">Légumes</li>
          <li value="04">Facile</li>
          <li value="05">Rapide</li>
        </ul>
    </form>
   </div>  
  </nav>
  
  <div class="container_recette">
 <?php if (empty($recettes)) : ?>
  <h3 class="errorR">Aucune recette trouvée </h3>
    <?php else : ?>
 <?php foreach($recettes as $recette): ?>
  <div class= "recette" onclick="afficherRecetteA(this)">
  <div class=recetteP>
    <a href="#ancre<?= $recette['id_recettes'] ?>">
    <h1><?= $recette['nom'] ?></h1>
    <img src="ressources/img_recette/<?= $recette['img'] ?>" alt="<?= $recette['img'] ?>">
    <div class="info">
      <h3 class="duree ">Durée: <?= $recette['duree'] ?> min</h3>
      <h3 class="creer">Crée le: <?= date('d/m/Y', strtotime($recette['created_at'])) ?></h3>
      <h3 class="modifier">Modifié le: <?= date('d/m/Y', strtotime($recette['updated_at'])) ?></h3>
      </div>
      </div>
      </a>
 
    <div id="ancre<?= $recette['id_recettes'] ?>" class="recetteA">
    <h2>Ingrédients :</h2>
    <div class="ingredients">
      <p><?= nl2br(trim($recette['ingredients']))?></p>
    </div>
    <h2>Étapes de préparation :</h2>
    <div  class="etapes">
    <p><?= nl2br(trim($recette['etapes'])) ?></p>
    </div>
   </div>
   </div>
   <?php endforeach; ?>
  <?php endif; ?>
  </div>
 

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