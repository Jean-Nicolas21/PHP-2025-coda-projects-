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
$error = "error.php?errorMessage=Unknown Artist...";
$idArtist = $_GET['id'];
$allArtist = [];
try {
    $allArtist = $db->executeQuery(<<<SQL
        SELECT *
        FROM artist
        WHERE id = :artistId
    SQL,["artistId" => $idArtist]);
    if (sizeof($allArtist) == 0) {
        header("Location: $error");
    }
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

$songArtist = [];
try {
    $songArtist = $db->executeQuery(<<<SQL
        SELECT
            s.name as songName,
            s.note as songNote,
            s.duration as songDuration,
            a.cover as albumCover,
            a.id as albumId,
            a.name as albumName
        FROM album a
        INNER JOIN song s ON s.album_id = a.id
        WHERE a.artist_id = :artistId
        ORDER BY s.note DESC
        LIMIT 5
    SQL,["artistId" => $idArtist]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$allAlbum = [];
try {
    $allAlbum = $db->executeQuery(<<<SQL
        SELECT *
        FROM album
        WHERE artist_id = :artistId
        ORDER BY release_date DESC
SQL, ["artistId" => $idArtist]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$soloArtHtml = "";
$artist = $allArtist[0];
    $nameArtist = $artist['name'];
    $coverArtist = $artist['cover'];
    $biographyArtist = $artist['biography'];
    $monthlyListenersArtist = $artist['monthly_listeners'];
$formatedListeners = displayListeners($monthlyListenersArtist);


$soloArtHtml .= <<<HTML
<div class="row align-items-center mb-5 p-3 bg-dark shadow-lg rounded-3 border border-secondary" style="border-color: #f8c4d6 !important;">
    
    <div class="col-auto text-center">
        <img src="$coverArtist" class="img-fluid rounded-circle shadow-lg mb-2" style="width: 200px; height: 200px; object-fit: cover; border: 3px solid #f8c4d6;" alt="Artist Cover">
    </div>

    <div class="col">
        <h1 class="text-white fw-bold mb-1" style="font-size: 2.5rem;">$nameArtist</h1>
        
        <div class="d-flex align-items-center mb-3">
            <span class="text-white-50 small fw-bold">$formatedListeners 🎧 monthly listeners</span>
        </div>

        <p class="text-white-75 border-start border-3 ps-3 py-1" style="border-color: #f8c4d6 !important;">$biographyArtist</p>
    </div>
</div>
HTML;

$topSongHtml = '<div class="d-flex flex-wrap">';
foreach ($songArtist as $song) {
    $nameSong = $song['songName'];
    $noteSong = $song['songNote'];
    $formatedNote = number_format($noteSong, 2, '.', '');
    $durationSong = $song['songDuration'];
    $formatedDuration = displayDuration($durationSong);
    $coverAlbum = $song['albumCover'];
    $nameAlbum = $song['albumName'];
    $idAlbum = $song['albumId'];
    $topSongHtml .= <<<HTML
<div class="carousel-card">
    
    <a href="album.php?id=$idAlbum" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center mb-2">
        <img src="$coverAlbum" class="img-fluid rounded-3 mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid #f8c4d6;" alt="Album Cover">
        
        <h4 class="fw-bold mb-1" style="color: #f8c4d6;">$nameSong</h4>
        <p class="text-white-50 small mb-0">$nameAlbum</p>
    </a>
    
    <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-50 w-100 mt-auto">
        <span class="text-white-50 small">$formatedDuration</span>
        <div style="color: #f8c4d6; font-size: 1.1rem;"> 
            $formatedNote&nbsp;⭐
        </div>
    </div>
</div>
HTML;
}
$topSongHtml .= '</div>';

$allAlbumHtml = '<div class="d-flex flex-wrap">';
foreach ($allAlbum as $album) {
    $nameAlbum = $album['name'];
    $coverAlbum = $album['cover'];
    $dateAlbum = $album['release_date'];
    $formatedDateAlbum = displayDate($dateAlbum);
    $idAlbum = $album['id'];
    $allAlbumHtml .= <<<HTML
<div class="carousel-card">
    
    <a href="album.php?id=$idAlbum" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center mb-2">
        <img src="$coverAlbum" class="img-fluid rounded-3 mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid #f8c4d6;" alt="Album Cover">
        
        <h4 class="fw-bold" style="color: #f8c4d6;">$nameAlbum</h4>
    </a>
    
    <p class="text-secondary small mb-0 mt-auto pt-2 border-top border-secondary border-opacity-50 w-100 text-center">$formatedDateAlbum</p>
</div>
HTML;
}
$allAlbumHtml .= '</div>';

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
    <div>$soloArtHtml</div>

    <h2 class="text-white mt-5 mb-4 border-bottom border-3 pb-2 fw-light section-title" style="border-color: #f8c4d6 !important;">Trending songs</h2>
    <div>$topSongHtml</div>

    <h2 class="text-white mt-5 mb-4 border-bottom border-3 pb-2 fw-light section-title" style="border-color: #f8c4d6 !important;">Albums</h2>
    <div>$allAlbumHtml</div>
</div>
HTML;

echo (new HTMLPage(title: "Lowify - $nameArtist"))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])
    ->addContent($html)
    ->addStylesheet('/inc/style.css')
    ->render();
