<?php
require_once SITE_ROOT . '/include/i18n.php';
require_once SITE_ROOT . '/include/auth.php';

define('DEFAULT_ADMIN_CMD', 'view');
define('DEFAULT_ADMIN_MODULE', 'news');
define('MODULE_INCLUDES', SITE_ROOT . '/include/templates');
define('MODULE_INCLUDE_FILE_EXT', 'inc.htm');
define('MODULE_FOOT_INCLUDE_FILE_EXT', 'foot-inc.htm');

if ($authorized !== true) {
    $_SESSION['PROTECTED_LINK_ID'] =   SITE . $_SERVER['REQUEST_URI'];
    header('Location: ' . SITE . '/'. AUTH_MODULE  .'/');
    exit;
}

?>
