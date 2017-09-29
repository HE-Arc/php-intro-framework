<?php // 05-composer

require "vendor/autoload.php";

use RedBeanPHP\Facade as R;

// Connexion à la base de données.
R::setup("sqlite:" . __DIR__ . "/../users.db");

// Configuration de Twig
$loader = new Twig_Loader_Filesystem("templates");
$twig = new Twig_Environment($loader);

// Ajout des filtres md5 et strtolower qui sont les fonctions PHP du même nom.
$twig->addFilter(new Twig_SimpleFilter("strtolower", "strtolower"));
$twig->addFilter(new Twig_SimpleFilter("md5", "md5"));

// variables globales
$titre = "HE-Arc";
$base = dirname($_SERVER["SCRIPT_NAME"]);

// Lecture de l"URL
list($uri) = explode("?", $_SERVER["REQUEST_URI"], 2);
// on ôte le prefix même que RewriteBase.
$uri = substr($uri, strlen($base));
// on match.
$matches = [];
if (preg_match("#^/(?<page>[^/]+)/(?<slug>[^/]+)/?#", $uri, $matches)) {
    $page = $matches["page"];
    $args = [$matches["slug"]];
} else {
    $page = "accueil";
    $args = [];
}

// Front controller
if (function_exists($page)) {
    $body = call_user_func_array($page, $args);
} else {
    $body = not_found();
}

header("Content-Type: text/html; charset=utf-8");
header("Content-Length: " . strlen($body));
echo $body;

// les pages
function equipe($slug) {
    global $twig, $base, $titre;
    $personne = R::findOne("personnes", "slug = ?", [$slug]);
    if (!$personne) {
        return not_found();
    }
    return $twig->render("equipe.html", compact("base", "titre", "personne"));
}

function accueil() {
    global $twig, $base, $titre;
    $personnes = R::find("personnes");
    return $twig->render("accueil.html", compact("base", "titre", "personnes"));
}

// page d'erreur
function not_found() {
    global $twig;
    header("404 Not Found");
    return $twig->render("404.html");
}
