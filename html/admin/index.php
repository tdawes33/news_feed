<?php
session_start();
require_once '../../include/utils.php';
require_once SITE_ROOT . '/include/admin.php';
require_once SITE_ROOT . '/include/news.php';

$id = NULL;
$module = $cmd = '';
$date = $title = $content = $filename =  '';
$image =  '.';
$width = $height = '0';
$message = $warning = '';
$news = true;

//TODO create admin module for display list of modules
if (!($module = getRequest('module')))
    $module = DEFAULT_ADMIN_MODULE;
if (!($cmd = getRequest('cmd')))
    $cmd = DEFAULT_ADMIN_CMD;

//TODO move this script to /include/di/news/admin/[edit.php|add.php|etc.]
if ($module === 'news') {

    if (getRequest('add')) {
        $cmd = 'add';
    } else {
        if (($id = getRequest('edit'))) {
            $cmd = 'edit';
        } else if (($id = getRequest('delete'))) {
            $cmd = 'delete';
        } else if (($id = getRequest('confirm_delete'))) {
            $cmd = 'confirm_delete';
        }
    }

    if ($cmd === 'update' || $cmd === 'add' || ($cmd === 'edit' && $id) || ($cmd === 'delete' && $id) || ($cmd === 'confirm_delete' && $id)) {

        if ($cmd === 'edit' || $cmd === 'update') {

            if ($cmd === 'update') {

                $id = sqlite_escape_string(getRequest('id'));
                validateText($module, $email);
                $date = strip_tags(sqlite_escape_string(getRequest('date')));
                $title = strip_tags(sqlite_escape_string(getRequest('title')));
                $content = news_strip_tags(sqlite_escape_string(getRequest('content')));
                $image = 'NULL';

                checkDuplicate($date, $title, $module, $email, $id);

                if (($file = getFileInfo('image')) && ($filename = $file['name'])) {
                    //new image
                    if (!($file_error = $file['error'])) {
                        if ($file['size'] > MAX_IMAGE_SIZE)
                            sendNotice($module, 'update', $email, array('%WARNING%'=>'イメージサイズがリミットを超えています。' . ' - 2MB'), 'edit');

                        $path_parts = pathinfo(basename($filename));
                        $ext = strtolower($path_parts['extension']);

                        $image = create_unique_key() . '.' . $ext;
                        $newfile = DOC_ROOT . IMAGE_DIR . $image;

                        handleUpload($module, $email, $file['tmp_name'], $newfile);  //sends warning/exits on error, or installs file

                        $sql = "select news_l10n.image from news, news_l10n " .
                                "where news.id={$id} and news.id=news_l10n.news_id and news_l10n.locale='{$locale}'";
                        if (!($res = db_query($sql)) || !($row = db_get_row($res)))
                            sendCreateNotice($module, $email, '処理中にエラーが発生しました。');

                        if (($old_image = $row[0]) !== 'NULL' && strlen($old_image) &&
                                is_writable(DOC_ROOT . IMAGE_DIR . $old_image)) {
                            unlink(DOC_ROOT . IMAGE_DIR . $old_image); //image is being replaced
                        }


                    } else if ($file_error == UPLOAD_ERR_INI_SIZE || $file_error == UPLOAD_ERR_FORM_SIZE) { //di php.ini upload_max_filesize 2M
                        sendNotice($module, 'update', $email, array('%WARNING%'=>'イメージサイズがリミットを超えています。' . ' - 2MB'), 'edit');
                    } else {
                        sendNotice($module, 'update', $email, array('%WARNING%'=>'イメージアップロード中にエラーが発生しました。'), 'edit');
                    }
                } else if (getRequest('delete_image') === 'on') {
                    $sql = "select news_l10n.image from news, news_l10n " .
                            "where news.id={$id} and news.id=news_l10n.news_id and news_l10n.locale='{$locale}'";
                    if (($res = db_query($sql)) && ($row = db_get_row($res))) {
                        if (($old_image = $row[0]) !== 'NULL' && strlen($old_image) &&
                                is_writable(DOC_ROOT . IMAGE_DIR . $old_image)) {
                            unlink(DOC_ROOT . IMAGE_DIR . $old_image); //image is being replaced
                        }
                    }
                }

                if ($image !== 'NULL' || getRequest('delete_image'))
                    $sql = "update news_l10n set title='{$title}', content='{$content}', image='{$image}' where news_id='{$id}' and locale='{$locale}'";
                else
                    $sql = "update news_l10n set title='{$title}', content='{$content}' where news_id='{$id}' and locale='{$locale}'";
                if (!db_query($sql))
                    sendCreateNotice($module, $email, '処理中にエラーが発生しました。');

                // Update date
                $sql = "update news set date = '{$date}' where id={$id}";
                if (!db_query($sql))
                    sendCreateNotice($module, $email, '処理中にエラーが発生しました。');

               $message = 'News item has been updated';
            }
            /* edit/edit after update */
            $cmd =  'edit'; //needed for template
            $sql = "select news.date, news_l10n.title, news_l10n.content, news_l10n.image, news.id  from news, news_l10n " .
                    "where news.id={$id} and news.id=news_l10n.news_id and news_l10n.locale='{$locale}'";

            if (!($res = db_query($sql)) || !($row = db_get_rows($res)))
                $news = false;

            if ($news) {
                $date = $row[0][0];
                $title = strip_tags($row[0][1]);
                $content = news_strip_tags($row[0][2]);
                if (($image = $row[0][3]) !== 'NULL' && strlen($image)) {
                    $image = IMAGE_DIR . $image;
                    if (($size = getimagesize(DOC_ROOT . $image))) {
                        $width = $size[0];
                        $height = $size[1];
                    }
                    $display = 'display: inline;';
                } else {
                    $image = '.';
                    $display = 'display: none;';
                }
                $id = $row[0][4];
                $vars = array('%CMD%'=>'update', '%NEWS_ID%'=>$id, '%DATE%'=>$date, '%TITLE%'=>$title, '%CONTENT%'=>$content, '%IMAGE%'=>$image, '%WIDTH%'=>$width, '%HEIGHT%'=>$height,
                    '%DISPLAY%'=>$display, '%MESSAGE%'=>$message, '%WARNING%'=>$warning, '%USER%'=>$email, '%SIGN_OUT_URL%'=>SITE . '/auth/?signout=1');
            }

        } else if ($cmd === 'delete') {
            // show a confirmation page
            $sql = sprintf("select news.date, news_l10n.title, news.id from news, news_l10n " .
                "where news.id=%d and news.id=news_l10n.news_id and news_l10n.locale='%s'",
                $id, sqlite_escape_string($locale));

            if (!($res = db_query($sql)) || !($row = db_get_rows($res)))
                $news = false;

            if ($news) {
                $date = $row[0][0];
                $title = strip_tags($row[0][1]);
                $id = $row[0][2];
                $vars = array('%CMD%'=> 'delete', '%NEWS_ID%' => $id, '%DATE%'    => $date, '%TITLE%'   => $title, 
                    '%CONTENT%' => $content, '%IMAGE%'   => $image, '%DISPLAY%' => $display, '%MESSAGE%' => $message, 
                    '%WARNING%' => $warning, '%USER%'    => $email, '%SIGN_OUT_URL%' => SITE . '/auth/?signout=1');
            }

        } else if ($cmd === 'confirm_delete') {
            // delete the item
            $id = sqlite_escape_string($id);

            $sql = sprintf("select image, title from news_l10n where news_id = %d", $id);
            $res = db_query($sql);
            $row = db_get_row($res);
            $image = (($row[0] !== 'NULL') ? $row[0] : '');

            $sql = sprintf("delete from news_l10n where news_id = %d", $id);
            $sql2 = sprintf("delete from news where id = %d", $id);

            db_query($sql);
            db_query($sql2);

            if ($image && is_writable(DOC_ROOT . IMAGE_DIR . $image))
                unlink(DOC_ROOT . IMAGE_DIR . $image);

            $vars = array('%CMD%'=> 'confirm_delete', '%NEWS_ID%' => $id, '%DATE%'    => $date, '%TITLE%'   => $title, '%CONTENT%' => $content,
                '%IMAGE%'   => $image, '%DISPLAY%' => $display, '%MESSAGE%' => $message, '%WARNING%' => $warning, '%USER%'    => $email,
                '%SIGN_OUT_URL%' => SITE . '/auth/?signout=1');
        } else if ($cmd === 'add') {
            $date = date("Y-m-d");
            $vars = array('%CMD%'=>'create', '%NEWS_ID%'=>'', '%DATE%'=>$date, '%TITLE%'=>'', '%CONTENT%'=>'', '%IMAGE%'=>'.',
                '%DISPLAY%'=>'display: none;', '%MESSAGE%'=>$message, '%WARNING%'=>$warning, '%USER%'=>$email, '%SIGN_OUT_URL%'=>SITE . '/auth/?signout=1');
            $cmd = 'edit';  //use edit template
        }
        require SITE_ROOT . '/include/head.php';
        require SITE_ROOT . '/include/templates/nav.htm';
        echo apply_template(file_get_contents(SITE_ROOT . "/include/templates/admin/{$module}/{$cmd}.html"),  $vars);
        require SITE_ROOT . '/include/foot.php';

    } else if ($cmd === 'create') {

        validateText($module, $email);
        $date = strip_tags(sqlite_escape_string(getRequest('date')));
        $title = strip_tags(sqlite_escape_string(getRequest('title')));
        $content = news_strip_tags(sqlite_escape_string(getRequest('content')));
        $image = 'NULL';

        checkDuplicate($date, $title, $module, $email);

        if (($file = getFileInfo('image')) && ($filename = $file['name'])) {
            if (!($file_error = $file['error'])) {
                if ($file['size'] > MAX_IMAGE_SIZE)
                    sendNotice($module, 'update', $email, array('%WARNING%'=>'イメージサイズがリミットを超えています。' . ' - 2MB'), 'edit');

                $path_parts = pathinfo(basename($filename));
                $ext = strtolower($path_parts['extension']);

                $image = create_unique_key() . '.' . $ext;
                $newfile = DOC_ROOT . IMAGE_DIR . $image;

                handleUpload($module, $email, $file['tmp_name'], $newfile);  //sends warning/exits on error or installs file
                $display = 'display: inline;';
                $size = getimagesize($newfile);
                $width = $size[0];
                $height = $size[1];

            } else if ($file_error == UPLOAD_ERR_INI_SIZE || $file_error == UPLOAD_ERR_FORM_SIZE) { //di php.ini upload_max_filesize 2M
                sendNotice($module, 'create', $email, array('%WARNING%'=>'イメージサイズがリミットを超えています。' . ' - 2MB'), 'edit');
            } else {
                sendNotice($module, 'create', $email, array('%WARNING%'=>'イメージアップロード中にエラーが発生しました。'), 'edit');
            }
        } else {
            $display = 'display: none;';
        }

        $sql = "insert into news (date) values ('{$date}')";
        if (!db_exec($sql)) {
            sendNotice($module, 'create', $email, array('%WARNING%'=>'処理中にエラーが発生しました。恐れ入りますが、カスタマーサービスにご連絡ください。'), 'edit');
        }
        $sql = "select last_insert_rowid()";
        if (!($res = db_query($sql)) || !($row = db_get_row($res))) {
            sendNotice($module, 'create', $email, array('%WARNING%'=>'処理中にエラーが発生しました。恐れ入りますが、カスタマーサービスにご連絡ください。'), 'edit');
        }
        $id = $row[0];
        $sql = "insert into news_l10n (news_id, locale, title, content, image) values ({$id}, '{$locale}', '{$title}', '{$content}', '{$image}')";
        if (!db_exec($sql)) {
            $sql = "delete from news where id={$id}"; //cleanup
            sendNotice($module, 'create', $email, array('%WARNING%'=>'処理中にエラーが発生しました。恐れ入りますが、カスタマーサービスにご連絡ください。'), 'edit');
        }
        $cmd = 'edit';  //return to edit page for review
        $message = '新規追加しました。';
        if ($image !== 'NULL' && strlen($image)) {
            $image = IMAGE_DIR . $image;
            $size = getimagesize($newfile);
            $width = $size[0];
            $height = $size[1];
        } else {
            $image = '.';
            $width = '0';
            $height = '0';
        }
        $vars = array('%CMD%'=>'update', '%NEWS_ID%'=>$id, '%DATE%'=>$date, '%TITLE%'=>$title, '%CONTENT%'=>$content, 
            '%IMAGE%'=>$image, '%WIDTH%'=>$width, '%HEIGHT%'=>$height,
            '%DISPLAY%'=>$display, '%MESSAGE%'=>$message, '%WARNING%'=>$warning, '%USER%'=>$email, '%SIGN_OUT_URL%'=>SITE . '/auth/?signout=1');

        require SITE_ROOT . '/include/head.php';
        require SITE_ROOT . '/include/templates/nav.htm';
        echo apply_template(file_get_contents(SITE_ROOT . "/include/templates/admin/{$module}/{$cmd}.html"),  $vars);
        require SITE_ROOT . '/include/foot.php';

    } else { //($cmd === 'view') 
        $sql = "select news.date, news_l10n.title, news.id from news, news_l10n where news.id=news_l10n.news_id and news_l10n.locale='{$locale}' order by news.date desc";
        if (!($res = db_query($sql)) || !($rows = db_get_rows($res)))
            $news = false;

        $news_list = '';
        if ($news) {
            foreach ($rows as $i=>$row) {
                $date = str_replace('-', '/', $row[0]);
                $title = strip_tags($row[1]);
                $id = $row[2];
                $vars = array('%DATE%'=>$date, '%TITLE%'=>$title, '%ID%'=>$id, '%COUNT%'=>$i);
                $news_list.= apply_template(file_get_contents(SITE_ROOT . "/include/templates/admin/{$module}/{$cmd}-row.htm"),  $vars);
            }
        }
        $vars = array('%USER%'=>$email, '%SIGN_OUT_URL%'=>SITE . '/auth/?signout=1', '%NEWS_LIST%'=>$news_list);
        require SITE_ROOT . '/include/head.php';
        require SITE_ROOT . '/include/templates/nav.htm';
        echo apply_template(file_get_contents(SITE_ROOT . "/include/templates/admin/{$module}/{$cmd}.htm"),  $vars);
        require SITE_ROOT . '/include/foot.php';
    }
}

