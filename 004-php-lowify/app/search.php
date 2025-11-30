<?php

/**
 * search.php
 * * Page de recherche principale de l'application Lowify.
 * Cette page gère la connexion à la base de données, l'exécution des requêtes
 * de recherche pour les artistes, albums et chansons, et l'affichage des
 * résultats avec une logique de priorité.
 */

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

$error = "error.php?errorMessage=It seems that what you're searching doesn't exist...";

$query = $_GET['query'] ?? '';

// --- RECHERCHE D'ARTISTE ---

$artistSearch = [];
try {
    $artistSearch = $db->executeQuery(<<<SQL
        SELECT *
        FROM artist
        WHERE (
        MATCH(name) AGAINST(:query IN NATURAL LANGUAGE MODE) OR
        name LIKE :query
        )
    SQL,["query" => $query]);
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

if (!empty($artistSearch)) {
    $filteredArtist = [];
    foreach ($artistSearch as $artist) {
        if ($artist['name'] === $query) {
            $filteredArtist[] = $artist;
        }
    }
    $artistSearch = $filteredArtist;
}

// --- RECHERCHE D'ALBUM ---

$albumSearch = [];
try {
    $albumSearch = $db->executeQuery(<<<SQL
        SELECT 
            al.name as albumName,
            al.cover as albumCover,
            al.release_date as albumDate,
            al.id as albumId,
            a.id as artistId,
            a.name as artistName
        FROM artist a
        INNER JOIN album al ON a.id = al.artist_id
        WHERE (
        MATCH(al.name) AGAINST(:query IN NATURAL LANGUAGE MODE) OR
        al.name LIKE :query
        )
    SQL,["query" => $query]);
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

if (!empty($albumSearch)) {
    $filteredAlbum = [];
    foreach ($albumSearch as $album) {
        if ($album['albumName'] === $query) {
            $filteredAlbum[] = $album;
        }
    }
    $albumSearch = $filteredAlbum;
}


// --- RECHERCHE DE CHANSON ---

$songSearch = [];
try {
    $songSearch = $db->executeQuery(<<<SQL
        SELECT 
            al.name as albumName,
            al.cover as albumCover,
            al.id as albumId,
            a.id as artistId,
            a.name as artistName,
            s.name as songName,
            s.note as songNote,
            s.duration as songDuration
        FROM artist a
        INNER JOIN album al ON a.id = al.artist_id
        INNER JOIN song s ON al.id = s.album_id
        WHERE (
        MATCH(s.name) AGAINST(:query IN NATURAL LANGUAGE MODE) OR
        s.name LIKE :query
        )
    SQL,["query" => $query]);
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

if (!empty($songSearch)) {
    $filteredSongs = [];
    foreach ($songSearch as $song) {
        if ($song['songName'] === $query) {
            $filteredSongs[] = $song;
        }
    }
    $songSearch = $filteredSongs;
}

// --- LOGIQUE DE PRIORITÉ ET D'EXCLUSIVITÉ ---

$foundResult = false;


if (!empty($songSearch)) {
    $foundResult = true;
    $artistSearch = [];
    $albumSearch = [];
}

else if (!empty($albumSearch)) {
    $foundResult = true;
    $artistSearch = [];
    $songSearch = [];
}

else if (!empty($artistSearch)) {
    $foundResult = true;
}

// --- LOGIQUE DE REDIRECTION (si rien n'a été trouvé du tout) ---
if (!$foundResult) {
    header("Location: $error");
    exit;
}


// --- GÉNÉRATION DU HTML DES CARTES D'ARTISTE ---

/** @var string $artistSearchHtml Contenu HTML généré pour l'affichage des cartes d'artiste. */
$artistSearchHtml = '';
foreach ($artistSearch as $artist) {
    // Extraction des données
    $coverArt = $artist['cover'];
    $nameArt = $artist['name'];
    $idArt = $artist['id'];

    $artistSearchHtml .= <<<HTML
<div class="carousel-card text-center" style="width: 220px; height: 280px;">
    <a href="artist.php?id=$idArt" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center justify-content-center">
        <img src="$coverArt" class="img-fluid rounded-circle mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid var(--lowify-pink-light);" alt="Artist Cover">
        
        <h4 class="fw-bold text-truncate" style="color: var(--lowify-pink-light); font-size: 1.2rem;">$nameArt</h4>
    </a>
</div>
HTML;
}

// --- GÉNÉRATION DU HTML DES CARTES D'ALBUM ---

/** @var string $albumSearchHtml Contenu HTML généré pour l'affichage des cartes d'album. */
$albumSearchHtml = '';
foreach ($albumSearch as $album) {
    $coverAlbum = $album['albumCover'];
    $nameAlbum = $album['albumName'];
    $idAlbum = $album['albumId'];
    $dateAlbum = $album['albumDate'];
    $formatedAlbumDate = displayDate($dateAlbum);
    $nameArtist = $album['artistName'];
    $idArtist = $album['artistId'];

    $albumSearchHtml .= <<<HTML
<div class="carousel-card text-center" style="width: 220px; height: 380px;">
    <a href="album.php?id=$idAlbum" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center mb-2">
        <img src="$coverAlbum" class="img-fluid rounded-3 mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid var(--lowify-pink-light);" alt="Album Cover">
        <h4 class="fw-bold text-truncate" style="color: var(--lowify-pink-light); font-size: 1.2rem;">$nameAlbum</h4>
    </a>
    
    <div class="d-flex flex-column pt-2 border-top border-secondary border-opacity-50 w-100 mt-auto">
        <p class="text-secondary small mb-1">$formatedAlbumDate</p>
        <a href="artist.php?id=$idArtist" class="text-white-50 small artist-link text-decoration-none">
            $nameArtist
        </a>
    </div>
</div>
HTML;
}

// --- GÉNÉRATION DU HTML DES CARTES DE CHANSON ---

$songSearchHtml = '<div class="d-flex flex-wrap justify-content-start">';
foreach ($songSearch as $song) {
    $coverAlbum = $song['albumCover'];
    $nameAlbum = $song['albumName'];
    $idAlbum = $song['albumId'];
    $nameArtist = $song['artistName'];
    $idArtist = $song['artistId'];
    $nameSong = $song['songName'];
    $formatedDuration = displayDuration($song['songDuration']);
    $noteSong = $song['songNote'];

    $songSearchHtml .= <<<HTML
<div class="carousel-card text-center" style="width: 200px; height: 320px;">
    <a href="album.php?id=$idAlbum" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center mb-2">
        <img src="$coverAlbum" class="img-fluid rounded-3 mb-2 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid var(--lowify-pink-light);" alt="Album Cover">
        
        <h4 class="fw-bold text-truncate" style="color: white; font-size: 1.1rem; margin-top: 5px;">$nameSong</h4>
        
        <p class="text-secondary small mb-1 mt-auto" style="color: var(--lowify-pink-darker) !important;">
            $nameArtist / $nameAlbum
        </p>
    </a>
    
    <div class="d-flex justify-content-between align-items-center w-100 border-top border-secondary border-opacity-50 pt-2 px-1 mt-1">
        <span class="text-secondary small">$formatedDuration</span>
        <span style="color: #ffc107; font-size: 1.1rem;">
            $noteSong&nbsp;⭐
        </span>
    </div>
</div>
HTML;
}
$songSearchHtml .= '</div>';

// --- COMPTAGE DES RÉSULTATS ET GESTION DES BLOCS D'AFFICHAGE ---

$artistCount = count($artistSearch);
$albumCount = count($albumSearch);
$songCount = count($songSearch);

$artistBlockHtml = '';
if ($artistCount > 0) {
    $artistBlockHtml = <<<HTML
        <h3 class="text-white mt-5 mb-3 fw-light">Artists found ({$artistCount})</h3>
        <div class="d-flex flex-wrap results-container artist-results">$artistSearchHtml</div>
    HTML;
}

$albumBlockHtml = '';
if ($albumCount > 0) {
    $albumBlockHtml = <<<HTML
        <h3 class="text-white mt-5 mb-3 fw-light">Albums found ({$albumCount})</h3>
        <div class="d-flex flex-wrap results-container album-results">$albumSearchHtml</div>
    HTML;
}

$songBlockHtml = '';
if ($songCount > 0) {
    $songBlockHtml = <<<HTML
        <h3 class="text-white mt-5 mb-3 fw-light">Songs found ({$songCount})</h3>
        <div class="results-container song-results">$songSearchHtml</div>
    HTML;
}

// --- GÉNÉRATION DE L'EN-TÊTE COMMUN ---

$commonHeaderHtml = <<<HEADER
<header class="bg-dark text-white mb-4 sticky-top p-3 animated-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        
        <a href="index.php" 
           class="text-decoration-none fw-lighter homepage-link" 
           style="color: #f8c4d6; font-size: 2rem; letter-spacing: 0.15em;" 
        > 
           Homepage 
        </a>

        <div class="position-absolute start-50 translate-middle-x">
            <h1 class="fw-lighter m-0 lowify-title" style="color: #f8c4d6; font-size: 2rem; letter-spacing: 0.15em; text-shadow: 0 0 5px rgba(248, 196, 214, 0.5);">
                Lowify
            </h1>
        </div>
        
        <div class="search-container">
            <form action="search.php" method="GET" class="d-flex">
                <input type="search" name="query" placeholder="Search..." class="form-control search-input" required>
                <button type="submit" class="btn search-button">🔍</button>
            </form>
        </div>
    </div>
</header>
HEADER;

// --- STRUCTURE HTML FINALE DE LA PAGE ---

$html =<<<HTML
$commonHeaderHtml

<div class="container py-4"> 
    <h2 class="text-white mt-3 mb-5 border-bottom border-3 pb-2 fw-light section-title" style="border-color: var(--lowify-pink-light) !important;">
        Results for: <span style="color: #f8c4d6; font-weight: bold;">"$query"</span>
    </h2>

    $artistBlockHtml
    $albumBlockHtml
    $songBlockHtml
</div>
HTML;

echo (new HTMLPage(title: "Lowify - Search result"))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])
    ->addContent($html)
    ->addStylesheet('/inc/style.css')
    ->render();