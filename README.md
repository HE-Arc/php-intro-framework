# Framework PHP

Pourquoi y en a-t-il tant ? L'explication donnée par Joe Gregorio pour [le
langage Python](http://bitworking.org/news/Why_so_many_Python_web_frameworks)
est parce que c'est facile d'en créer un. Dans les faits, ça montre une
maturité de la plateforme.

## A long time ago, in a galaxy far, far away

Rasmus Lerdorf inventait PHP en bricolant un outil pour savoir qui venait
consulter son CV. Zend, c'est à dire ZEev et aNDi, ont réécrit PHP et qui
allait devenir PHP 3 le précurseur du langage de prédilection pour créer sur le
web.

PHP a évolué depuis pour devenir ce qu'il est aujourd'hui. Sa popularité est
liée au fait qu'il est simple à mettre en oeuvre, gratuit **et** libre et que tout
un tas de modules sont fournis avec (imagerie, base de données, etc.)

## Quiz

### Qu'est-ce qu'[Internet](https://www.youtube.com/watch?v=iDbyYGrswtg)?

> _un réseau IP_

### Qu'est-ce que le [World Wide Web](http://line-mode.cern.ch/www/hypertext/WWW/TheProject.html)?

> _Essentiellement: HTTP, un protocole d'échange de texte, d'hypertext même_

## HTTP en bref.

HTTP est un protocole texte plutôt simple, jugez plutôt:

    $ curl -v "http://www.he-arc.ch/index.php?page=equipe&id=25"
    > GET /index.php?page=equipe&id=25 HTTP/1.1
    > Host: www.he-arc.ch
    >
    < HTTP/1.1 200 OK
    < Content-Type: text/html; charset=utf-8
    <

    <!DOCTYPE html>
    <html>
        <meta charset=utf-8>
        <title>He-Arc</title>

    <!-- etc -->

Ce que nous voyons est une connexion TCP/IP au serveur `he-arc.ch`.
Une fois la connexion établie, il envoie en texte ASCII les entêtes HTTP puis
deux retours à la ligne (ce qui correspond à une ligne vide). La requête HTTP
commencent toujours par la demande, ici `GET /index.php?page=equipe&id=25
HTTP/1.1` puis les entêtes, ici: `Host: www.he-arc.ch`. La réponse du serveur
est du même type, le code de réponse (`HTTP/1.1 200 OK`), les entêtes, une ligne
vide puis le contenu.

La demande et les entêtes sont en US-ASCII mais le corps peut être encodé
autrement, ici c'est dit dans l'entête `Content-Type: text/html; charset=utf-8`.

## PHP parle HTTP.

Réalisons cette page: [`00-base/index.php`](00-base/index.php).

```php
<?php // 00-base

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
    $query = $db->query("SELECT * FROM `personnes` WHERE `id` = :id");
    $query->execute(compact('id'));

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
        <li><a href="?page=equipe&amp;id=1">Yoan Blanc</a></li>
        <li><a href="?page=equipe&amp;id=2">Yoan Blanc</a></li>
    </ul>
<?php
}
?>
</body>
</html>
```

## Templates

PHP est un langage de template. Pour preuve, il faut ouvrir une balise `<?php`
pour commencer la partie code.

Avec la pratique, on a réalisé que de mélanger la logique métier et celle
d'affichage n'était pas optimale car difficile à lire et maintenir.

Sortons les bouts de PHP de notre page à l'aide d'`include`. Voir:
[`01-includes/index.php`](01-includes/index.php).

```php
<?php // 01-includes

// ...

include "templates/entete.html";

// Contenu
if ("equipe" === $page) {
    $query = $db->query("SELECT * FROM `personnes` WHERE `id` = :id");
    $query->execute(compact('id'));

    $personne = $query->fetch(PDO::FETCH_OBJ);

    include "templates/equipe.html";
} else {
    include "templates/accueil.html";
}

include "templates/pieddepage.html";
```

## Sécurité des templates

Dans ce le cas présent rien ne nous empêche de mettre de la logique métier dans
nos fichiers de template, car ils sont faits de PHP eux aussi. Dans le cadre de
grands projets, l'intégrateur sera peut-être un graphiste ou une société externe
en qui votre confiance est limitée.

Un template pour notre page collaborateur, réalisé avec
[Twig](http://twig.sensiolabs.org/):

```jinja
{% extends "base.html" -%}

{% block corps -%}
<p><a href="?">retour</a></p>
<h1>Équipe</h1>
<h2>
  {{- personne.prenom }} {{ personne.nom -}}
</h2>
<p>
  <img src="http://www.gravatar.com/avatar/{{ personne.email | strtolower | md5 }} ?>" alt="avatar">
</p>
{% endblock -%}
```

Le code est un poil plus propre du côté de nos templates qui ne peuvent plus
exécuter de PHP sauf ce qu'on leur autorise, ici `md5` et `strtolower`. Voir
[`02-twig/index.php`](02-twig/index.php).

```php
<?php // 02-twig

require_once 'Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

// ...

// Configuration de Twig
$loader = new Twig_Loader_FileSystem("templates");
$twig = new Twig_Environment($loader);

// Ajout des filtres md5 et strtolower qui sont les fonctions PHP du même nom.
$twig->addFilter(new Twig_SimpleFilter('strtolower', 'strtolower'));
$twig->addFilter(new Twig_SimpleFilter('md5', 'md5'));

// variable globale
$titre = "He-Arc";

// Contenu
if ("equipe" === $page) {
    // ...
    $personne = // ...

    echo $twig->render("equipe.html", compact("titre", "personne"));
} else {
    $personnes = // ...

    echo $twig->render("accueil.html", compact("titre", "personnes"));
}
```

## Base de données

Effectuer des requêtes MySQL à la main ou devoir connaitre tous les champs crée
beaucoup de redondance et de failles de sécurité potentielles.

![Exploits of a mom](https://imgs.xkcd.com/comics/exploits_of_a_mom.png)

Une solution est d'ajouter une couche d'abstraction qui va cacher la structure
réelle de notre base de données et offrir une interface orientée objet. Un
[_Object-Relational Mapping_ ou
ORM](https://fr.wikipedia.org/wiki/Mapping_objet-relationnel) dans le jargon.

```php
<?php
// Ne dites plus
$query = $db->query("SELECT * FROM `personnes` WHERE `id` = :id");
$query->execute(compact('id'));
$personne = $query->fetch(PDO::FETCH_OBJ);

// Mais dites plutôt
//  RedBean
$personne = R::load('personnes', $id);
//  Doctrine
$personne = $om->find('Personne', $id);
//  etc.
```

### ORM

Une bibliothèque qui va créer ce lien entre les mondes objet et relationnel. Il
en existe toute une foule:

 * [RedBean](http://www.redbeanphp.com/)
 * [Doctrine](http://www.doctrine-project.org/)
 * [Eloquent ORM](http://laravel.com/docs/4.2/eloquent)
 * [etc.](https://en.wikipedia.org/wiki/List_of_object-relational_mapping_software#PHP)

```php
<?php // 03-redbean

require 'RedBean/rb.php';

// Connexion à la page de donnée.
R::setup("sqlite:../users.db");

// ...

// variable globale
$titre = "He-Arc";

// Contenu
if ("equipe" === $page) {
    $personne = R::load("personnes", $id);
    echo $twig->render("equipe.html", compact("titre", "personne"));
} else {
    $personnes = R::find("personnes");
    echo $twig->render("accueil.html", compact("titre", "personnes"));
}
```

## URL as UI

Les addresses des pages font partie de l'expérience utilisateur. Un utilisateur
doit être capable d'imaginer le contenu de la page en lisant l'URI.
Certainement, ce que vous faites avant de cliquer sur un lien.

Donc comment remplacer:

    /index.php?page=equipe&id=42

en quelque chose de plus parlant?

    /equipe/42

`42` quoi?

    /equipe/jean-bon

Un peu mieux! La personne avec l'identifiant `42` aura également un _slug_ unique
créé à partir de son nom, ici `jean-bon`. Pensez aux URLs de Wikipedia!

### Réécriture d'URL

La solution à notre problème est de demander au serveur web de réécrire les
URL pour nous.

Ainsi pour nos visiteurs:

    /equipe/jean-bon

Sera en réalité ceci pour PHP:

    /index.php/equipe/jean-bon

Apache le fait via
[`mod_rewrite`](https://httpd.apache.org/docs/current/mod/mod_rewrite.html) et
Nginx [`try_files`](http://nginx.org/en/docs/http/ngx_http_core_module.html#try_files).

Avec Apache2, ça donne ceci:

    // .htaccess
    RewriteEngine on
    RewriteBase /php-intro-framework/04-routes/

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L,QSA]


```php
<?php // 04-routes

// ...

// variables globales
$titre = "He-Arc";
$base = dirname($_SERVER["SCRIPT_NAME"]);

// Lecture de l'URL
list($uri) = explode("?", $_SERVER["REQUEST_URI"], 2);
// on ôte le prefix même que RewriteBase.
$uri = substr($uri, strlen($base));
// on match.
$matches = [];
if (preg_match("#^/(?<page>[^/]+)/(?<slug>[^/]+)/?#", $uri, $matches)) {
    $page = $matches["page"];
    $slug = $matches["slug"];
} else {
    $page = "accueil";
}

if ("equipe" === $action) {
    $personne = R::findOne("personnes", "slug = ?", [$slug]);
    echo $twig->render("personne.html", compact("base", "titre", "personne"));
} else {
    $personnes = R::find("personnes");
    echo $twig->render("accueil.html", compact("base", "titre", "personne"));
}
```

Ce `if` n'est pas très élégant. En créant des fonctions ayant le même nom que
nos pages, il est possible d'appeler directement la fonction via
[`call_user_func_array`](http://php.net/call_user_func_array). Voir:
[`04-routes/index.php`](04-routes/index.php).

```php
<?php // 04-routes

// ...

// variables globales
$titre = "He-Arc";
$base = dirname($_SERVER["SCRIPT_NAME"]);

// Lecture de l'URL
list($uri) = explode("?", $_SERVER["REQUEST_URI"], 2);
// on ôte le prefix qui est le même que RewriteBase.
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
    echo not_found();
}

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
```

### Routing

Ce que nous venons de créer est un système dit de _Routes_. Il va faire le lien
entre des adresses (URI) et des actions dans le code. Son nom est le _Front
Controller_.

En pratique, les actions ne sont pas des fonctions mises à plat mais sont
encapsulées dans une classe qu'on nomme un contrôleur. Faire ainsi permet
de regrouper logiquement les fonctions et éviter d'utiliser d'affreux éléments
tel que `global`.

## Modèle - Vue - Contrôleur

Et voilà, nous venons de créer un système:

 * Modèle: l'ORM qui s'occupe de notre base de données
 * Vue: les templates qui affiche les données
 * Contrôleur: une classe qui défini quoi faire en fonction des entrées
   utilisateur (URI, formulaire, etc.)

[MVC](https://fr.wikipedia.org/wiki/Mod%C3%A8le-vue-contr%C3%B4leur) vient des
applications bureau et ne représente pas toujours le fonctionnement dans le
monde du web. Par exemple, Django, un framework Python, se décrit comme étant
[Modèle - Template -
Vue](https://docs.djangoproject.com/en/1.8/faq/general/#django-appears-to-be-a-mvc-framework-but-you-call-the-controller-the-view-and-the-view-the-template-how-come-you-don-t-use-the-standard-names).

## Composer

Maintenir notre répertoire de `vendor` ainsi que les `require` est peu pratique.
Voici qu'entre en scène [Composer](https://getcomposer.org/), un gestionnaire
de paquets pour PHP. [Packagist](https://packagist.org/) est le dépôt central
où sont stockées les bibliothèques installables.

Le fichier `composer.json` à la racine du projet décrit les dépendances de
votre projet à l'aide de la clé `require`. Dans notre cas, nous avons besoin de
Twig et RedBean.

```json
{
    "require": {
        "twig/twig": "~1.24",
        "gabordemooij/redbean": "~4.3.2"
    }
}
```

Puis `composer` va les installer dans un répertoire `vendor`.

```
$ composer install
Loading composer repositories with package information
Updating dependencies (including require-dev)
  - Installing gabordemooij/redbean (v4.3.2)
    Loading from cache

  - Installing twig/twig (v1.24.1)
    Loading from cache

Writing lock file
Generating autoload files
```

Enfin, nous pouvons réduire le nombre de `require` et `include` à un seul, en
laissant soin à l'auto-loader de charger le bon fichier à la demande. Tout ceci
est spécifié dans [PSR-4](http://www.php-fig.org/psr/psr-4/). Ainsi, les
définitions de Twig sont présentes et il nous suffit d'obtenir la classe `R`
depuis RedBean.

```php
<?php
// Ne dites plus
require "../vendor/RedBean/rb.php";
require_once "../vendor/Twig/lib/Twig/Autoloader.php";

Twig_Autoloader::register();


// Mais dites plutôt
require "vendor/autoload.php";

use \RedBeanPHP\Facade as R;

```

Notez que nous n'utilisons plus le répertoire `vendor` global mais celui créé
par `composer`.

## Framework PHP

Les frameworks web en PHP (ou d'autres langages) reposent majoritairement sur
ces paradigmes et outils.

Un framework web est une collection de bibliothèques choisie et assemblée avec
un peu de glue. Il vous propose une structure de base pour construire selon une
méthode jugée bonne par ses concepteurs. Il est possible de remplacer un
composant par un autre, par le sien. Et même de créer sa glue ou même ses
outils propres.

### Lien avec Laravel

Je vous invite à aller lire le code généré pour vous par Laravel. Vous allez
retrouver ces éléments-là. Symfony, CakePHP, etc. auront les mêmes idées.
