<?php

// --- Inclusions des dépendances ---

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';
require_once 'inc/utils.inc.php';

// --- Configuration de la base de données ---

$host = 'mysql';
$dbname = 'lowify';
$username = 'lowify';
$password = 'lowifypassword';

$db = null;

// --- Tentative de connexion à la base de données ---

try {
    $db = new DatabaseManager(
        dsn: "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        username: $username,
        password: $password
    );
} catch (PDOException $e) {
    die ("Connection failed: " . $e->getMessage());
}

$error = "error.php?errorMessage= Oops error...";


// --- Recupération du liked song ---

$likedSong = $_GET['id'] ?? null;
$currentLiked = null;
$likedSongArray = [];
try {
    $likedSongArray = $db->executeQuery(<<<SQL
        SELECT 
            song.is_liked
        FROM song
        WHERE id = :likedSong
    SQL,["likedSong" => $likedSong]);
    if (sizeof($likedSongArray) == 0) {
        header("Location: $error");
        exit;
    }
    $currentLiked = (int)$likedSongArray[0]['is_liked'];
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}
// --- Inversion du champ is_liked ---
$newLiked = 1 - $currentLiked;


// --- Mise à jour de le DB avec la nouvelle valeur pour le champ is_liked ---
try {
    $db->executeQuery(<<<SQL
UPDATE song
SET is_liked = :newLiked
WHERE id = :likedSong
SQL,[
    "newLiked" => $newLiked,
    "likedSong" => $likedSong]);
} catch (PDOException $e) {
    header("Location: $error");
    exit;
}
header('Location: ' . $_SERVER['HTTP_REFERER']);