<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';
require_once 'inc/utils.inc.php';

$host = 'mysql';
$dbname = 'lowify';
$username = 'lowify';
$password = 'lowifypassword';

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

$allArtist = [];
try {
    $allArtist = $db->executeQuery(<<<SQL
    SELECT id, name, cover
    FROM artist
    SQL);
} catch (PDOException $e) {
    die ("Error during query: " . $e->getMessage());
}

$artistsListHtml = '<div class="d-flex flex-wrap justify-content-start">'; // Utilisation de Flexbox/Wrap
foreach ($allArtist as $artist) {
    $nameArt = $artist['name'];
    $coverArt = $artist['cover'];
    $idArt = $artist['id'];

    // Réutilisation de la structure de carte artiste (comme sur index.php)
    $artistsListHtml .= <<<HTML
<div class="carousel-card text-center" style="width: 220px; height: 320px;">
    <a href="artist.php?id=$idArt" class="text-decoration-none text-white text-center flex-grow-1 d-flex flex-column align-items-center justify-content-center">
        <img src="$coverArt" class="img-fluid rounded-circle mb-3 shadow-sm" style="width: 160px; height: 160px; object-fit: cover; border: 1px solid var(--lowify-pink-light);" alt="Artist Cover">
        
        <h4 class="fw-bold text-truncate" style="color: var(--lowify-pink-light); font-size: 1.2rem;">$nameArt</h4>
    </a>
</div>
HTML;
}
$artistsListHtml .= '</div>';

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
    <h2 class="text-white mt-3 mb-4 border-bottom border-3 pb-2 fw-light section-title" style="border-color: var(--lowify-pink-light) !important;">All Artists</h2>

    <div>$artistsListHtml</div>
</div>
HTML;

echo (new HTMLPage(title: "Lowify - Artists"))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])
    ->addContent($html)
    ->addStylesheet('/inc/style.css')
    ->render();