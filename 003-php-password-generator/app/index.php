<?php
function generateSelectOptions(int $selected = 10): string {
    $html = "";
    $options = range(8, 42);
    foreach ($options as $value) {
        $attribute = "";
        if ((int) $value == (int) $selected) {
            $attribute = "selected";
        }
        $html .= "<option $attribute value=\"$value\">$value</option>";
    }
    return $html;
}

function takeRandom(string $subject): string {
    $index = random_int(0, strlen($subject) - 1);
    $randomChar = $subject[$index];
    return $randomChar;
}

function generatePassword(int $size, bool $capitals, bool $smalls, bool $numbers, bool $symbols): string {
    $password = "";
    $sequences = [];
    if ($capitals == 1) {
        $sequences[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    if ($smalls == 1) {
        $sequences[] = "abcdefghijklmnopqrstuvwxyz";
    }
    if ($numbers == 1) {
        $sequences[] = "0123456789";
    }
    if ($symbols == 1) {
        $sequences[] = "!@#$%^&*()-_=+[ ]{}|\\\\;:'\"\\\",.<>/?`~";
    }
    if (empty($sequences)) {
        return "Please select at least one type.";
    }
    foreach ($sequences as $value) {
        $password .= takeRandom($value);
    }
    $limitBoucle = $size - count($sequences);
    if ($limitBoucle < 0) {
        $limitBoucle = 0;
    }
    for ($i = 1; $i < $limitBoucle; $i++) {
        $randomSequence = $sequences[random_int(0, (count($sequences) - 1))];
        $password .= takeRandom($randomSequence);
    }
    $password = str_shuffle($password);
    return $password;
}

$generated = "";
$size = $_POST["Size"] ?? 10;
$capitals = $_POST["Capitals"] ?? 0;
$smalls = $_POST["Smalls"] ?? 0;
$numbers = $_POST["Numbers"] ?? 0;
$symbols = $_POST["Symbols"] ?? 0;

//generatePassword($size, $capitals, $smalls, $numbers, $symbols);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generated = generatePassword($size, $capitals, $smalls, $numbers, $symbols);
} else {
    $size = 10;
    $capitals = 1;
    $smalls = 1;
    $numbers = 1;
    $symbols = 1;
}
$resultSelect = generateSelectOptions($size);
$isCapitalsChecked = $capitals == 1 ? "checked" : "";
$isSmallsChecked = $smalls == 1 ? "checked" : "";
$isNumbersChecked = $numbers == 1 ? "checked" : "";
$isSymbolsChecked = $symbols == 1 ? "checked" : "";
$page=<<<PAGE
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>
    <h1>Password generator</h1>
    <h2>Password</h2>
    <div class="passwordCont">$generated</div>
    <form method="POST" action="/index.php">
        <input type="checkbox" name="Capitals" id="Capitals" value="1" $isCapitalsChecked>
        <label for="Capitals">Capitals</label><br>
        <input type="checkbox" name="Smalls" id="Smalls" value="1"$isSmallsChecked>
        <label for="Smalls">Smalls</label><br>
        <input type="checkbox" name="Numbers" id="Numbers" value="1"$isNumbersChecked>
        <label for="Numbers">Numbers</label><br>
        <input type="checkbox" name="Symbols" id="Symbols" value="1"$isSymbolsChecked>
        <label for="Symbols">Symbols</label><br>
        <div>
            <label for="size" class="form-label">Size</label>
            <select class="form-select" aria-label="Default select example" name="Size">
                $resultSelect
            </select>
        </div
        <br>
        <button type="submit">Generate</button>
        </form>
  </body>
</html>
PAGE;

echo $page;