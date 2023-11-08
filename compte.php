<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=projet_fl', 'root', '');

// Récupérez les informations de l'utilisateur actuel
$userQuery = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$userQuery->execute([$_SESSION['id']]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

$name = $user['name'];
$email = $user['email'];
$password = '';
$errorName = '';
$errorEmail = '';
$errorPassword = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = htmlspecialchars($_POST['utilisateur'], ENT_QUOTES, 'UTF-8');
    $newEmail = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $newPassword = $_POST['mdp'];
    $newConfirmPassword = $_POST['mdpconf']; 

    // Effectuez les validations nécessaires sur les données soumises

    // Vérifiez si les deux mots de passe correspondent
    if ($newPassword !== $newConfirmPassword) {
        $errorPassword = 'Les mots de passe ne correspondent pas.';
    } else {
        // Hachez le nouveau mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Mettez à jour le compte de l'utilisateur avec le nouveau mot de passe haché
        $updateQuery = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
        $updateQuery->execute([$newName, $newEmail, $hashedPassword, $_SESSION['id']]);

        if ($updateQuery->rowCount() > 0) {
            $success = 'Compte mis à jour';
            $name = $newName;
            $email = $newEmail;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="fruits et légumes de saison">
    <title>Mon compte</title>
    <link href="https://fonts.googleapis.com/css2?family=Spicy+Rice&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Marck+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Paprika&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style_compte.css"> 
</head>
<body>
<header>
    <div class="logo_title">
      <a  href="index.php" id="InitialMonth"><img class= "logo" src="ressources/logo2.png" alt="Image du logo"></a>
      <h1 class="titre">Mes fruits et légumes de saison</h1>  
    </div>
  </header>
  <div class="box-account">
    <h2>Modifier votre compte.</h2>
        <form method="post" action="">
            <div class="form-groupe">
                <label for="utilisateur">Pseudo</label>
                <input type="text" id="utilisateur" name="utilisateur" placeholder="Entrez votre pseudo" maxlength="24" value="<?= htmlspecialchars($name) ?>">
                <img src="ressources/check.svg" alt="icone de validation" class="icone-verif">
                <span class="message-alerte">Choisissez un pseudo entre 3 et 24 caractères</span>
                <?php if (!empty($errorName)) : ?>
                <div class="error-account"><?= $errorName?></div>
            <?php endif; ?>
            </div>
            <div class="form-groupe">
                <label for="email">Entrez votre mail</label>
                <input type="email" id="email" name="email" placeholder=" Entrez votre mail" value="<?= htmlspecialchars($email) ?>">
                <img src="ressources/check.svg" alt="icone de validation" class="icone-verif">
                <span class="message-alerte">Rentrez un email valide.</span>
                <?php if (!empty($errorEmail)) : ?>
                <div class="error-account"><?= $errorEmail?></div>
            <?php endif; ?>
            </div>
            <div class="form-groupe">
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" placeholder="Entrez un nouveau mot de passe" required>
                <img src="ressources/check.svg" alt="icone de validation" class="icone-verif">
                <span class="message-alerte">Un symbole, une lettre minuscule, un chiffre.</span>
                <?php if (!empty($errorPassword)) : ?>
                <div class="error-account"><?= $errorPassword?></div>
            <?php endif; ?>
            </div>
            <div class="ligne">
                    <div class="l1"><span>faible</span></div>
                    <div class="l2"><span>moyen</span></div>
                    <div class="l3"><span>fort</span></div>
                </div>
            <div class="form-groupe">
                <label for="mdpconf">Confirmer le mot de passe</label>
                <input type="password" id="mdpconf" name="mdpconf" placeholder="Confirmez le mot de passe" required>
                <img src="ressources/check.svg" alt="icone de validation" class="icone-verif">
            </div>
            
            <button type="submit">Modifier le compte</button>
            <?php if (!empty($success)) : ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>
        </form> 
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
