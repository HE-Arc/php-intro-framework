<?php
// 00-base

// Lecture de la query string `page=<XX>&id=<YY>`.
$page = $_GET["page"] ?? null;
$id = (int) ($_GET["id"] ?? 0);

// Connexion à la base de données.
$db = new PDO("sqlite:../users.db");

// Entête HTTP
header("Content-Type: text/html; charset=utf-8");
// Page HTML
?>
<!DOCTYPE html>
<title>HE-Arc</title>
<?php
// Contenu
if ("equipe" === $page):
    $query = $db->query("SELECT * FROM `personnes` WHERE `id` = :id");
    $query->execute(compact('id'));

    $personne = $query->fetch(PDO::FETCH_OBJ);
?>
    <p><a href="<?php echo $_SERVER["PHP_SELF"] ?>">retour</a>
    <h1>Équipe</h1>
    <h2>
        <?php echo $personne->prenom ?>
        <?php echo $personne->nom ?>
    </h2>
    <p><img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower($personne->email)) ?>" alt="avatar">
<?php
else:
?>
    <h1>Accueil</h1>
    <ul>
        <li><a href="?page=equipe&id=1">Yoan Blanc</a>
        <li><a href="?page=equipe&id=2">Yoan Blanc</a>
    </ul>
<?php
endif;
