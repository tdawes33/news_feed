<?php
session_start();
require_once '../../include/utils.php';
require_once SITE_ROOT . '/include/i18n.php';
require_once SITE_ROOT . '/include/news.php';

$media_types = unserialize(MEDIA_TYPES);
if (!($media = strtolower(getRequest('media'))) || !in_array($media, $media_types))
    $media = MEDIA_DEFAULT;

if (($id = getRequest('id'))) {
    $id = sqlite_escape_string($id);
    if ($media !== $media_types['HANDHELD']) {
        $sql = "select news.date, news_l10n.title from news, news_l10n " .
                "where news.id={$id} and news.id=news_l10n.news_id and news_l10n.locale='{$locale}'";
        if (($res = db_query($sql)) && ($row = db_get_rows($res))) {
            header('Location: ' . SITE . '/news/' . str_replace('-', '/', $row[0][0]) . '/' . urlencode($row[0][1]));
        } else {
            redirect_to_latest();
        }
    } else {
        $sql = "select news.date, news_l10n.title, news_l10n.content, news_l10n.image, news.id from news, news_l10n " .
            "where news.id={$id} and news.id=news_l10n.news_id and news_l10n.locale='{$locale}'";
    }
} else if (($year = getRequest('year')) && ($month = getRequest('month')) && 
        ($day = getRequest('day')) && ($title = getRequest('title'))) { 
    $date = sqlite_escape_string($year . '-' . $month . '-' . $day);
    $title = sqlite_escape_string(urldecode($title));
    $sql = "select news.date, news_l10n.title, news_l10n.content, news_l10n.image, news.id from news, news_l10n " .
            "where news.date='{$date}' and news_l10n.title='{$title}' and news.id=news_l10n.news_id and " .
            "news_l10n.locale='{$locale}'";
} else {
    redirect_to_latest();
}

$date = $title = $image = $content = '';
$width = $height = '0';

if (!($res = db_query($sql))) {
    header('Location: ' .  SITE . '/' . (($media === $media_types['SCREEN']) ? REDIRECT_PAGE : HANDHELD_REDIRECT_PAGE));
} else {
    if (!($row = db_get_rows($res))) {
        redirect_to_latest();
    } else {
        $date = str_replace('-', '/', $row[0][0]);
        $title = strip_tags($row[0][1]);
        $content = news_strip_tags($row[0][2]);
        if (($image = $row[0][3]) !== 'NULL' && strlen($image)) {
            $image = IMAGE_DIR . $image;
            $size = getimagesize(DOC_ROOT . $image);
            $width = $size[0];
            $height = $size[1];
            $display = 'display: inline;';
        } else {
            $image = '.';
            $display = 'display: none;';
        }
    }
}

$vars = array('%DATE%'=>$date, '%TITLE%'=>$title, '%CONTENT%'=>$content, '%IMAGE%'=>$image, 
    '%WIDTH'=>$width, '%HEIGHT%'=>$height, '%DISPLAY%'=>$display);

if ($media === 'screen') {
    require SITE_ROOT . '/include/head.php';
    require SITE_ROOT . '/include/templates/nav.htm';
    echo apply_template(file_get_contents(SITE_ROOT . '/include/templates/news.htm'),  $vars);  //expand provided variables in template string
    require SITE_ROOT . '/include/foot.php';
} else if  ($media === $media_types['HANDHELD']) {
    require SITE_ROOT . '/include/handheld_head.php';
    echo apply_template(file_get_contents(SITE_ROOT . '/include/templates/handheld_news.htm'),  $vars);  //expand provided variables in template string
    require SITE_ROOT . '/include/handheld_foot.php';
}

function redirect_to_latest() {
    global $locale, $media;
    $row = $res = NULL;

    //avoid infinit redirect
    if (getSession('redirect')) {
        unset($_SESSION['redirect']);
        header('Location: ' .  SITE . '/' . (($media === $media_types['SCREEN']) ? REDIRECT_PAGE : HANDHELD_REDIRECT_PAGE));
    } else {
        $_SESSION['redirect'] = true;
    }

    $sql = "select news.date, news_l10n.title, news.id from news, news_l10n " .
            "where news.date=(select max(date) from news) and news.id=news_l10n.news_id and news_l10n.locale='{$locale}'";
    if (($res = db_query($sql)) && ($row = db_get_rows($res))) {

        if ($media === $media_types['SCREEN'])
            header('Location: ' . SITE . '/news/' . str_replace('-', '/', $row[0][0]) . '/' . urlencode($row[0][1]));
        else
            header('Location: ' . SITE . "/news/index.php?id={$row[0][2]}&media={$media_types['HANDHELD']}");
    } else {
        header('Location: ' .  SITE . '/' . (($media === $media_types['SCREEN']) ? REDIRECT_PAGE : HANDHELD_REDIRECT_PAGE));
    }
}

?>
