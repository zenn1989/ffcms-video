<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\template;
use engine\router;
use engine\system;
use engine\database;
use engine\property;
use engine\language;
use engine\extension;
use engine\meta;
use engine\user;
use engine\cache;

class components_video_front {
    protected static $instance = null;
    const ALLOWED_HTML_TAGS = "<p><a><img><img/><table><tr><td><tbody><thead><th><pre><iframe><span><strong><em><s><blockquote><ul><ol><li><h1><h2><h3><h4><div>";
    const RSS_UPDATE_TIME = 600;
    const RSS_ITEM_LIMIT = 10;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function make() {
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $this->buildVideo());
    }

    private function buildVideo() {
        $content = null;
        $way = $source_way = router::getInstance()->shiftUriArray();
        // get latest object
        $last_object = array_pop($way);
        if($way[0] == "tag" && system::getInstance()->suffixEquals($last_object, '.html')) {
            $content = $this->viewTagList($last_object);
        } elseif($source_way[0] == "feed.rss") {
            $this->viewvideoRssFeed(); //void
        } elseif(system::getInstance()->suffixEquals($last_object, '.html')) { // its a single video
            $content = $this->viewFullVideo($last_object, $way);
        } elseif($way[0] == "categorys") {
            $content = $this->viewListCategory();
        } else { // its a category
            $content = $this->viewCategory();
        }
        return $content;
    }

    private function viewListCategory() {
        $params = array();

        $category_inherit = $this->getCategoryArray();

        foreach($category_inherit as $item) {
            $poster = false;
            if(file_exists(root . '/upload/video/catposter/poster_'.$item['id'].'.jpg'))
                $poster = property::getInstance()->get('script_url') . '/upload/video/catposter/poster_'.$item['id'].'.jpg';
            $params['videocat'][] = array(
                'id' => $item['id'],
                'name' => $item['name'],
                'path' => $item['path'],
                'desc' => $item['desc'],
                'poster' => $poster
            );
        }
        meta::getInstance()->add('title', language::getInstance()->get('video_category_title'));
        $params['video_sort_type'] = 'categorys';
        return template::getInstance()->twigRender('components/video/view_categorys.tpl', $params);
    }

    private function viewFullVideo($url, $categories)
    {
        $stmt = null;
        $category_link = null;
        $category_text = null;
        $link_cat = system::getInstance()->altimplode("/", $categories);
        $time = time();
        $catstmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE path = ?");
        $catstmt->bindParam(1, $link_cat, PDO::PARAM_STR);
        $catstmt->execute();
        if ($catresult = $catstmt->fetch()) {
            $category_link = $catresult['path'];
            $category_serial_text = system::getInstance()->altstripslashes(unserialize($catresult['name']));
            $category_text = $category_serial_text[language::getInstance()->getUseLanguage()];
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE link = ? AND category = ? AND display = 1 AND date <= ?");
            $stmt->bindParam(1, $url, PDO::PARAM_STR);
            $stmt->bindParam(2, $catresult['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(3, $time, PDO::PARAM_INT);
            $stmt->execute();
        }
        if ($stmt != null && $result = $stmt->fetch()) {
            $video_view_id = $result['id'];
            $lang_text = system::getInstance()->altstripslashes(unserialize($result['text']));
            $lang_title = system::getInstance()->altstripslashes(unserialize($result['title']));
            $lang_description = system::getInstance()->altstripslashes(unserialize($result['description']));
            $lang_keywords = system::getInstance()->altstripslashes(unserialize($result['keywords']));
            if(system::getInstance()->length($lang_title[language::getInstance()->getUseLanguage()]) < 1 || system::getInstance()->length($lang_text[language::getInstance()->getUseLanguage()]) < 1)
                return null;
            meta::getInstance()->add('title', $lang_title[language::getInstance()->getUseLanguage()]);
            meta::getInstance()->add('keywords', $lang_keywords[language::getInstance()->getUseLanguage()]);
            meta::getInstance()->add('description', $lang_description[language::getInstance()->getUseLanguage()]);
            $tagPrepareArray = system::getInstance()->altexplode(',', $lang_keywords[language::getInstance()->getUseLanguage()]);
            $tag_array = array();
            foreach($tagPrepareArray as $tagItem) {
                $tag_array[] = trim($tagItem);
            }
            $similar_item_count = extension::getInstance()->getConfig('count_similar_item', 'video', extension::TYPE_COMPONENT, 'int') > 0 ? extension::getInstance()->getConfig('count_similar_item', 'video', extension::TYPE_COMPONENT, 'int') : 5;
            $similar_array = array();
            $search_similar_string = $lang_title[language::getInstance()->getUseLanguage()];
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("SELECT a.*, b.path, MATCH (a.title) AGAINST (? IN BOOLEAN MODE) AS relevance
                                        FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a,
                                        ".property::getInstance()->get('db_prefix')."_com_video_category b
                                        WHERE a.category = b.category_id AND a.id != ? AND a.display = 1
                                        AND MATCH (a.title) AGAINST (? IN BOOLEAN MODE)
                                        ORDER BY relevance LIMIT 0,?");
            $stmt->bindParam(1, $search_similar_string, PDO::PARAM_STR);
            $stmt->bindParam(2, $video_view_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $search_similar_string, PDO::PARAM_STR);
            $stmt->bindParam(4, $similar_item_count, PDO::PARAM_INT);
            $stmt->execute();
            $simBody = null;
            if($stmt->rowCount() > 0) {
                $simRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($simRes as $simRow) {
                    $similar_title = unserialize($simRow['title']);
                    $similar_path = $simRow['path'];
                    $similar_id = $simRow['id'];
                    $similar_full_path = $similar_path == null ? $simRow['link'] : $similar_path . "/" . $simRow['link'];
                    $similar_text_serialize = system::getInstance()->altstripslashes(unserialize($simRow['text']));
                    $similar_text_full = system::getInstance()->nohtml($similar_text_serialize[language::getInstance()->getUseLanguage()]);
                    $similar_text_short = system::getInstance()->sentenceSub(system::getInstance()->altstripslashes($similar_text_full), 70);
                    $similar_poster = false;
                    if(file_exists(root . '/upload/video/poster_' . $similar_id . '.jpg'))
                        $similar_poster = property::getInstance()->get('script_url') . '/upload/video/poster_' . $similar_id . '.jpg';
                    $similar_array[] = array(
                        'link' => $similar_full_path,
                        'title' => $similar_title[language::getInstance()->getUseLanguage()],
                        'preview' => $similar_text_short,
                        'poster' => $similar_poster
                    );
                }
            }

            $vstmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_com_video_entery SET views = views+1 WHERE id = ?");
            $vstmt->bindParam(1, $video_view_id, PDO::PARAM_INT);
            $vstmt->execute();
            $vstmt = null;
            $image_poster_root = root . '/upload/video/poster_' . $video_view_id . '.jpg';
            $image_poster_url = false;
            if(file_exists($image_poster_root)) {
                $image_poster_url = property::getInstance()->get('script_url') . '/upload/video/poster_' . $video_view_id . '.jpg';
            }
            $image_gallery_root = root . '/upload/video/gallery/' . $video_view_id . '/';
            $image_gallery_array = array();
            if(file_exists($image_gallery_root)) {
                foreach(system::getInstance()->altscandir($image_gallery_root . 'orig/') as $image_item) {
                    $file_array = explode(".", $image_item);
                    $file_ext = array_pop($file_array);
                    if(in_array($file_ext, array('jpg', 'gif', 'png', 'bmp', 'jpeg'))) {
                        $image_gallery_array[] = array(
                            'full' => property::getInstance()->get('script_url') . '/upload/video/gallery/' . $video_view_id . '/orig/' . $image_item,
                            'thumb' => property::getInstance()->get('script_url') . '/upload/video/gallery/' . $video_view_id . '/thumb/' . $image_item
                        );
                    }
                }
            }
            $comment_list = extension::getInstance()->call(extension::TYPE_MODULE, 'comments')->buildCommentTemplate();
            $guest_add = extension::getInstance()->getConfig('guest_comment', 'comments', extension::TYPE_MODULE, 'bool');
            $captcha_full = false;
            $captcha_img = null;
            if($guest_add) {
                $captcha_full = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false;
                $captcha_img = extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->show();
            }
            $pathway = router::getInstance()->getUriString();
            $theme_array = array(
                'code' => $result['code'],
                'tags' => $tag_array,
                'title' => $lang_title[language::getInstance()->getUseLanguage()],
                'text' => system::getInstance()->removeCharsFromString('<hr />', $lang_text[language::getInstance()->getUseLanguage()], 1),
                'date' => system::getInstance()->toDate($result['date'], 'h'),
                'unixtime' => $result['date'],
                'category_url' => $category_link,
                'category_name' => $category_text,
                'author_id' => $result['author'],
                'author_nick' => user::getInstance()->get('nick', $result['author']),
                'view_count' => $result['views']+1, // fix update query after select (data is not updater now)
                'similar_items' => $similar_array,
                'pathway' => $pathway,
                'poster' => $image_poster_url
            );
            return template::getInstance()->twigRender('components/video/full_view.tpl', array('local' => $theme_array, 'comments' => $comment_list, 'guest_access' => $guest_add, 'captcha' => array('full' => $captcha_full, 'image' => $captcha_img)));
        }
        return null;
    }

    private function viewTagList($tagname)
    {
        $cleartag = system::getInstance()->nohtml(system::getInstance()->noextention($tagname));
        meta::getInstance()->add('title', $cleartag);
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a, ".property::getInstance()->get('db_prefix')."_com_video_category b WHERE a.category = b.category_id AND a.keywords like ? LIMIT 100");
        $buildSearch = '%'.$cleartag.'%';
        $stmt->bindParam(1, $buildSearch, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount() < 1){
            return null;
        }
        $prepared_array = array('tagname' => $cleartag);
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $video_full_link = null;
            if ($result['path'] == null) {
                $video_full_link = $result['link'];
            } else {
                $video_full_link = $result['path'] . "/" . $result['link'];
            }
            $video_serial_title = unserialize($result['title']);
            $prepared_array['videoinfo'][] = array('link' => $video_full_link, 'title' => $video_serial_title[language::getInstance()->getUseLanguage()]);
        }
        return template::getInstance()->twigRender('components/video/tag_view.tpl', array('local' => $prepared_array));
    }

    private function viewVideoRssFeed() {
        if(!extension::getInstance()->getConfig('enable_rss', 'video', extension::TYPE_COMPONENT, 'bool'))
            return null;
        header('Content-Type: application/rss+xml; charset=utf-8');
        $way = router::getInstance()->shiftUriArray();
        $fulltext_enabled = extension::getInstance()->getConfig('enable_full_rss', 'video', extension::TYPE_COMPONENT, 'bool');
        $fulltext_mod = $way[1] === "fulltext" && $fulltext_enabled ? true : false;
        $cache_filename = null;
        switch($way[1]) {
            case null:
                $cache_filename = 'rssvideofeed_'.language::getInstance()->getUseLanguage();
                break;
            case 'fulltext':
                $cache_filename = 'rssvideofeed_fulltext_'.language::getInstance()->getUseLanguage();
                break;
            case 'short':
                $cache_filename = 'rssvideofeed_short' . language::getInstance()->getUseLanguage();
                break;
            case 'medium':
                $cache_filename = 'rssvideofeed_medium' . language::getInstance()->getUseLanguage();
                break;
        }
        if(cache::getInstance()->get($cache_filename, self::RSS_UPDATE_TIME))
            template::getInstance()->justPrint(cache::getInstance()->get($cache_filename, self::RSS_UPDATE_TIME));
        $params = array();
        $time = time();
        $item_count = self::RSS_ITEM_LIMIT;
        if(extension::getInstance()->getConfig('rss_count', 'video', extension::TYPE_COMPONENT, 'int') > 0)
            $item_count = extension::getInstance()->getConfig('rss_count', 'video', extension::TYPE_COMPONENT, 'int');
        $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.text,a.link,a.date,a.keywords,b.path,b.name FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a,
                                        ".property::getInstance()->get('db_prefix')."_com_video_category b WHERE a.category = b.category_id AND a.date <= ? AND a.display = 1 ORDER BY a.date DESC LIMIT 0,?");
        $stmt->bindParam(1, $time, PDO::PARAM_INT);
        $stmt->bindParam(2, $item_count, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $site_title = property::getInstance()->get('seo_title');
        $site_desc = property::getInstance()->get('seo_description');
        $params['channel'] = array(
            'title' => $site_title[language::getInstance()->getUseLanguage()],
            'desc' => $site_desc[language::getInstance()->getUseLanguage()],
            'link' => property::getInstance()->get('url') . '/video/'
        );
        foreach($result as $row) {
            $item_title = system::getInstance()->altstripslashes(unserialize($row['title']));
            $item_fulltext = system::getInstance()->altstripslashes(unserialize($row['text']));
            $item_langtext = system::getInstance()->stringInline(system::getInstance()->nohtml($item_fulltext[language::getInstance()->getUseLanguage()], true));
            $item_catname = system::getInstance()->altstripslashes(unserialize($row['name']));
            $item_keywords = unserialize($row['keywords']);
            $item_desc = null;
            $item_link = property::getInstance()->get('url') . '/video/';
            if(system::getInstance()->contains('<hr />', $item_fulltext[language::getInstance()->getUseLanguage()])) {
                $item_desc = strstr($item_fulltext[language::getInstance()->getUseLanguage()], '<hr />', true);
                $item_desc = system::getInstance()->stringInline(system::getInstance()->nohtml($item_desc, true));
            } elseif(system::getInstance()->length($item_langtext) > 100) {
                $item_desc = system::getInstance()->sentenceSub($item_langtext, 100) . "...";
            } else {
                $item_desc = $item_langtext;
            }
            $item_desc = system::getInstance()->htmlQuoteDecode($item_desc);
            if($row['path'] == null) {
                $item_link .= $row['link'];
            } else {
                $item_link .= $row['path'] . "/" . $row['link'];
            }
            $item_image = null;
            $image_size = 0;
            if(file_exists(root . '/upload/video/poster_' . $row['id'] . '.jpg')) {
                $item_image = property::getInstance()->get('script_url') . '/upload/video/poster_' . $row['id'] .'.jpg';
                $image_size = filesize(root . '/upload/video/poster_' . $row['id'] . '.jpg');
            }
            $full_text = $fulltext_mod ? system::getInstance()->stringInline(system::getInstance()->htmlQuoteDecode(system::getInstance()->nohtml($item_fulltext[language::getInstance()->getUseLanguage()]))) : null;
            $title = system::getInstance()->htmlQuoteDecode(system::getInstance()->nohtml($item_title[language::getInstance()->getUseLanguage()], true));
            if(extension::getInstance()->getConfig('enable_soc_rss', 'video', extension::TYPE_COMPONENT, 'bol')) {
                $add_hash_lang = extension::getInstance()->getConfig('rss_hash', 'video', extension::TYPE_COMPONENT, 'str');
                $add_hash = $add_hash_lang[language::getInstance()->getUseLanguage()];
                if($way[1] == 'short') { // twitter autopost from RSS - max length 140 chars
                    $reserve_length = 0;
                    $reserve_length += system::getInstance()->length($title);
                    if(extension::getInstance()->getConfig('rss_soc_linkshort', 'video', extension::TYPE_COMPONENT, 'bool')) {
                        $reserve_length += 20;
                    } else {
                        $reserve_length += system::getInstance()->length($item_link) > 25 ? 25 : system::getInstance()->length($item_link); // as test twitter not reserve more than 22 symb. 25 as max.
                    }
                    $used_keys = array();
                    foreach(system::getInstance()->altexplode(',', $item_keywords[language::getInstance()->getUseLanguage()]) as $keyitem) {
                        $keyitem = trim($keyitem);
                        if(system::getInstance()->length($keyitem) + $reserve_length <= 140) { // title.length + keyword.length + space + sharp(#)
                            $title .= " #".$keyitem;
                            $used_keys[] = $keyitem;
                            $reserve_length += system::getInstance()->length($keyitem);
                            $reserve_length += 2; // space + sharp (#key )
                        } else {
                            break;
                        }
                    }
                    foreach(system::getInstance()->altexplode(',', $add_hash) as $add_keyitem) {
                        $add_keyitem = system::getInstance()->altlower(trim($add_keyitem));
                        if(system::getInstance()->length($add_keyitem) + $reserve_length + 2 <= 140 && !in_array($add_keyitem, $used_keys)) {
                            $title .= " #".$add_keyitem;
                            $used_keys[] = $add_keyitem;
                            $reserve_length += system::getInstance()->length($add_keyitem);
                            $reserve_length += 2; // space + sharp (#key )
                        }
                    }
                } elseif($way[1] == 'medium') { // other social from RSS - hash tags in desc and title UPPER
                    $used_keys = array();
                    foreach(system::getInstance()->altexplode(',', $item_keywords[language::getInstance()->getUseLanguage()]) as $keyitem) {
                        $keyitem = trim($keyitem);
                        $item_desc .= " #".$keyitem;
                        $used_keys[] = $keyitem;
                    }
                    foreach(system::getInstance()->altexplode(',', $add_hash) as $add_keyitem) {
                        $add_keyitem = system::getInstance()->altlower(trim($add_keyitem));
                        if(!in_array($add_keyitem, $used_keys)) {
                            $item_desc .= " #".$add_keyitem;
                            $used_keys[] = $add_keyitem;
                        }
                    }
                    $title = system::getInstance()->altupper($title);
                }
            }
            $params['items'][] = array(
                'title' => $title,
                'text' => $full_text,
                'desc' => $item_desc,
                'date' => date(DATE_RSS, $row['date']),
                'link' => $item_link,
                'category' => $item_catname[language::getInstance()->getUseLanguage()],
                'id' => $row['id'],
                'image_url' => $item_image,
                'image_size' => $image_size
            );
        }
        $content = template::getInstance()->twigString()->render(@file_get_contents(root . '/resource/cmscontent/rss_2.xml'), $params);
        cache::getInstance()->store($cache_filename, $content);
        template::getInstance()->justPrint($content);
    }

    public function viewCategory()
    {
        $way = router::getInstance()->shiftUriArray();
        $item_type = 'all';
        if(in_array($way[0], array('all', 'top'))) {
            $item_type = array_shift($way);
        }
        $pop_array = $way;
        $last_item = array_pop($pop_array);
        $page_index = 0;
        $page_video_count = extension::getInstance()->getConfig('count_video_page', 'video', 'components', 'int');
        $total_video_count = 0;
        $cat_link = null;
        if (system::getInstance()->isInt($last_item)) {
            $page_index = $last_item;
            $cat_link = system::getInstance()->altimplode("/", $pop_array);
        } else {
            $cat_link = system::getInstance()->altimplode("/", $way);
        }
        $select_coursor_start = $page_index * $page_video_count;

        $category_select_array = array();
        $category_list = null;
        $fstmt = null;
        $page_title = null;
        $page_desc = null;
        if (extension::getInstance()->getConfig('multi_category', 'video', 'components', 'boolean')) {
            $fstmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE path like ?");
            $path_swarm = "$cat_link%";
            $fstmt->bindParam(1, $path_swarm, PDO::PARAM_STR);
            $fstmt->execute();
        } else {
            $fstmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE path = ?");
            $fstmt->bindParam(1, $cat_link, PDO::PARAM_STR);
            $fstmt->execute();
        }
        while ($fresult = $fstmt->fetch()) {
            $category_select_array[] = $fresult['category_id'];
            if ($cat_link == $fresult['path']) {
                $serial_name = system::getInstance()->nohtml(unserialize($fresult['name']));
                $serial_desc = unserialize($fresult['desc']);
                $page_title = language::getInstance()->get('video_view_category').': ';
                if($item_type == 'top')
                    $page_title .= language::getInstance()->get('vide_view_top');
                else
                    $page_title .= $serial_name[language::getInstance()->getUseLanguage()];
                $seo_title = $page_title;
                $seo_desc = $page_desc = $serial_desc[language::getInstance()->getUseLanguage()];
                if($page_index > 0) {
                    $seo_title .= " - " . language::getInstance()->get('video_page_title') . ' ' . ($page_index+1);
                    $seo_desc .= " - " . language::getInstance()->get('video_page_title') . ' ' . ($page_index+1);
                }
                meta::getInstance()->add('title', $seo_title);
                meta::getInstance()->add('description', $seo_desc);
            }
        }
        $category_list = system::getInstance()->altimplode(',', $category_select_array);
        $theme_array = array();
        $fstmt = null;
        if (system::getInstance()->isIntList($category_list)) {
            $max_preview_length = 150;
            $time = time();
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE category in ($category_list) AND date <= ? AND display = 1");
            $stmt->bindParam(1, $time, PDO::PARAM_INT);
            $stmt->execute();
            if ($countRows = $stmt->fetch()) {
                $total_video_count = $countRows[0];
            }
            $stmt = null;

            $order_column = 'a.date';
            if($item_type == 'top')
                $order_column = 'a.views';

            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a,
												  ".property::getInstance()->get('db_prefix')."_com_video_category b
												  WHERE a.category in ($category_list) AND a.date <= ?
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, {$order_column} DESC LIMIT ?,?");
            $stmt->bindParam(1, $time, PDO::PARAM_INT);
            $stmt->bindParam(2, $select_coursor_start, PDO::PARAM_INT);
            $stmt->bindParam(3, $page_video_count, PDO::PARAM_INT);
            $stmt->execute();
            if (sizeof($category_select_array) > 0) {
                while ($result = $stmt->fetch()) {
                    $lang_text = system::getInstance()->altstripslashes(unserialize($result['text']));
                    $lang_title = system::getInstance()->altstripslashes(unserialize($result['title']));
                    $lang_keywords = system::getInstance()->altstripslashes(unserialize($result['keywords']));
                    $video_short_text = $lang_text[language::getInstance()->getUseLanguage()];
                    if(system::getInstance()->length($lang_title[language::getInstance()->getUseLanguage()]) < 1) // do not add the empty title video
                        continue;
                    if (system::getInstance()->contains('<hr />', $video_short_text)) {
                        $video_short_text = strstr($video_short_text, '<hr />', true);
                    } elseif (system::getInstance()->length($video_short_text) > $max_preview_length) {
                        $video_short_text = system::getInstance()->sentenceSub(system::getInstance()->nohtml($video_short_text), $max_preview_length) . "...";
                    }
                    if ($result['path'] == null) {
                        $video_full_link = $result['link'];
                    } else {
                        $video_full_link = $result['path'] . "/" . $result['link'];
                    }
                    $tagPrepareArray = system::getInstance()->altexplode(',', $lang_keywords[language::getInstance()->getUseLanguage()]);
                    $tag_array = array();
                    foreach($tagPrepareArray as $tagItem) {
                        $tag_array[] = trim($tagItem);
                    }
                    $comment_count = 0;
                    if(is_object(extension::getInstance()->call(extension::TYPE_HOOK, 'comment'))) {
                        $comment_count = extension::getInstance()->call(extension::TYPE_HOOK, 'comment')->getCount('/'.language::getInstance()->getUseLanguage().'/video/'.$video_full_link);
                    }
                    $cat_serial_text = system::getInstance()->altstripslashes(unserialize($result['name']));
                    $video_view_id = $result['id'];
                    $image_poster_root = root . '/upload/video/poster_' . $video_view_id . '.jpg';
                    $image_poster_url = false;
                    if(file_exists($image_poster_root)) {
                        $image_poster_url = property::getInstance()->get('script_url') . '/upload/video/poster_' . $video_view_id . '.jpg';
                    }
                    $theme_array[] = array(
                        'tags' => $tag_array,
                        'title' => $lang_title[language::getInstance()->getUseLanguage()],
                        'text' => $video_short_text,
                        'date' => system::getInstance()->toDate($result['date'], 'h'),
                        'unixtime' => $result['date'],
                        'category_url' => $result['path'],
                        'category_name' => $cat_serial_text[language::getInstance()->getUseLanguage()],
                        'author_id' => $result['author'],
                        'author_nick' => user::getInstance()->get('nick', $result['author']),
                        'full_video_uri' => $video_full_link,
                        'comment_count' => $comment_count,
                        'view_count' => $result['views'],
                        'poster' => $image_poster_url,
                        'important' => $result['important']
                    );
                }
            }
            $stmt = null;
        }
        if($item_type == 'top')
            $page_link = $cat_link == null ? "video/top" : "video/top/" . $cat_link;
        else
            $page_link = $cat_link == null ? "video" : "video/" . $cat_link;
        $pagination = template::getInstance()->showFastPagination($page_index, $page_video_count, $total_video_count, $page_link);
        $full_params = array('local' => $theme_array,
            'pagination' => $pagination,
            'page_title' => $page_title,
            'page_desc' => $page_desc,
            'page_link' => $cat_link,
            'video_sort_type' => $item_type
        );

        return template::getInstance()->twigRender('/components/video/short_view.tpl', $full_params);
    }

    /**
     * Magic and drugs inside (:
     * @return array
     */
    public function getCategoryArray() {
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_category ORDER BY `path` ASC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $work_data = array();
        $total_result = array();
        foreach($result as $item) {
            $work_data[$item['path']] = array(
                'id' => $item['category_id'],
                'name' => $item['name'],
                'desc' => $item['desc']
            );
        }
        ksort($work_data); // sort
        foreach($work_data as $path=>$row) {
            $cname = unserialize($row['name']);
            $cdesc = unserialize($row['desc']);
            $spliter_count = substr_count($path, "/");
            $add = '';
            if ($path != null) {
                for ($i = -1; $i <= $spliter_count; $i++) {
                    $add .= "-";
                }
            } else {
                $add = "-";
            }
            $total_result[] = array(
                'id' => $row['id'],
                'name' => $add . ' ' . $cname[language::getInstance()->getUseLanguage()],
                'desc' => $cdesc[language::getInstance()->getUseLanguage()],
                'path' => $path
            );
        }
        return $total_result;
    }

    public function checkVideoWay($way, $id = 0, $cat_id)
    {
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way) || $way == "tag") {
            return false;
        }
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE link = ? AND category = ? AND id != ?");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->bindParam(2, $cat_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $id, PDO::PARAM_INT);
        $stmt->execute();
        $pRes = $stmt->fetch();
        $stmt = null;
        return $pRes[0] > 0 ? false : true;
    }

    public function checkVideoOwnerExist($owner_id, $video_id) {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE id = ? AND author = ? AND display = 0");
        $stmt->bindParam(1, $video_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $owner_id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res > 0;
    }
}