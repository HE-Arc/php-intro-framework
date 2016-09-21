<?php // 06-fastroute

require "vendor/autoload.php";

use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;
use FastRoute\RouteCollector;

use RedBeanPHP\Facade as R;


// Connexion à la page de donnée.
R::setup("sqlite:" . __DIR__ . "/../users.db");

// Configuration de Twig
$loader = new Twig_Loader_Filesystem("templates");
$twig = new Twig_Environment($loader);

// Ajout des filtres md5 et strtolower qui sont les fonctions PHP du même nom.
$twig->addFilter(new Twig_SimpleFilter("strtolower", "strtolower"));
$twig->addFilter(new Twig_SimpleFilter("md5", "md5"));

// Définition des routes
$dispatcher = simpleDispatcher(function(RouteCollector $r) {
    $r->addRoute('GET', '/', 'accueil');
    $r->addRoute('GET', '/equipe/{slug}', 'equipe');
});

// variables globales
$titre = "He-Arc";
$base = dirname($_SERVER["SCRIPT_NAME"]);

// verbe et requête HTTP
$httpMethod = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"];

// on ôte le prefix même que rewritebase.
$uri = substr($uri, strlen($base));
// on nettoie l'url.
if (false !== ($pos = strpos($uri, '?'))) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// exécution du routage.
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch($routeInfo[0]) {
case Dispatcher::NOT_FOUND:
    $body = not_found();
    break;
case Dispatcher::METHOD_NOT_ALLOWED:
    $body = not_allowed($routeInfo[1]);
    break;
case Dispatcher::FOUND:
    $handler = $routeInfo[1];
    $args = $routeInfo[2];
    try {
        $body = call_user_func_array($handler, $args);
    } catch (Exception $e){
        $body = server_error($e);
    }
    break;
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

// les pages d'erreur
function not_found() {
    global $twig;
    header("404 Not Found");
    return $twig->render("404.html");
}

function not_allowed($allowedMethods) {
    global $twig;
    header("405 Method Not Allowed");
    return $twig->render("405.html", compact('allowedMethods'));
}

function server_error($exception) {
    global $twig;
    header("500 Internal Server Error");
    return $twig->render("500.html", compact('exception'));
}