function sendNotice($module, $cmd, $email, $notice, $template) {
    $vars = resetVars($cmd, $email);
    $vars = array_merge($vars, $notice);
    
    require SITE_ROOT . '/include/head.php';
    require SITE_ROOT . '/include/templates/nav.htm';
    echo apply_template(file_get_contents(SITE_ROOT . "/include/templates/admin/{$module}/{$template}.html"),  $vars);
    require SITE_ROOT . '/include/foot.php';

    exit;
}

function resetVars($cmd, $email) {
    $display = (($image = getRequest('image'))) ? 'display: inline;' : 'display: none';

    //create or update
    return array('%CMD%'=>$cmd, '%NEWS_ID%'=>getRequest('id'), '%DATE%'=>getRequest('date'), '%TITLE%'=>getRequest('title'), '%CONTENT%'=>getRequest('content'),
        '%IMAGE%'=>$image, '%DISPLAY%'=>$display, '%MESSAGE%'=>'', '%WARNING%'=>'', '%USER%'=>$email, '%SIGN_OUT_URL%'=>SITE . '/auth/?signout=1');
}

function handleUpload($module, $email, $tempfile, $newfile) {
    $valid_img_ext = unserialize(IMG_TYPES);
    $jpg_ext = unserialize(JPG_EXT);

    $path_parts = pathinfo(basename($newfile));
    $ext = strtolower($path_parts['extension']);

    if (eregi(implode('|', $valid_img_ext), $ext)) {

        if (move_uploaded_file($tempfile, $newfile)) {

            if (eregi(implode('|', $jpg_ext), $ext)) {
                $output = array();
                exec(IMAGEMAGICK_PATH . 'identify -verbose ' . $newfile . ' | awk \'/Colorspace/ { print $2}\'', $output);
                if (strtolower($output[0]) == "cmyk") {
                    exec(IMAGEMAGICK_PATH . 'mogrify -colorspace RGB ' . $newfile);
                    $message = 'イメージをCMYKからRGBに変換しました。　高画質な画像をお求めの場合はあらかじめイメージを修正してからアップロードしてください。';
                }
            }
            exec(IMAGEMAGICK_PATH . 'mogrify -resize '.IMG_WIDTH_MAX.'x'.IMG_HEIGHT_MAX.'\> ' . $newfile);
        } else {
            sendCreateNotice($module, $email, 'イメージアップロード中にエラーが発生しました。');
        }

    } else {
        sendCreateNotice($module, $email, '無効なファイルタイプです。: ' . $ext);
    }
}

