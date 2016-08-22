<?php // 06-aura

require "vendor/autoload.php";

use RedBeanPHP\Facade as R;

use Aura\Router\RouterContainer;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;


// Connexion à la page de donnée.
R::setup("sqlite:" . __DIR__ . "/../users.db");

// Configuration de Twig
$loader = new Twig_Loader_Filesystem("templates");
$twig = new Twig_Environment($loader);

// Ajout des filtres md5 et strtolower qui sont les fonctions PHP du même nom.
$twig->addFilter(new Twig_SimpleFilter("strtolower", "strtolower"));
$twig->addFilter(new Twig_SimpleFilter("md5", "md5"));

// variables globales
$titre = "He-Arc";
$base = dirname($_SERVER['SCRIPT_NAME']);

// Aura boilerplate.
$router = new RouterContainer();
$map = $router->getMap();
$generator = $router->getGenerator();

// Getsion du prefix de l'application.
$map->attach('', $base, function($map) use ($twig, $generator, $titre) {
    $map->get(
        'accueil',
        '/',
        function($request, $response) use ($twig, $generator, $titre) {
            $personnes = R::find("personnes");
            $body = $twig->render(
                "accueil.html",
                compact("generator", "titre", "personnes")
            );
            $response->getBody()->write($body);
            return $response;
        }
    );

    $map->get(
        'equipe',
        '/equipe/{slug}',
        function($request, $response) use ($twig, $generator, $titre) {
            $slug = $request->getAttribute('slug');
            $personne = R::findOne("personnes", "slug = ?", [$slug]);
            if (!$personne) {
                $body = $twig->render("404.html");
                $response->withStatus(404);
            } else {
                $body = $twig->render(
                    "equipe.html",
                    compact("generator", "titre", "personne")
                );
            }
            $response->getBody()->write($body);
            return $response;
        }
    );
});

// Requête et réponse HTTP
$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);
$response = new Response();

// Routage
$matcher = $router->getMatcher();
$route = $matcher->match($request);

if (!$route) {
    // Gestion des erreurs.
    $body = $twig->render("404.html");
    $response->getBody()->write($body);
    $response->withStatus(404);
} else {
    foreach ($route->attributes as $key => $val) {
        $request = $request->withAttribute($key, $val);
    }
    $callable = $route->handler;
    $response = $callable($request, $response);
}

// Sortie (SAPI ~ Server API)
$emitter = new SapiEmitter();
$emitter->emit($response);
