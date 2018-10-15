<?php
session_start();
require_once '../../include/utils.php';
require_once SITE_ROOT . '/include/i18n.php';
require_once SITE_ROOT . '/include/admin.php';
require_once SITE_ROOT . '/include/news.php';

//find command and id
foreach ($_REQUEST as $key=>$value) {
    if (($pos = strpos($key, EDIT_KEY_MARKER)) !== false && ($id = substr($key, $pos+strlen(EDIT_KEY_MARKER)))) {   //$pos should generally be 0 always
        //header('Location: ' . '/admin/?cmd=edit
        $cmd = 'edit';
    } else if (($pos = strpos($key, DELETE_KEY_MARKER)) !== false && ($id = substr($key, $pos+strlen(DELETE_KEY_MARKER)))) {
        $cmd = 'delete';
    }
}

?>
