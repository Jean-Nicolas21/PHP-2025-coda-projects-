<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';
require_once 'inc/utils.inc.php';

$host = 'mysql';
$dbname = 'lowify';
$username = 'lowify';
$password = 'lowifypassword';
//Initialisation
$db = null;
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
        if ($artist['name'] === $query) { // === pour une comparaison stricte (avec la casse)
            $filteredArtist[] = $artist;
        }
    }
    // Si la recherche a trouvé des choses mais AUCUNE n'est une correspondance exacte, on vide le tableau.
    $artistSearch = $filteredArtist;
}

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
    // Si la recherche trouve 82 chansons, mais qu'AUCUNE n'est la chanson exacte, on vide le tableau.
    $songSearch = $filteredSongs;
}

$foundResult = false; // Initialisation (ou réinitialisation)

// 1. Priorité Album : Si un album est trouvé, on l'affiche exclusivement.
if (!empty($songSearch)) {
    $foundResult = true;
    $artistSearch = []; // Vide les autres tableaux pour l'exclusivité
    $albumSearch = [];
}
// 2. PRIORITÉ INTERMÉDIAIRE : ALBUM (si aucune chanson n'a été trouvée)
else if (!empty($albumSearch)) {
    $foundResult = true;
    $artistSearch = []; // Vide le tableau d'artiste pour l'exclusivité
    $songSearch = []; // Déjà vide
}
// 3. DERNIÈRE PRIORITÉ : ARTISTE (si ni chanson ni album)
else if (!empty($artistSearch)) {
    $foundResult = true;
}

// --- LOGIQUE DE REDIRECTION (si rien n'a été trouvé du tout) ---
if (!$foundResult) {
    header("Location: $error");
    exit;
}


$artistSearchHtml = '';
foreach ($artistSearch as $artist) {
    $coverArt = $artist['cover'];
    $nameArt = $artist['name'];
    $idArt = $artist['id'];

    // Utilisation de la carte artiste (même structure que index.php et artists.php)
    $artistSearchHtml .= <<<HTML
<div class="carousel-card text-center" style="width: 220px; height: 280px;">
    <a href="artist.php?id=$idArt" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center justify-content-center">
        <img src="$coverArt" class="img-fluid rounded-circle mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid var(--lowify-pink-light);" alt="Artist Cover">
        
        <h4 class="fw-bold text-truncate" style="color: var(--lowify-pink-light); font-size: 1.2rem;">$nameArt</h4>
    </a>
</div>
HTML;
}

$albumSearchHtml = '';
foreach ($albumSearch as $album) {
    $coverAlbum = $album['albumCover'];
    $nameAlbum = $album['albumName'];
    $idAlbum = $album['albumId'];
    $dateAlbum = $album['albumDate'];
    $formatedAlbumDate = displayDate($dateAlbum);
    $nameArtist = $album['artistName'];
    $idArtist = $album['artistId'];

    // Utilisation de la carte album (Image carrée)
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

$songSearchHtml = '<div class="d-flex flex-wrap justify-content-start">';
foreach ($songSearch as $song) {
    // Les variables sont maintenant disponibles grâce à la requête modifiée
    $coverAlbum = $song['albumCover'];
    $nameAlbum = $song['albumName'];
    $idAlbum = $song['albumId'];
    $nameArtist = $song['artistName'];
    $idArtist = $song['artistId'];
    $nameSong = $song['songName'];
    $formatedDuration = displayDuration($song['songDuration']);
    $noteSong = $song['songNote'];

    // Structure de carte pour l'affichage exclusif de la recherche
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

/*if (empty($artistSearch) && empty($albumSearch) && empty($songSearch)) {
    header("Location: $error");
    exit;
}*/

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
