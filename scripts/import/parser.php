<?php

require_once 'WordTranslation.php';

$counter = 0;
$html = include 'content-to-parse.php';
$dict = [];

$htmlArray = findHtmlVocabulary($html);
foreach ($htmlArray as $htmlRow) {
    $dict[] = extractDictEntryFromHtmlRow($htmlRow);
}

//storeTestFile('test.txt');

function storeTestFile(string $testFile): void
{
    $data = file_get_contents('https://s.learnnavi.org/audio/vocab/5788.mp3');
    //$data = 'content';
    if ($data !== false) {
        file_put_contents($testFile, $data);
    } else {
        echo "no content";exit();
    }
}

function displayDictionary(array $dict, string $classification = null): void
{
    echo
        '<table><theader>'.
        '<th>Na\'vi</th>'.
        '<th>Classification</th>'.
        '<th>English</th>'.
        '<th>Phonetic</th>'.
        '</theader><tbody>';
    foreach ($dict as $wordTranslation) {
        $displayAllowed = true;
        if (!empty($classification)) {
            $displayAllowed = $classification == $wordTranslation->classification;
        }
        if ($displayAllowed) {
            displayWordTranslation($wordTranslation);
        }
    }
    echo '</tbody></table>';
}

function displayWordTranslation(WordTranslation $wordTranslation): void
{
    echo
        '<tr>'.
        '<td><a href="./assets/mp3/'.$wordTranslation->mp3File.'" onclick="window.open(this.href,this.target,\'width=550px,height=350px\');return false">'.$wordTranslation->word.'</a></td>'.
        '<td>'.$wordTranslation->classification.'</td>'.
        '<td>'.$wordTranslation->translation.'</td>'.
        '<td>'.$wordTranslation->phonetic.'</td>'.
        '</tr>'."\n"
    ;
}

function extractDictEntryFromHtmlRow(string $htmlRow): WordTranslation
{
    $word = new WordTranslation();
    $word->word = findWord($htmlRow);
    $word->mp3File = findMp3File($htmlRow);
    $word->phonetic = findPhonetic($htmlRow);
    $word->translation = findTranslation($htmlRow);
    $word->classification = findClassification($htmlRow);

    return $word;
}

function findWord(string $htmlRow): string
{
    $word = '';
    $startPosition = strpos($htmlRow, ';return false">');
    $endPosition = strpos($htmlRow, '</a></span>');
    if ($startPosition !== false && $endPosition !== false) {
        $startPosition += strlen(';return false">');
        $word = substr($htmlRow, $startPosition, $endPosition - $startPosition);
    }
    return $word;
}

function findMp3File(string $htmlRow): string
{
    $mp3File = '';
    $startPosition = strpos($htmlRow, 'https://s.learnnavi.org/audio/vocab/');
    $endPosition = strpos($htmlRow, '" onclick="window.open');
    if ($startPosition !== false && $endPosition !== false) {
        $startPosition += strlen('https://s.learnnavi.org/audio/vocab/');
        $mp3File = substr($htmlRow, $startPosition, $endPosition - $startPosition);
    }
    //storeMp3File($mp3File);
    return $mp3File;
}

/*function storeMp3File(string $mp3File): void
{
    global $counter;
    if (!file_exists($mp3File)) {
        if ($counter <= 1500) {
            $data = file_get_contents('https://s.learnnavi.org/audio/vocab/' . $mp3File);
            if ($data !== false) {
                file_put_contents($mp3File, $data);
            }
        }
    }
}*/

function findPhonetic(string $htmlRow): string
{
    $phonetic = '';
    $startPosition = strpos($htmlRow, '</a></span> [');
    $endPosition = strpos($htmlRow, '] <em>');
    if ($startPosition !== false && $endPosition !== false) {
        $startPosition += strlen('</a></span> [');
        $phonetic = substr($htmlRow, $startPosition, $endPosition - $startPosition);
    }
    return $phonetic;
}

function findTranslation(string $htmlRow): string
{
    $translation = '';
    $startPosition = strpos($htmlRow, '</em>');
    if ($startPosition !== false) {
        $startPosition += strlen('</em>');
        $translation = trim(substr($htmlRow, $startPosition));
    }
    return $translation;
}

function findClassification(string $htmlRow): string
{
    $classification = '';
    $startPosition = strpos($htmlRow, '<em>');
    $endPosition = strpos($htmlRow, '</em>');
    if ($startPosition !== false && $endPosition !== false) {
        $startPosition += strlen('<em>');
        $classification = substr($htmlRow, $startPosition, $endPosition - $startPosition);
    }
    return $classification;
}

/**
 * Convert an HTML string into an array of HTML lines
 * Additionally it removes extra content
 */
function findHtmlVocabulary($html): array
{
    $htmlArray = explode('<br />', $html);
    foreach ($htmlArray as $key => $htmlRow) {
        // Removes first paragraph of each new letter
        $position = strpos($htmlRow, '</p>');
        if ($position !== false) {
            $htmlRow = substr($htmlRow, $position + strlen('</p>'));
        }

        // Removes extra HTML content by starting string at the first span
        $position = strpos($htmlRow, '<span');
        if ($position !== false) {
            $htmlRow = substr($htmlRow, $position);
        }

        // Removes extra HTML content by ending before other unexpected HTML tags
        $position = strpos($htmlRow, '</div');
        if ($position !== false) {
            $htmlRow = substr($htmlRow, 0, $position);
        }

        $htmlArray[$key] = trim($htmlRow);
    }
    return $htmlArray;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dictionary Na'vi / English</title>
    <style>
body {
    background-color: #333;
    color: #fff;
}
main {
    width: 800px;
    margin: auto;
}
table {
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid;
}
td {
    padding: 4px 8px;
}
a {
    color: #a8c7fa;
    text-decoration: underline;
}
    </style>
</head>
<body>

    <main>
        <?php displayDictionary($dict); ?>
    </main>

</body>
</html>