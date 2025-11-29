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

$error = "error.php?errorMessage=Unknown Album...";
$idAlbum = $_GET['id'];

$allAlbum = [];
try {
    $allAlbum = $db->executeQuery(<<<SQL
        SELECT 
            al.name as albumName,
            al.cover as albumCover,
            al.release_date as albumDate,
            al.artist_id,
            al.id as albumId,
            a.name as artistName,
            a.biography as artistBio,
            a.monthly_listeners as artistListeners,
            a.id as artistIdReal,
            s.artist_id,
            s.album_id,
            s.name as songName,
            s.duration as songDuration,
            s.note as songNotes
        FROM artist a
        INNER JOIN album al ON a.id = al.artist_id
        INNER JOIN song s ON al.id = s.album_id
        WHERE al.id = :albumId
        ORDER BY s.id ASC
    SQL,["albumId" => $idAlbum]);
    if (sizeof($allAlbum) == 0) {
        header("Location: $error");
    }
} catch (PDOException $e) {
    header("Location: $error");
    die("Connection failed: " . $e->getMessage());
}

$infoAlbumArt = "";
$header = $allAlbum[0];
    $nameAlbum = $header['albumName'];//ok
    $nameArtist = $header['artistName'];//ok
    $bioArtist = $header['artistBio'];//ok
    $idArtist = $header['artistIdReal'];//ok
    $listenersArtist = $header['artistListeners'];
    $formatedListeners = displayListeners($listenersArtist);//ok
    $coverAlbum = $header['albumCover'];
    $dateAlbum = $header['albumDate'];
    $formatedDateAlbum = displayDate($dateAlbum);

$infoAlbumArt .= <<<HTML
<div class="row align-items-center mb-5 p-3 bg-dark shadow-lg rounded-3 border border-secondary">
    
    <div class="col-auto text-center">
        <img src="$coverAlbum" class="img-fluid rounded-circle shadow-lg mb-2" style="width: 200px; height: 200px; object-fit: cover; border: 3px solid #f8c4d6;" alt="Album Cover">
        <p class="text-secondary small mt-2">Released: $formatedDateAlbum</p>
    </div>

    <div class="col">
        <h1 class="text-white fw-bold mb-1">$nameAlbum</h1>
        
        <div class="d-flex align-items-center mb-3">
            <a href="artist.php?id=$idArtist" 
               class="text-decoration-none me-4 fw-bold"
               style="color: #f8c4d6;" 
               onmouseover="this.style.color='#ffe6f0'; this.style.textDecoration='underline';" 
               onmouseout="this.style.color='#f8c4d6'; this.style.textDecoration='none';"
            >
                <h3 class="fw-normal mb-0">$nameArtist</h3>
            </a>
            <span class="text-white-50 small">$formatedListeners 🎧 per month</span>
        </div>

        <p class="text-white-75 border-start border-3 ps-3 py-1" style="border-color: #f8c4d6 !important;">$bioArtist</p>
    </div>
</div>
HTML;

$songAlbum = "";
$index = 0;
$songAlbum .= '<ul class="list-group list-group-flush bg-transparent mt-4">';
foreach ($allAlbum as $index => $album) {
    $trackNumber = $index + 1;
    $nameSong = $album['songName'];
    $durationSong = $album['songDuration'];
    $formatedDuration = displayDuration($durationSong);
    $noteSong = $album['songNotes'];
    $formatedNote = number_format($noteSong, 2, '.', '');
    $songAlbum .= <<<HTML
<li class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white border-bottom border-secondary border-opacity-50 song-row"> 
    
    <div class="col-7 text-truncate"> 
        <span class="fw-light me-3 text-white-50">#$trackNumber</span> 
        <span class="fw-normal">$nameSong</span>
    </div>

    <div class="col-5 d-flex justify-content-end align-items-center"> 
        
        <span class="text-white-50 text-end me-4" style="width: 50px;">$formatedDuration</span>
        
        <div style="color: #f8c4d6; width: 40px; text-align: end;"> 
            $formatedNote&nbsp;⭐
        </div>
    </div>
</li>
HTML;
}

$songAlbum .= '</ul>';

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

$html = <<<HTML
$commonHeaderHtml

<div class="container py-4"> 
    $infoAlbumArt

    <h2 class="text-white mt-5 mb-3 border-bottom border-3 pb-2" style="border-color: #f8c4d6 !important;">Album tracks</h2> 

    <div>$songAlbum</div>
</div>

HTML;

echo (new HTMLPage(title: "Lowify - $nameAlbum"))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])
    ->addContent($html)
    //->addRawStyle($artistsCSS)
    ->addStylesheet('/inc/style.css')
    ->render();