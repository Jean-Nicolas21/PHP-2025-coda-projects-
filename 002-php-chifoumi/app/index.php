<?php
$player = $_GET["player"] ?? "Veuillez sélectionner un choix";
$choicesPhp = ["pierre", "feuille", "ciseaux", "lezard", "spock"];
$rand_choice = array_rand(array_flip($choicesPhp), 1);
if ($player === "Veuillez sélectionner un choix") {
    $rand_choice = null;
}
//$playedGames= "";
//$nbrWinPlay
if ($player === $rand_choice) {
    $resultat = "Égalité";
} elseif ($player === "pierre" && $rand_choice === "ciseaux" || $rand_choice === "lezard") {
    $resultat = "Vous avez gagné";
} elseif ($player === "feuille" && $rand_choice === "pierre" || $rand_choice === "spock") {
    $resultat = "Vous avez gagné";
} elseif ($player === "ciseaux" && $rand_choice === "feuille" || $rand_choice === "lezard") {
    $resultat = "Vous avez gagné";
} elseif ($player === "lezard" && $rand_choice === "feuille" || $rand_choice === "spock") {
    $resultat = "Vous avez gagné";
} elseif ($player === "spock" && $rand_choice === "pierre" || $rand_choice === "ciseaux") {
    $resultat = "Vous avez gagné";
} else {
    $resultat = "Vous avez perdu";
}

if ($player === "Veuillez sélectionner un choix") {
    $resultat = "";
}


$html = <<<HTML
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pierre, Feuille, Ciseaux</title>
  </head>
  <body>
    <h1>Pierre, Feuille, Ciseaux</h1>
    <h2>Joueur 1</h2>
    <div class="joueurun">{$player}</div>
    <h2>PHP</h2>
    <div class="php">{$rand_choice}</div>
    <h2>Résultat</h2>
    <div class="resultat">{$resultat}</div>
    <a href="http://localhost:80/?player=pierre">Pierre</a>
    <a href="http://localhost:80/?player=feuille">Feuille</a>
    <a href="http://localhost:80/?player=ciseaux">Ciseaux</a>
    <a href="http://localhost:80/?player=lezard">Lézard</a>
    <a href="http://localhost:80/?player=spock">Spock</a>
    <a href="https://localhost/">Reset</a>
    <h2>Statistiques</h2>
    <div class="stats"></div>
  </body>
</html>
HTML;
echo $html;