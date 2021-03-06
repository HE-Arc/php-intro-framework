<?php // 02-twig

require_once "../vendor/Twig/lib/Twig/Autoloader.php";
Twig_Autoloader::register();

// Lecture de la query string `page=<XX>&id=<YY>`.
$page = $_GET["page"] ?? null;
$id = (int) ($_GET["id"] ?? 0);

// Connexion à la base de données.
$db = new PDO("sqlite:../users.db");

// Configuration de Twig
$loader = new Twig_Loader_Filesystem("templates");
$twig = new Twig_Environment($loader);

// Ajout des filtres md5 et strtolower qui sont les fonctions PHP du même nom.
$twig->addFilter(new Twig_SimpleFilter("strtolower", "strtolower"));
$twig->addFilter(new Twig_SimpleFilter("md5", "md5"));

// variable globale
$titre = "HE-Arc";

// Contenu
if ("equipe" === $page) {
    $query = $db->query("SELECT * FROM `personnes` WHERE `id` = ?");
    $query->execute([$id]);

    $personne = $query->fetch(PDO::FETCH_OBJ);

    $body = $twig->render("equipe.html", compact("titre", "personne"));
} else {
    $query = $db->query("SELECT * FROM `personnes`");
    $query->execute();

    $personnes = $query->fetchAll(PDO::FETCH_OBJ);

    $body = $twig->render("accueil.html", compact("titre", "personnes"));
}

header("Content-Type: text/html; charset=utf-8");
header("Content-Length: " . strlen($body));
echo $body;
