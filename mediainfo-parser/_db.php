<?php
/**
 * Project skriptid.
 * File: _db.php
 * User: roomet
 * Date: 22.08.2018 12:27
 */


function createTables()
{
    $creates = [
        'DROP TABLE IF EXISTS `filmid`',
        'CREATE TABLE IF NOT EXISTS `filmid` (`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `nimi` VARCHAR(255), `imdb` VARCHAR(10) ) ENGINE=MyISAM;',
        'DROP TABLE IF EXISTS `subs`',
        'CREATE TABLE IF NOT EXISTS `subs` ( `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY)ENGINE=MyISAM'

    ];
    $db      = DB();
    foreach ($creates as $create) {
        $db->query($create);
    }
}


/**
 * PDO singleton
 *
 * @return null|PDO
 */
function DB()
{
    static $dbh = null;
    if (empty($dbh)) {

        try {
            $dsn = sprintf('mysql:dbname=%s;host=%s;charset=%s', DB_NAME, DB_HOST, DB_CHARSET);
            $dbh = new PDO($dsn, DB_USER, DB_PASS);
        } catch (PDOException $e) {
            die('ERROR : Connection failed: ' . $e->getMessage());
        }
    }

    return $dbh;
}