<?php
/**
 * Project skriptid.
 * File: parser.php
 * User: roomet
 * Date: 22.08.2018 12:07
 */
require_once '_config.php';
require_once '_db.php';
require_once '_fx.php';
require_once 'MediaInfo.php';


if (!isCLI()) {
    Abi("VIGA! Skript on kasutatav vaid käsurealt.");
}

if ($argc != 2) {
    Abi();
}

$dirFile = $argv[1];

if (!is_readable($dirFile)) {
    Abi("VIGA! Ei suuda avada faili '{$dirFile}' lugemiseks.");
}

$fh = fopen($dirFile, 'r');
if ($fh === false) {
    Abi("VIGA! Ei suuda avada faili '{$dirFile}' lugemiseks.");
}

createTables();




/**
 * Rakenduse abiinfo. Vajadusel lisame ette ka veateate.
 *
 * @param string $message Veataeade vajadusel
 */
function Abi($message = '')
{
    if (!empty($message)) {
        echo NL . $message . NL . NL;
    }
    echo "Kasutamine:" . NL;
    echo TAB . "> php " . basename(__FILE__) . " mi-listing.txt" . NL;
    echo NL;
    die();
}

/**
 * KOntrollime, kas käivitati skripti käsurealt
 *
 * @return bool
 */
function isCLI()
{
    return php_sapi_name() == 'cli';
}

function fixFilePath($fileString)
{
    $fileString = str_replace('\\', '\\\\', $fileString);
    $fileString = str_replace("'", "\\'", $fileString);

    return $fileString;
}