<?php
#NOTE PDO requires php5

define('DATA_FILE', SITE_ROOT . '/data/site.db');
$db = NULL;

function open_db(&$sql_error) {
    global $db;
    return ($db = @sqlite_open(DATA_FILE, 0666, $sql_error));
}

function db_query($sql) {
    global $db;
    $sql_error = '';

    if (is_null($db) && !open_db($sql_error))
        return false;
    return @sqlite_query($db, $sql, SQLITE_NUM);
}

function db_get_row($res) {
    return @sqlite_fetch_array($res, SQLITE_NUM);
}

function db_get_rows($res) {
    return @sqlite_fetch_all($res, SQLITE_NUM);
}

function db_exec($sql) {
    global $db;
    $sql_error = '';

    if (is_null($db) && !open_db($sql_error))
        return false;

    return @sqlite_exec($db, $sql);
}

?>
