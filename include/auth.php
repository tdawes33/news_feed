<?php
require_once 'vendor/openwall/PasswordHash.php'; //use relative path for cmd-line script

define('AUTH_MODULE', 'auth');
define('SIGNIN_TEMPLATE', TEMPLATE_DIR . AUTH_MODULE . '/signin.html');
define('SIGNOUT_REDIRECT', SITE . '/home.htm');
define('PROTECTED_LINK_ID', 'protected_link');
define('ID', 'username');
define('PWD', 'password');
define('HASH_ITERATION', 8);

$email = '';

if (!is_bool(($authorized = getSession('authorized'))) || !($email = getSession('email'))) {
    $_SESSION['authorized'] = $authorized = false;
    if (isset($_SESSION['email']))
        unset($_SESSION['email']);
} else {
    $email = $_SESSION['email'];    //make username available
}

function validCredentials() {
    $email = $password = '';
    $res = $row = NULL;

    if (!($email = sqlite_escape_string(getPost(ID))) || !($password = sqlite_escape_string(getPost(PWD)))) {
        return false;
    } else {

        $sql = "select email, password_digest from user where email = '{$email}'";
        if (!($res = db_query($sql)) || !($row = db_get_rows($res)))
            return false;

        $email = $row[0][0];
        $hash = $row[0][1];
        $hasher = new PasswordHash(HASH_ITERATION, FALSE);
        if ($hasher->CheckPassword($password, $hash)) {
            $_SESSION['authorized'] = $authorized = true;
            $_SESSION['email'] = $email;
        } else {
            $_SESSION['authorized'] = $authorized = false;
            if (isset($_SESSION['email']))
                unset($_SESSION['email']);
        }

        return $authorized;
    }
}

function get_random_bytes($count) {
    $output = '';

    if (is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
        $output = fread($fh, $count);
        fclose($fh);
    }

    if (strlen($output) < $count) {
        $output = '';
        for ($i = 0; $i < $count; $i += 16) {
            $this->random_state = md5(microtime() . $this->random_state);
            $output .= pack('H*', md5($this->random_state));
        }
        $output = substr($output, 0, $count);
    }

    return $output;
}

function hash_password($password) {
}
				
?>
