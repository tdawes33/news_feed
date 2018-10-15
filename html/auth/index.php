<?php
session_start();
require_once '../../include/utils.php';
require_once SITE_ROOT . '/include/i18n.php';
require_once SITE_ROOT . '/include/auth.php';

$warning = '';

if ($authorized !== true && getPost('cmd') === 'signin') {

    if (getPost(ID) && getPost(PWD)) {
        if (validCredentials()) {
            if (isset($_SESSION[PROTECTED_LINK_ID]))
                header('Location: ' . $_SESSION[PROTECTED_LINK_ID]);
            else
                header('Location: ' . SITE . '/admin/');
            exit;
        } else {
            $warning = 'E メール、またはパスワードが無効です。';    //invalid email or password
        }
    } else {
        $warning = 'E メール/パスワードは必須項目です。'; //both fields required
    }
} else if (getRequest('signout')) {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure']);
    }
    session_destroy();
    header('Location: ' . SIGNOUT_REDIRECT);
    exit;
} 

if ($authorized === true) {
    header('Location: ' . SITE . '/admin/');
    exit;
}

$vars = array('%WARNING%'=>$warning);

require SITE_ROOT . '/include/head.php';
require SITE_ROOT . '/include/templates/nav.htm';
echo apply_template(file_get_contents(SIGNIN_TEMPLATE),  $vars);  
require SITE_ROOT . '/include/foot.php';


?>
