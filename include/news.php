<?php

define('REDIRECT_PAGE', 'home.htm');
define('HANDHELD_REDIRECT_PAGE', 'mobile/index.htm');

define('ALLOWABLE_TAGS', '<br /><br><a>');
define('IMAGE_DIR', '/news/images/');
define('MAX_IMAGE_SIZE', 2000000);
define('DATE_SIZE', '10');
define('MAX_TITLE', '100');
define('MAX_CONTENT', '50000');

$media_types = array('SCREEN'=>'screen', 'HANDHELD'=>'handheld');
define('MEDIA_TYPES', serialize($media_types));
define('MEDIA_DEFAULT', $media_types['SCREEN']);

$jpg_ext = array('jpg', 'jpeg');
define('JPG_EXT', serialize($jpg_ext));
$img_types = array_merge(array('png', 'ping', 'gif'), $jpg_ext);
define('IMG_TYPES', serialize($img_types));

define('IMAGEMAGICK_PATH', '/usr/local/bin/');
define('IMG_WIDTH_MAX', '224');
define('IMG_HEIGHT_MAX', '150');

define('LIST_DEFAULT', 5);
define('LIST_LIMIT', 40);

define('EDIT_KEY_MARKER', 'edit_');
define('DELETE_KEY_MARKER', 'delete_');

function news_strip_tags($string) {
    return strip_tags($string , ALLOWABLE_TAGS);
}


?>
