<?php // 01-includes

// Lecture de la query string `page=<XX>&id=<YY>`.
$page = isset($_GET["page"]) ? $_GET["page"] : null;
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

// Connexion à la page de donnée.
$db = new PDO("sqlite:../users.db");

include "templates/entete.html";

// Contenu
if ("equipe" === $page) {
    $query = $db->query("SELECT * FROM `personnes` WHERE `id` = ?");
    $query->execute([$id]);

    $personne = $query->fetch(PDO::FETCH_OBJ);

    include "templates/equipe.html";
} else {
    include "templates/accueil.html";
}

include "templates/pieddepage.html";