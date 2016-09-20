# PHP Intro Framework

Une introduction aux frameworks PHP

## Contenu

* `00-base` application PHP basique
* `01-includes` séparation des logiques métiers/affichage
* `02-twig` utilisation de [Twig](http://twig.sensiolabs.org/) comme système de templates
* `03-redbean` utilisation de [RedBean](http://redbeanphp.com/) comme ORM
* `04-routes` réécritures d'URL avec `mod_rewrite`
* `05-composer` gestion des dépendences avec [Composer](https://getcomposer.org/)
* `06-fastroute` gestion des routes avec [FastRoute](https://github.com/nikic/FastRoute)
* `slides.md` présentation (construire via le `Makefile`)

## Emoji's

Afin d'activer les Emoji's.

1. Installer [xelatex-emoji](https://github.com/mreq/xelatex-emoji)
2. Placer les images [emojione](https://github.com/Ranks/emojione) à la racine
3. Adapter `xelatex-emoji`

```tex
% Déjà chargé dans pandoc
%\usepackage{amsmath}

% EmojiOne
%\providecommand{\xelatexemojipath}[1]{images/#1.png}
\providecommand{\xelatexemojipath}[1]{emojione-2.2.6/assets/png/#1.png}
```
