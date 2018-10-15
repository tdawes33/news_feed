<?php
define('DOC_ROOT',  $_SERVER['DOCUMENT_ROOT']);
define('SITE_ROOT',  dirname(DOC_ROOT));
require_once 'db.php';

define('INCLUDE_DIR',  SITE_ROOT . '/include');
define('TEMPLATE_DIR',  INCLUDE_DIR . '/templates/');

mb_internal_encoding("UTF-8");
//all available languages defined here (primary language codes only ...)
$translations = array('English'=>'en', '日本語'=>'ja');
$translations_en = array('English'=>'en', 'Japanese'=>'ja');    //for xml:lang
define('DEFAULT_LOCALE', $translations_en['Japanese']);                //TODO fix this site specific default
$locale_display = array('en'=>'English', 'ja'=>'Japanese');    //for xml:lang

define('PROTO', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://'));
define('DOMAIN', $_SERVER['SERVER_NAME']);
define('PORT', ':' . $_SERVER['SERVER_PORT']);
define('SITE', PROTO . DOMAIN . ((PORT === ':80') ? '' : PORT));

define('LOCALE_DIR', '../locale');
define('TIMEZONE', 'America/Los_Angeles');

define('AJAX_CONTENT', 'Content-type: application/json; charset=utf-8');
define('PAGE_CONTENT', 'Content-type: text/plain; charset=utf-8');

define('EMAIL_ADDRESS_MAX', 320);
define('PASSWORD_MAX', 12);

if (!defined('PATH_SEPARATOR')) {
    if (strpos($_ENV['OS'], 'Win') !== false)
        define('PATH_SEPARATOR', ';'); 
    else
        define('PATH_SEPARATOR', ':'); 
} 
$session_id = session_id();

function urlPath() {
    $proto = ($_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
    $path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

    if (strlen($path) == 0)
        $path = '/';
    else if (substr($path, -1, 1) != '/')
        $path .= '/';
        
    return $proto . $_SERVER['SERVER_NAME'] . $path;
}

//assume no country code
function rootDomain() {
    $domain_parts = explode('.', $_SERVER['SERVER_NAME']);
    return $domain_parts[count($domain_parts)-2] . '.' . $domain_parts[count($domain_parts)-1];
}

function isAjax() {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        return true;
    else
        return false;
}

function create_unique_key() {
    return sha1($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] .  time() . rand()); 
}

function apply_template($string, $vars) {
	foreach ($vars as $name=>$value)
		$string = preg_replace(('/'.$name.'/'), $value, $string);

    return $string;
}

function var_name(&$var, $scope=0)
{
    $old = $var;
    if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old)
        return $key; 
}

function is_mobile_browser(){
    $useragent=$_SERVER['HTTP_USER_AGENT'];
     
	 return (preg_match('/android|avantgo|blackberry|blazer|elaine|hiptop|ip(hone|od)|kindle|midp|mmp|mobile|o2|opera mini|palm( os)?|pda|plucker|pocket|psp|smartphone|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce; (iemobile|ppc)|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));

}

function getGlobal($collection, $name) { return isset($collection[$name]) ? $collection[$name] : ''; }
function getSession($name) { return getGlobal($_SESSION, $name); }
function getRequest($name) { return getGlobal($_REQUEST, $name); }
function getPost($name) { return getGlobal($_POST, $name); }
function getServer($name) { return getGlobal($_SERVER, $name); }
function getFileInfo($name) { return getGlobal($_FILES, $name); }

?>
