<?php // 01-includes

// Lecture de la query string `page=<XX>&id=<YY>`.
$page = $_GET["page"] ?? null;
$id = (int) ($_GET["id"] ?? 0);

// Connexion à la base de données.
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
