<?php

/**
 * index.php
 * * Page d'accueil de l'application Lowify.
 * Cette page affiche les sections principales : artistes populaires, albums récents
 * et albums populaires (les mieux notés), et sert de point de départ à la navigation.
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

$error = "error.php?errorMessage=Oops didn't catch the page...";

// -----------------------------------------------------------------------------
// --- 1. RÉCUPÉRATION DES ARTISTES POPULAIRES (Top 5 par auditeurs mensuels) ---
// -----------------------------------------------------------------------------

$popularArtist = [];
try {
    $popularArtist = $db->executeQuery(<<<SQL
       SELECT *
        FROM artist 
        ORDER BY monthly_listeners DESC
        LIMIT 5 
SQL);
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

// --- GÉNÉRATION DU HTML DES ARTISTES POPULAIRES ---

$popularArtistHtml = '<div class="d-flex flex-wrap">';
foreach ($popularArtist as $artist) {
    $nameArtist = $artist['name'];
    $coverArtist = $artist['cover'];
    $monthlyListeners = $artist['monthly_listeners'];
    $formatedListeners = displayListeners($monthlyListeners);
    $idArtist = $artist['id'];
    $popularArtistHtml .= <<<HTML
<div class="carousel-card text-center">
    <a href="artist.php?id=$idArtist" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center">
        <img src="$coverArtist" class="img-fluid rounded-circle mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid #f8c4d6;" alt="Artist Cover">
        
        <h4 class="fw-bold text-truncate" style="color: #f8c4d6; font-size: 1.2rem;">$nameArtist</h4>
    </a>
    
    <p class="text-secondary small mb-0 mt-auto pt-2 border-top border-secondary border-opacity-50 w-100 text-center">
        $formatedListeners 🎧
    </p>
</div>
HTML;
}
$popularArtistHtml .= '</div>';

// -----------------------------------------------------------------------------
// --- 2. RÉCUPÉRATION DES ALBUMS RÉCENTS (Top 5 par date de sortie) ---
// -----------------------------------------------------------------------------

$recentAlbum = [];
try {
    $recentAlbum = $db->executeQuery(<<<SQL
       SELECT 
           al.name as albumName,
           al.cover as albumCover,
           al.release_date as albumDate,
           al.id as albumId,
           a.name as artistName,
           a.id as artistId
        FROM album al
        INNER JOIN artist a ON al.artist_id = a.id
        ORDER BY release_date DESC
        LIMIT 5 
SQL);
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

// --- GÉNÉRATION DU HTML DES ALBUMS RÉCENTS ---

$recentAlbumHtml = '<div class="d-flex flex-wrap">';
foreach ($recentAlbum as $album) {
    $nameAlbum = $album['albumName'];
    $coverAlbum = $album['albumCover'];
    $releasedDate = $album['albumDate'];
    $formatedAlbumDate = displayDate($releasedDate);
    $albumId = $album['albumId'];
    $artistName = $album['artistName'];
    $artistId = $album['artistId'];
    $recentAlbumHtml .= <<<HTML
<div class="carousel-card text-center" style="width: 220px; height: 380px;">
    <a href="album.php?id=$albumId" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center mb-2">
        <img src="$coverAlbum" class="img-fluid rounded-3 mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid #f8c4d6;" alt="Album Cover">
        <h4 class="fw-bold text-truncate" style="color: #f8c4d6; font-size: 1.2rem;">$nameAlbum</h4>
    </a>
    
    <div class="d-flex flex-column pt-2 border-top border-secondary border-opacity-50 w-100 mt-auto">
        <p class="text-secondary small mb-1">$formatedAlbumDate</p>
        <a href="artist.php?id=$artistId" class="text-white-50 small artist-link text-decoration-none">
            $artistName
        </a>
    </div>
</div>
HTML;
}
$recentAlbumHtml .= '</div>';


// -----------------------------------------------------------------------------
// --- 3. RÉCUPÉRATION DES ALBUMS POPULAIRES (Top 5 par note moyenne) ---
// -----------------------------------------------------------------------------

$popularAlbum = [];
try {
    $popularAlbum = $db->executeQuery(<<<SQL
       SELECT 
           al.name as albumName,
           al.cover as albumCover,
           al.release_date as albumDate,
           al.id as albumId,
           a.name as artistName,
           a.id as artistId,
           ROUND(AVG(s.note),2) as songNoteAvg 
        FROM artist a
        INNER JOIN album al ON a.id = al.artist_id
        INNER JOIN song s ON al.id = s.album_id
        GROUP BY albumName, albumCover, albumDate, albumId, artistName, artistId
        ORDER BY songNoteAvg DESC
        LIMIT 5 
SQL);
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

// --- GÉNÉRATION DU HTML DES ALBUMS POPULAIRES ---

$popularAlbumHtml = '<div class="d-flex flex-wrap">';
foreach ($popularAlbum as $album) {
    $nameAlbum = $album['albumName'];
    $coverAlbum = $album['albumCover'];
    $releasedDate = $album['albumDate'];
    $formatedAlbumDate = displayDate($releasedDate);
    $albumId = $album['albumId'];
    $artistName = $album['artistName'];
    $artistId = $album['artistId'];
    $avgRating = $album['songNoteAvg'];

    $popularAlbumHtml .= <<<HTML
<div class="carousel-card text-center" style="width: 220px; height: 380px;">
    <a href="album.php?id=$albumId" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center mb-2">
        <img src="$coverAlbum" class="img-fluid rounded-3 mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid #f8c4d6;" alt="Album Cover">
        <h4 class="fw-bold text-truncate" style="color: #f8c4d6; font-size: 1.2rem;">$nameAlbum</h4>
    </a>
    
    <div class="d-flex justify-content-between align-items-end pt-2 border-top border-secondary border-opacity-50 w-100 mt-auto">
        <div class="d-flex flex-column align-items-start">
            <p class="text-secondary small mb-1">$formatedAlbumDate</p>
            <a href="artist.php?id=$artistId" class="text-white-50 small artist-link text-decoration-none">
                $artistName
            </a>
        </div>
        
        <div style="color: #f8c4d6; font-size: 1.1rem; text-align: right;"> 
            $avgRating&nbsp;⭐
        </div>
    </div>
</div>
HTML;
}
$popularAlbumHtml .= '</div>';

// --- GÉNÉRATION DES BLOCS HTML STATIQUES (Header, Bienvenue, Banner) ---

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

$welcomeBlockHtml = <<<HTML
<div class="text-center my-5 p-4 rounded-3 shadow-lg welcome-block-glow">
    <h1 class="fw-lighter mb-2 welcome-title-glow" style="font-size: 2.5rem; letter-spacing: 0.1em;"> 
        Welcome to Lowify
    </h1>
    <p class="lead text-white-50">
        The new eden for music's lover
    </p>
</div>
HTML;

$artistBannerHtml = <<<HTML
<a href="artists.php" class="text-decoration-none">
    <div class="my-5 p-4 rounded-3 shadow-lg text-center" style="background-color: #1a1a1a; border: 2px solid #f8c4d6;">
        <h2 class="fw-bold m-0" style="color: #f8c4d6; font-size: 2rem;">
            Explore All Artists 
        </h2>
    </div>
</a>
HTML;

// --- STRUCTURE HTML FINALE DE LA PAGE ---

$html =<<<HTML
$commonHeaderHtml

<div class="container py-4"> 
    <div>$welcomeBlockHtml</div>

    <div>$artistBannerHtml</div>

    <h2 class="text-white mt-5 mb-4 border-bottom border-3 pb-2 fw-light section-title" style="border-color: #f8c4d6 !important;">Popular Artists</h2>
    <div>$popularArtistHtml</div>

    <h2 class="text-white mt-5 mb-4 border-bottom border-3 pb-2 fw-light section-title" style="border-color: #f8c4d6 !important;">Recent Albums</h2>
    <div>$recentAlbumHtml</div>

    <h2 class="text-white mt-5 mb-4 border-bottom border-3 pb-2 fw-light section-title" style="border-color: #f8c4d6 !important;">Popular Albums</h2>
    <div>$popularAlbumHtml</div>
</div>
HTML;

// --- Rendu de la Page ---

echo (new HTMLPage(title: "Lowify - Welcome"))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])
    ->addContent($html)
    ->addStylesheet('/inc/style.css')
    ->render();