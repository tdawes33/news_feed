<?php
session_start();
require_once '../../../include/utils.php';
require_once SITE_ROOT . '/include/i18n.php';
require_once SITE_ROOT . '/include/news.php';

$news = true;
$res = $rows = NULL;
$output = '';

$scrolling = getRequest('scrolling');
$media_types = unserialize(MEDIA_TYPES);
if (!($media = strtolower(getRequest('media'))) || !in_array($media, $media_types))
    $media = MEDIA_DEFAULT;

if (ctype_digit($count = getRequest('count')) && $count)
    $count = ($count < LIST_LIMIT) ? $count : LIST_LIMIT;
else
    $count = LIST_DEFAULT;

$sql = "select news.date, news_l10n.title, news.id from news, news_l10n where news.id=news_l10n.news_id and news_l10n.locale='{$locale}' order by news.date desc limit {$count}";
if (!($res = db_query($sql)) || !($rows = db_get_rows($res)))
    $news = false;

require SITE_ROOT . '/include/head_list.php';
require SITE_ROOT . '/include/templates/list_open.htm';

if ($news) {
    foreach ($rows as $i=>$row) {
        $date = $row[0];
        $title = strip_tags($row[1]);
        $id = $row[2];

        if ($media !== $media_types['HANDHELD'])
            $url = '/news/' . str_replace('-', '/', $date) . '/' . urlencode($title) . (($media !== MEDIA_DEFAULT) ? '?media=' . $media : '');
        else
            $url = "/news/index.php?id={$id}&media={$media}";

        $vars = array('%DATE%'=>$date, '%TITLE%'=>$title, '%URL%'=>$url, '%COUNT%'=>$i);
        $output .= apply_template(file_get_contents(SITE_ROOT . '/include/templates/news_list.htm'),  $vars);  //expand provided variables in template string
    }
    echo $output;
} else {
    echo 'No news';
}

require SITE_ROOT . '/include/foot_list.php';
?>

