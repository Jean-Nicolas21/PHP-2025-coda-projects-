<?php

// --- Fonction utilitaire pour adapter le format des listeners ---
function displayListeners(int $number): string {
$suffixes = ['','K', 'M'];
if ($number < 1000) {
return (string)$number;
}
$base = floor(log10($number) / 3);
$formatedValue = $number / pow(1000, $base);
$result = number_format($formatedValue, 1);
if (substr($result, -2) === '.0') {
$result = substr($result, 0, -2);
}
return $result . $suffixes[$base];
}

// --- Fonction utilitaire pour adapter le format de la durée des sons ---

function displayDuration(int $totalSecond): string {
    $minutes = floor($totalSecond / 60);
    $secondes = $totalSecond % 60;
    return sprintf('%02d:%02d', $minutes, $secondes);
}

// --- Fonction utilitaire pour adapter le format des dates des albums ---

function displayDate (string $date) : string {
    $format = "d/m/Y";
    $dateTimeObject = new DateTime($date);
    return $dateTimeObject->format($format);
}