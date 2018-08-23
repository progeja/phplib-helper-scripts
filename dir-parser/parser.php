<?php
/**
 * Project skriptid.
 * File: parser.php
 * User: roomet
 * Date: 21.08.2018 9:40
 */

define('NL', "\n");
define('TAB', "\t");

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
echo "SET NAMES 'utf8';" . NL;
echo "TRUNCATE TABLE `filetree`;" . NL;
while (($line = fgets($fh)) !== false) {
    // line by line
    $lineData = explode('--', $line, 2);
    $pi       = pathinfo(trim($lineData[1]));

    preg_match('/^(\w+)\:(.+)$/', $pi['dirname'], $mm);
    $path = str_replace('\\', '\\\\', $mm[2]);
    $path = str_replace("'", "\\'", $path);

    $SQL = sprintf("INSERT INTO `filetree` (`id`, `drive`, `path`, `file`, `size`) VALUES (NULL, '%s', '%s', '%s', '%s');" . NL,
        $mm[1], fixFilePath($mm[2]), fixFilePath($pi['basename']), trim($lineData[0]));
    //echo utf8_encode($SQL);
    echo $SQL;
}
fclose($fh);


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
    echo TAB . "> php " . basename(__FILE__) . " dir-listing.txt" . NL;
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