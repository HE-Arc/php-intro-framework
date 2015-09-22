<?php
// 00-base

// Lecture de la query string `page=<XX>&id=<YY>`.
$page = isset($_GET["page"]) ? $_GET["page"] : null;
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

// Connexion à la page de donnée.
$db = new PDO("sqlite:../users.db");

// Page HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8>
    <title>He-Arc</title>
</head>
<body>

<?php
// Contenu
if ("equipe" === $page) {
    $query = $db->query("SELECT * FROM `personnes` WHERE `id` = ?");
    $query->execute([$id]);

    $personne = $query->fetch(PDO::FETCH_OBJ);
?>
    <p><a href="<?php echo $_SERVER["PHP_SELF"] ?>">retour</a></p>
    <h1>Équipe</h1>
    <h2>
        <?php echo $personne->prenom ?>
        <?php echo $personne->nom ?>
    </h2>
    <p>
        <img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower($personne->email)) ?>" alt="avatar">
    </p>
<?php
} else {
?>
    <h1>Accueil</h1>
    <ul>
        <li><a href="?page=equipe&id=1">Yoan Blanc</a></li>
        <li><a href="?page=equipe&id=2">Yoan Blanc</a></li>
    </ul>
<?php
}
?>
</body>
</html>