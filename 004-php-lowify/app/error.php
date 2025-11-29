<?php
require_once 'inc/page.inc.php';

// Récupère le message d'erreur de l'URL, ou utilise un message par défaut
$errorMessage = $_GET['errorMessage'] ?? "Oops...error, the page could not be found.";

// --- HEADER COMMUN (avec la barre de recherche intégrée) ---
// Note: Cette variable doit être définie ou incluse. Je la définis ici pour l'exhaustivité.
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


// --- BLOC D'ERREUR STYLISÉ (avec la classe spécifique 'error-block-alert') ---
$errorBlockHtml = <<<HTML
<div class="text-center my-5 p-5 rounded-3 shadow-lg error-block-alert">
    <h1 class="fw-lighter mb-4 welcome-title-glow" style="font-size: 4rem; letter-spacing: 0.1em; color: #ff5555;">
        ⚠️ Oops something went wrong! ⚠️
    </h1>
    <p class="lead text-white" style="font-size: 1.5rem;">
        $errorMessage
    </p>
    <p class="text-white-50 mt-3">I'm feeling betrayed...Are you trying to leave this eden or break it?</p>
    <a href="index.php" class="btn btn-outline-light mt-4 error-button-fix">
        CLICK HERE TO LEAVE THIS WONDERFUL WEBSITE
    </a>
</div>
HTML;

$html =<<<HTML
$commonHeaderHtml
<div class="container py-4 d-flex justify-content-center"> 
    $errorBlockHtml
</div>
HTML;

echo (new HTMLPage(title: "Lowify - Error"))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])
    ->addContent($html)
    ->addStylesheet('/inc/style.css') // Assurez-vous que c'est le bon chemin
    ->render();