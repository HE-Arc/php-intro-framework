<?php // 04-routes

require "../vendor/RedBean/rb.php";
require_once "../vendor/Twig/lib/Twig/Autoloader.php";
Twig_Autoloader::register();

// Connexion à la page de donnée.
R::setup("sqlite:../users.db");

// Configuration de Twig
$loader = new Twig_Loader_FileSystem("templates");
$twig = new Twig_Environment($loader);

// Ajout des filtres md5 et strtolower qui sont les fonctions PHP du même nom.
$twig->addFilter(new Twig_SimpleFilter("strtolower", "strtolower"));
$twig->addFilter(new Twig_SimpleFilter("md5", "md5"));

// variables globales
$titre = "He-Arc";
$base = dirname($_SERVER["SCRIPT_NAME"]);

// Lecture de l"URL
list($uri) = explode("?", $_SERVER["REQUEST_URI"], 2);
// on ôte le prefix même que RewriteBase.
$uri = substr($uri, strlen($base));
// on match.
$matches = [];
if (preg_match("#^/(?P<page>[^/]+)/(?P<slug>[^/]+)/?#", $uri, $matches)) {
    $page = $matches["page"];
    $args = [$matches["slug"]];
} else {
    $page = "accueil";
    $args = [];
}

// Front controller
if (function_exists($page)) {
    echo call_user_func_array($page, $args);
} else {
    header("404 Not Found");
    echo $twig->render("404.html");
}

// les pages
function equipe($slug) {
    global $twig, $base, $titre;
    $personne = R::findOne("personnes", "slug = ?", [$slug]);
    return $twig->render("equipe.html", compact("base", "titre", "personne"));
}

function accueil() {
    global $twig, $base, $titre;
    $personnes = R::find("personnes");
    return $twig->render("accueil.html", compact("base", "titre", "personnes"));
}
