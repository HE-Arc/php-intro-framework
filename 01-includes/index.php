<?php // 01-includes

// Lecture de la query string `page=<XX>&id=<YY>`.
$page = isset($_GET["page"]) ? $_GET["page"] : null;
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

// Connexion à la page de donnée.
$db = new PDO("sqlite:../users.db");

header("Content-Type: text/html; charset=utf-8");
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
