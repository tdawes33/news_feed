<?php
require_once 'utils.php';

$_SESSION['lang'] = $locale = DEFAULT_LOCALE;

//determine language choice
if (($lang = getRequest('lang')) && in_array($lang, $translations)) {
	$_SESSION['lang'] = $locale = $lang;    //user requested
} else if (($lang = getRequest('lang')) && array_key_exists($lang, $translations_en)) {
    $lang = $translations_en[$lang];
	$_SESSION['lang'] = $locale = $lang;    //user requested
} else if (($lang = getSession('lang')) && in_array($lang, $translations)) {
	$locale = $lang;    //user session
} else if (($langs = getServer('HTTP_ACCEPT_LANGUAGE'))) {
    foreach (split(',', $langs) as $key=>$val) {    //user's browser
        if (in_array(substr($val, 0, 2), $translations)) {
            $_SESSION['lang'] = $locale = substr($val, 0, 2);
            break;
        }
    }
} else {
    $locale = $_SESSION['lang'] = DEFAULT_LOCALE;
}
putenv("LC_ALL=$locale");
 //.po & .mo files should be at $locale_dir/$locale/LC_MESSAGES/messages.{po,mo}
$domain = 'messages';   //file source default
setDomain($domain);

function resetDomain() {
    global $domain;

    bindtextdomain($domain, LOCALE_DIR);   //load from domain.mo
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
}

function setDomain($domain) {
    bindtextdomain($domain, LOCALE_DIR);   //load from domain.mo
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
}

?>