function sendCreateNotice($module, $email, $warning) {
    sendNotice($module, 'create', $email, array('%WARNING%'=>$warning), 'edit');
    exit;
}

//check required fields and limits
function validateText($module, $email) {
    if (!(getRequest('date')))
        sendCreateNotice($module, $email, 'データは入力必須項目です。');
    else if (strlen(getRequest('date')) != DATE_SIZE)
        sendCreateNotice($module, $email, '無効なデーターです。');
    else if (!(getRequest('title')))
        sendCreateNotice($module, $email, 'タイトルは入力必須項目です。');
    else if (strlen(getRequest('title')) > MAX_TITLE)
        sendCreateNotice($module, $email, 'タイトルが最大文字数を超えています。');
    else if (!(getRequest('content')))
        sendCreateNotice($module, $email, 'コンテンツは入力必須項目です。');
    else if (strlen(getRequest('content')) > MAX_CONTENT)
        sendCreateNotice($module, $email, '内容が最大文字数を超えています。');

}

function checkDuplicate($date, $title, $module, $email, $id = NULL) {
    if (is_null($id))
        $sql = "select count(*) from news, news_l10n where news.date='{$date}' and news.id=news_l10n.news_id and news_l10n.title='{$title}'";
    else
        $sql = "select count(*) from news, news_l10n where news.date='{$date}' and news.id != {$id} and news.id=news_l10n.news_id and news_l10n.title='{$title}'";

    if (!($res = db_query($sql)) || !($row = db_get_row($res)))
        sendCreateNotice($module, $email, '処理中にエラーが発生しました。');
    
    if ($row[0] == 1)
        sendCreateNotice($module, $email, "この投稿は既に存在します。 - {$date} : {$title}");
}


?>
