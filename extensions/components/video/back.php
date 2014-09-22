<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\admin;
use engine\template;
use engine\database;
use engine\property;
use engine\language;
use engine\user;
use engine\extension;
use engine\permission;
use engine\csrf;

class components_video_back {
    protected static $instance = null;

    const ITEM_PER_PAGE = 10;
    const SEARCH_PER_PAGE = 50;

    const FILTER_ALL = 0;
    const FILTER_MODERATE = 1;
    const FILTER_IMPORTANT = 2;
    const FILTER_SEARCH = 3;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.2';
    }

    public function install() {
        $db_tables = "CREATE TABLE IF NOT EXISTS `".property::getInstance()->get('db_prefix')."_com_video_category` (
                      `category_id` int(24) NOT NULL AUTO_INCREMENT,
                      `name` text NOT NULL,
                      `desc` varchar(4096) NOT NULL DEFAULT '',
                      `path` varchar(320) NOT NULL,
                      PRIMARY KEY (`category_id`),
                      UNIQUE KEY `link` (`path`),
                      KEY `id` (`category_id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;
                    INSERT INTO `".property::getInstance()->get('db_prefix')."_com_video_category` (`category_id`, `name`, `desc`, `path`) VALUES
                    (1, 'a:2:{s:2:\"en\";s:4:\"Main\";s:2:\"ru\";s:14:\"Главная\";}', '', '');
                    CREATE TABLE IF NOT EXISTS `".property::getInstance()->get('db_prefix')."_com_video_entery` (
                      `id` int(24) NOT NULL AUTO_INCREMENT,
                      `code` text NOT NULL,
                      `title` varchar(2048) NOT NULL,
                      `text` mediumtext NOT NULL,
                      `link` varchar(256) NOT NULL,
                      `category` int(24) NOT NULL,
                      `date` int(16) NOT NULL,
                      `author` int(24) NOT NULL,
                      `description` varchar(2048) NOT NULL,
                      `keywords` varchar(4096) NOT NULL,
                      `views` int(36) NOT NULL DEFAULT '0',
                      `display` int(2) NOT NULL DEFAULT '1',
                      `important` int(1) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      KEY `id` (`id`),
                      FULLTEXT KEY `title` (`title`,`text`),
                      FULLTEXT KEY `title_2` (`title`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        database::getInstance()->con()->exec($db_tables);
        $lang = array(
            'ru' => array(
                'front' => array(
                    'video_global_title' => 'Видео',
                    'video_view_category' => 'Видео-каталог',
                    'video_view_category_unset' => 'Главная',
                    'vide_view_top' => 'Топ просмотры',
                    'video_page_title' => 'Страница',
                    'video_similar_item_title' => 'Похожие видео:',
                    'video_menutab_all' => 'Все',
                    'video_menutab_top' => 'Популярные',
                    'video_menutab_cats' => 'Категории',
                    'video_view_more' => 'Подробней',
                    'video_view_comments' => 'Комментарии',
                    'video_tagitem_title' => 'Поиск по видео-тегу',
                    'video_category_breadcrumb' => 'Категории видео',
                    'video_category_title' => 'Категории видео-каталога',
                    'video_category_view' => 'Просмотр',
                ),
                'back' => array(
                    'admin_components_video.name' => 'Видео',
                    'admin_components_video.desc' => 'Компонент позволяет реализовать функциональные возможности видео-каталога на вашем сайте.',
                    'admin_components_video_manage_title' => 'Управление видео',
                    'admin_components_video_add_title' => 'Добавить видео',
                    'admin_components_video_categorymanage_title' => 'Управление категориями',
                    'admin_components_video_settings_title' => 'Настройки',
                    'admin_components_video_category_edit_title' => 'Редактирование категории',
                    'admin_components_video_category_edit_label_owner' => 'Категория - родитель',
                    'admin_components_video_category_edit_label_name' => 'Название категории',
                    'admin_components_video_category_edit_label_catdesc_title' => 'Описание категории',
                    'admin_components_video_category_edit_label_catdesc_desc' => 'Описание категории отображаемое на сайте. Допустимо использование HTML.',
                    'admin_components_video_category_edit_label_path' => 'Физический путь',
                    'admin_components_video_category_edit_label_poster' => 'Постер',
                    'admin_components_video_category_edit_poster_view' => 'Смотреть',
                    'admin_components_video_category_edit_poster_del' => 'Удалить',
                    'admin_components_video_category_edit_popup_preview' => 'Просмотр',
                    'admin_components_video_category_edit_button_save' => 'Сохранить',
                    'admin_components_video_category_edit_notify_noowner' => 'Не выбрана категория родитель',
                    'admin_components_video_category_edit_notify_notitle' => 'Не задано имя категории на основном языке',
                    'admin_components_video_category_edit_notify_wrongpath' => 'Путь категории задан не верно или такой путь уже занят',
                    'admin_components_video_category_del_title' => 'Удаление категории',
                    'admin_components_video_category_del_catsource' => 'Категория для удаления',
                    'admin_components_video_category_del_moveto' => 'Переместить видео в',
                    'admin_components_video_category_del_button_del' => 'Удалить категорию',
                    'admin_components_video_category_del_notify_warning' => 'Вы действительно хотите удалить данную категорию? Необходимо выбрать раздел, в который будут перемещены все материалы данной категории.',
                    'admin_components_video_category_del_notify_norecepient' => 'Не выбрана категория для перемещения материалов',
                    'admin_components_video_category_del_notify_nodeletable' => 'Удаление данной категории невозможно!',
                    'admin_components_video_category_list_tab_name' => 'Категория',
                    'admin_components_video_category_list_tab_manage' => 'Управление',
                    'admin_components_video_category_list_alt_title' => 'Список категорий',
                    'admin_components_video_category_list_alt_desc' => 'Вы можете добавить категорию без ранее определенной категории родителя.',
                    'admin_components_video_category_list_alt_button' => 'Добавить раздел',
                    'admin_components_video_delete_item_title' => 'Удаление видео',
                    'admin_components_video_delete_notify_warning' => 'Вы уверены что хотите удалить данный видеоролик?',
                    'admin_components_video_delete_th_name' => 'Заголовок',
                    'admin_components_video_delete_th_path' => 'Путь',
                    'admin_components_video_delete_btn_delete' => 'Удалить',
                    'admin_components_video_delete_btn_cancel' => 'Отмена',
                    'admin_components_video_edit_title' => 'Редактирование видео',
                    'admin_components_video_edit_pathway_title' => 'Физический путь',
                    'admin_components_video_edit_pathway_desc' => 'Содержимое URI по которому будет доступен видеоролик',
                    'admin_components_video_edit_date_title' => 'Дата',
                    'admin_components_video_edit_date_current' => 'Текущая дата',
                    'admin_components_video_edit_date_desc' => 'Дата видео в формате d.m.Y(hh,mm,ss)(день.месяц.год + часы:минуты:секунды)',
                    'admin_components_video_edit_code_title' => 'Код видео',
                    'admin_components_video_edit_code_desc' => 'Укажите в данном поле код видео-ролика полученный с сервиса youtube,vkontakte,vimeo или прочего',
                    'admin_components_video_edit_code_tab_code' => 'Код',
                    'admin_components_video_edit_code_tab_url' => 'Из URL',
                    'admin_components_video_edit_notify_languages' => 'Ниже вы можете заполнить различные языковые версии описания страницы видео - заголовок, текст, параметры описания и ключевых слов.',
                    'admin_components_video_edit_vname_title' => 'Заголовок видео',
                    'admin_components_video_edit_vname_desc' => 'Укажите желаемый заголовок видеоролика, который будет отображен на странице материала.',
                    'admin_components_video_edit_text_title' => 'Описание видеоролика',
                    'admin_components_video_edit_desc_title' => 'Meta описание',
                    'admin_components_video_edit_desc_desc' => 'Параметр описания meta description для страницы видео',
                    'admin_components_video_edit_keywords_title' => 'Meta ключи',
                    'admin_components_video_edit_keywords_desc' => 'Параметр ключевых слов - meta keywords для страницы видео',
                    'admin_components_video_edit_poster_title' => 'Постер видео',
                    'admin_components_video_edit_poster_view' => 'Просмотр',
                    'admin_components_video_edit_poster_del' => 'Удалить',
                    'admin_components_video_edit_poster_desc' => 'Загрузите изображение, которое будет отображаться в качестве постера для видео',
                    'admin_components_video_edit_checkbox_display' => 'Отображать на сайте?',
                    'admin_components_video_edit_checkbox_important' => 'Зафиксировать как важное?',
                    'admin_components_video_edit_category_title' => 'Категория',
                    'admin_components_video_edit_category_desc' => 'Выберите категорию в которую необходимо поместить видеоролик',
                    'admin_components_video_edit_btn_save' => 'Сохранить',
                    'admin_components_video_edit_poster_preview_modal' => 'Просмотр',
                    'admin_components_video_edit_buildcode_title' => 'Извлечение из URL',
                    'admin_components_video_edit_buildcode_desc' => 'С помощью данной формы вы можете сгенирировать код видео ролика из URL. В данный момент поддерживается генерация для youtube.com, vimeo.com',
                    'admin_components_video_edit_buildcode_size' => 'Размер',
                    'admin_components_video_edit_buildcode_service_poster' => 'Постер',
                    'admin_components_video_edit_buildcode_service_save' => 'Открыть и сохранить - автоматический постер из сервиса',
                    'admin_components_video_edit_buildcode_button_work' => 'Обработать',
                    'admin_components_video_edit_buildcode_button_close_modal' => 'Закрыть окно',
                    'admin_components_video_edit_buildcode_notify_success' => 'Код успешно сгенирирован и занесен в поле добавления кода видео ролика.',
                    'admin_components_video_edit_buildcode_notify_fail' => 'Невозможно обработать код видео из данного URL.',
                    'admin_components_video_edit_notify_codeempty' => 'Код видеоролика не указан или указан не верно',
                    'admin_components_video_edit_notify_titleempty' => 'Заголовок видеоролика не задан или задан не корректно',
                    'admin_components_video_edit_notify_catempty' => 'Категория видеоролика не выбрана или выбрана не корректно',
                    'admin_components_video_edit_notify_patherror' => 'Физический путь видео не указан или используются запрещенные символы',
                    'admin_components_video_edit_notify_textempty' => 'Текст описания видео не задан или задан некорректно',
                    'admin_components_video_edit_notify_success' => 'Содержимое видео ролика успешно обновлено',
                    'admin_components_video_list_filter_title' => 'Фильтр',
                    'admin_components_video_list_filter_all' => 'Все видео',
                    'admin_components_video_list_filter_moderate' => 'На модерации',
                    'admin_components_video_list_filter_impotant' => 'Отметка важные',
                    'admin_components_video_list_table_title' => 'Заголовок',
                    'admin_components_video_list_table_link' => 'Ссылка',
                    'admin_components_video_list_table_date' => 'Дата',
                    'admin_components_video_list_table_manage' => 'Управление',
                    'admin_components_video_list_del_selectall' => 'Выбрать все',
                    'admin_components_video_list_del_selected' => 'Удалить выбранные',
                    'admin_components_video_list_empty' => 'Видео ролики еще не добавлены',
                    'admin_components_video_settings_block_main' => 'Основные настройки',
                    'admin_components_video_settings_itempage_title' => 'Видео на странице',
                    'admin_components_video_settings_itempage_desc' => 'Количество превью видео роликов отображаемых на 1 странице',
                    'admin_components_video_settings_multicat_title' => 'Мультикатегории',
                    'admin_components_video_settings_multicat_desc' => 'Отображать видео в разделах родителях дочерней категории',
                    'admin_components_video_settings_similarcount_title' => 'Похожие видео',
                    'admin_components_video_settings_similarcount_desc' => 'Количество элементов в блоке похожие видео на странице',
                    'admin_components_video_settings_block_poster' => 'Размер постера',
                    'admin_components_video_settings_posterdx_title' => 'Ширина постера',
                    'admin_components_video_settings_posterdx_desc' => 'Ширина картинки-постера к видеоролику, отображаемая до загрузки видео',
                    'admin_components_video_settings_posterdy_title' => 'Высота постера',
                    'admin_components_video_settings_posterdy_desc' => 'Высота картинки-постера к видеоролику, отображаемая до загрузки видео',
                    'admin_components_video_settings_block_rss' => 'RSS лента',
                    'admin_components_video_settings_enablerss_title' => 'Включить RSS',
                    'admin_components_video_settings_enablerss_desc' => 'Включить трансляцию RSS 2.0 потока новых видео на /video/feed.rss',
                    'admin_components_video_settings_rsscount_title' => 'Количество видео',
                    'admin_components_video_settings_rsscount_desc' => 'Количество ссылок на видеоролики, отображаемое в RSS ленте',
                    'admin_components_video_settings_enablesoc_title' => 'Социальный',
                    'admin_components_video_settings_enablesoc_desc' => 'Включить поддержку социального RSS потока с поддержкой хэш-тегов?',
                    'admin_components_video_settings_hashtag_title' => 'Дополнительные хэш-теги',
                    'admin_components_video_settings_hashtag_desc' => 'Дополнительные хэш-теги для социальной ленты, будут добавлены если основная длина не исчерпана. Формат: video,joke,picture преобразуется в #video #joke #picture',
                    'admin_components_video_settings_shortlink_title' => 'Сокращение ссылок',
                    'admin_components_video_settings_shortlink_desc' => 'Включить ли учет сокращения ссылок - расчет длины текста, ссылки и тегов.'
                )
            ),
            'en' => array(
                'front' => array(
                    'video_global_title' => 'Video',
                    'video_view_category' => 'Video catalog',
                    'video_view_category_unset' => 'Main',
                    'vide_view_top' => 'Top views',
                    'video_page_title' => 'Page',
                    'video_similar_item_title' => 'Similar videos:',
                    'video_menutab_all' => 'All',
                    'video_menutab_top' => 'Popular',
                    'video_menutab_cats' => 'Categorys',
                    'video_view_more' => 'More',
                    'video_view_comments' => 'Comments',
                    'video_tagitem_title' => 'Video tag search',
                    'video_category_breadcrumb' => 'Video categorys',
                    'video_category_title' => 'Categorys video',
                    'video_category_view' => 'View',
                ),
                'back' => array(
                    'admin_components_video.name' => 'Video',
                    'admin_components_video.desc' => 'This component provide functions to realise video catalog on website',
                    'admin_components_video_manage_title' => 'Video management',
                    'admin_components_video_add_title' => 'Add video',
                    'admin_components_video_categorymanage_title' => 'Category management',
                    'admin_components_video_settings_title' => 'Settings',
                    'admin_components_video_category_edit_title' => 'Edit category',
                    'admin_components_video_category_edit_label_owner' => 'Category owner',
                    'admin_components_video_category_edit_label_name' => 'Category title',
                    'admin_components_video_category_edit_label_catdesc_title' => 'Category description',
                    'admin_components_video_category_edit_label_catdesc_desc' => 'Category description what be displayed on website. Allowed use HTML.',
                    'admin_components_video_category_edit_label_path' => 'URI Path',
                    'admin_components_video_category_edit_label_poster' => 'Poster',
                    'admin_components_video_category_edit_poster_view' => 'View',
                    'admin_components_video_category_edit_poster_del' => 'Remove',
                    'admin_components_video_category_edit_popup_preview' => 'Preview',
                    'admin_components_video_category_edit_button_save' => 'Save',
                    'admin_components_video_category_edit_notify_noowner' => 'Category owner was not selected',
                    'admin_components_video_category_edit_notify_notitle' => 'Category title was not defined',
                    'admin_components_video_category_edit_notify_wrongpath' => 'Category URI path is incorrect',
                    'admin_components_video_category_del_title' => 'Category delete',
                    'admin_components_video_category_del_catsource' => 'Category to delete',
                    'admin_components_video_category_del_moveto' => 'Move items to',
                    'admin_components_video_category_del_button_del' => 'Remove category',
                    'admin_components_video_category_del_notify_warning' => 'Are you sure to delete this category? You must select category where be moved all items.',
                    'admin_components_video_category_del_notify_norecepient' => 'Recipient category to move items not selected',
                    'admin_components_video_category_del_notify_nodeletable' => 'Category delete is unpossible!',
                    'admin_components_video_category_list_tab_name' => 'Category',
                    'admin_components_video_category_list_tab_manage' => 'Management',
                    'admin_components_video_category_list_alt_title' => 'Category list',
                    'admin_components_video_category_list_alt_desc' => 'You can alternative add category without selecting owner.',
                    'admin_components_video_category_list_alt_button' => 'Add category',
                    'admin_components_video_delete_item_title' => 'Remove video',
                    'admin_components_video_delete_notify_warning' => 'Are you sure to delete this video?',
                    'admin_components_video_delete_th_name' => 'Title',
                    'admin_components_video_delete_th_path' => 'Path',
                    'admin_components_video_delete_btn_delete' => 'Remove',
                    'admin_components_video_delete_btn_cancel' => 'Cancel',
                    'admin_components_video_edit_title' => 'Edit video',
                    'admin_components_video_edit_pathway_title' => 'Path URI',
                    'admin_components_video_edit_pathway_desc' => 'URI path to video without base path',
                    'admin_components_video_edit_date_title' => 'Date',
                    'admin_components_video_edit_date_current' => 'Current date',
                    'admin_components_video_edit_date_desc' => 'Video date in format d.m.Y(hh,mm,ss)(day.month.year + hour:min:sec)',
                    'admin_components_video_edit_code_title' => 'Video code',
                    'admin_components_video_edit_code_desc' => 'Put here video code from public service youtube,vkontakte,vimeo or other',
                    'admin_components_video_edit_code_tab_code' => 'Code',
                    'admin_components_video_edit_code_tab_url' => 'Parse from URL',
                    'admin_components_video_edit_notify_languages' => 'Below you can set many language version if video - title, text and other',
                    'admin_components_video_edit_vname_title' => 'Video title',
                    'admin_components_video_edit_vname_desc' => 'Set video title what be displayed on site page',
                    'admin_components_video_edit_text_title' => 'Video description',
                    'admin_components_video_edit_desc_title' => 'Meta description',
                    'admin_components_video_edit_desc_desc' => 'Param of meta description tag for video page',
                    'admin_components_video_edit_keywords_title' => 'Meta keywords',
                    'admin_components_video_edit_keywords_desc' => 'Param of meta keywords for video page',
                    'admin_components_video_edit_poster_title' => 'Video poster',
                    'admin_components_video_edit_poster_view' => 'View',
                    'admin_components_video_edit_poster_del' => 'Remove',
                    'admin_components_video_edit_poster_desc' => 'Upload image what be displayed on page like poster',
                    'admin_components_video_edit_checkbox_display' => 'Display on site?',
                    'admin_components_video_edit_checkbox_important' => 'Set important and stick?',
                    'admin_components_video_edit_category_title' => 'Category',
                    'admin_components_video_edit_category_desc' => 'Select category for this video page',
                    'admin_components_video_edit_btn_save' => 'Save',
                    'admin_components_video_edit_poster_preview_modal' => 'Preview',
                    'admin_components_video_edit_buildcode_title' => 'Extract from URL',
                    'admin_components_video_edit_buildcode_desc' => 'Using this form you can generate video code from video http(s) URL. Support youtube.com, vimeo.com',
                    'admin_components_video_edit_buildcode_size' => 'Size',
                    'admin_components_video_edit_buildcode_service_poster' => 'Poster',
                    'admin_components_video_edit_buildcode_service_save' => 'Open and save poster from service provider',
                    'admin_components_video_edit_buildcode_button_work' => 'Parse',
                    'admin_components_video_edit_buildcode_button_close_modal' => 'Close window',
                    'admin_components_video_edit_buildcode_notify_success' => 'URL are successful parsed and inserted to code area',
                    'admin_components_video_edit_buildcode_notify_fail' => 'Unavailable to parse this URL.',
                    'admin_components_video_edit_notify_codeempty' => 'Video code are empty or incorrect',
                    'admin_components_video_edit_notify_titleempty' => 'Video title is empty or incorrect',
                    'admin_components_video_edit_notify_catempty' => 'Video category is not selected or incorrect',
                    'admin_components_video_edit_notify_patherror' => 'Video path URI is not defined or incorrect',
                    'admin_components_video_edit_notify_textempty' => 'Video text description is empty or incorrect',
                    'admin_components_video_edit_notify_success' => 'Video page was successful updated',
                    'admin_components_video_list_filter_title' => 'Filter',
                    'admin_components_video_list_filter_all' => 'All video',
                    'admin_components_video_list_filter_moderate' => 'On moderate',
                    'admin_components_video_list_filter_impotant' => 'Marker important',
                    'admin_components_video_list_table_title' => 'Title',
                    'admin_components_video_list_table_link' => 'Link',
                    'admin_components_video_list_table_date' => 'Date',
                    'admin_components_video_list_table_manage' => 'Management',
                    'admin_components_video_list_del_selectall' => 'Select all',
                    'admin_components_video_list_del_selected' => 'Delete selected',
                    'admin_components_video_list_empty' => 'Video list is empty',
                    'admin_components_video_settings_block_main' => 'Basic settings',
                    'admin_components_video_settings_itempage_title' => 'Video count',
                    'admin_components_video_settings_itempage_desc' => 'Count of video items on 1 page',
                    'admin_components_video_settings_multicat_title' => 'Multicategory',
                    'admin_components_video_settings_multicat_desc' => 'Display video also in mother categorys in hierarchy',
                    'admin_components_video_settings_similarcount_title' => 'Similar video',
                    'admin_components_video_settings_similarcount_desc' => 'Count of items in similar block',
                    'admin_components_video_settings_block_poster' => 'Poster size',
                    'admin_components_video_settings_posterdx_title' => 'Poster width',
                    'admin_components_video_settings_posterdx_desc' => 'Width of image poster on video page and local storage',
                    'admin_components_video_settings_posterdy_title' => 'Poster height',
                    'admin_components_video_settings_posterdy_desc' => 'Width of image poster on video page and local storage',
                    'admin_components_video_settings_block_rss' => 'RSS feed',
                    'admin_components_video_settings_enablerss_title' => 'Enable RSS',
                    'admin_components_video_settings_enablerss_desc' => 'Enable RSS 2.0 feed for newest video on /video/feed.rss',
                    'admin_components_video_settings_rsscount_title' => 'Rss count',
                    'admin_components_video_settings_rsscount_desc' => 'Count of latest item in rss feed',
                    'admin_components_video_settings_enablesoc_title' => 'Socialize',
                    'admin_components_video_settings_enablesoc_desc' => 'Enable socialize version of RSS feed with hash-tags?',
                    'admin_components_video_settings_hashtag_title' => 'Additional tags',
                    'admin_components_video_settings_hashtag_desc' => 'Additional hash-tags for socialize rss. Will be added if max length is not used fully. Format: video,joke,picture will be parsed to #video #joke #picture',
                    'admin_components_video_settings_shortlink_title' => 'Shorten link',
                    'admin_components_video_settings_shortlink_desc' => 'Enable support of shorten links for social rss(for feedburner socialize and other link shorten service)'
                )
            )
        );
        language::getInstance()->add($lang);
        // refresh configs
        $default_cfgs = 'a:12:{s:16:"count_video_page";s:2:"10";s:14:"enable_useradd";s:1:"0";s:14:"multi_category";s:1:"1";s:11:"enable_tags";s:1:"1";s:18:"count_similar_item";s:1:"4";s:9:"poster_dx";s:3:"480";s:9:"poster_dy";s:3:"360";s:10:"enable_rss";s:1:"1";s:9:"rss_count";s:2:"10";s:14:"enable_soc_rss";s:1:"0";s:8:"rss_hash";a:2:{s:2:"en";s:0:"";s:2:"ru";s:0:"";}s:17:"rss_soc_linkshort";s:1:"0";}';
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET `configs` = ?, `version` = '1.0.1', `compatable` = '2.0.2' WHERE `type` = 'components' AND dir = 'video'");
        $stmt->bindParam(1, $default_cfgs, \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
        // allow comment add
        database::getInstance()->con()->query("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET `path_allow` = CONCAT(`path_allow`, ';video/*') WHERE `type` = 'modules' AND `dir` = 'comments'");
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
            case 'list':
                $content = $this->viewVideoList();
                break;
            case 'edit':
                $content = $this->viewVideoEdit();
                break;
            case 'add':
                $content = $this->viewVideoAdd();
                break;
            case 'delete':
                $content = $this->viewVideoDelete();
                break;
            case 'settings':
                $content = $this->viewVideoSettings();
                break;
            case 'category':
                $content = $this->viewVideoCategory();
                break;
            case 'addcategory':
                $content = $this->viewVideoAddCategory();
                break;
            case 'delcategory':
                $content = $this->viewVideoDelCategory();
                break;
            case 'editcategory':
                $content = $this->viewVideoEditCategory();
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    public function accessData() {
        return array(
            'admin/components/video',
            'admin/components/video/list',
            'admin/components/video/edit',
            'admin/components/video/add',
            'admin/components/video/delete',
            'admin/components/video/settings',
            'admin/components/video/category',
            'admin/components/video/addcategory',
            'admin/components/video/delcategory',
            'admin/components/video/editcategory',
        );
    }

    private function viewVideoEditCategory() {
        $cat_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['video']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->getCategoryArray();

        if(system::getInstance()->post('submit')) {
            $cat_name = system::getInstance()->nohtml(system::getInstance()->post('category_name'));
            $cat_desc = system::getInstance()->post('category_desc');
            $cat_path = system::getInstance()->nohtml(system::getInstance()->post('category_path'));
            $owner_cat_id = (int)system::getInstance()->post('category_owner');
            $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE category_id = ?");
            $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
            $stmt->execute();
            $resCat = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = null;
            $old_path = $resCat['path'];
            if(!system::getInstance()->isInt($cat_id) || $cat_id < 1) {
                $params['notify']['owner_notselect'] = true;
            }
            if(strlen($cat_name[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['noname'] = true;
            }
            if($cat_id != 1 && $cat_id != $owner_cat_id && $old_path != $cat_path && !system::getInstance()->suffixEquals($old_path, $cat_path)) { // its not a general category?
                if (!$this->checkCategoryWay($cat_path, $owner_cat_id, $cat_id)) {
                    $params['notify']['wrongpath'] = true;
                }
            }
            if(sizeof($params['notify']) == 0) {
                $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE category_id = ?");
                $stmt->bindParam(1, $owner_cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $resMother = $stmt->fetch();
                $new_category_path = null;
                if ($resMother['path'] == null) {
                    $new_category_path = $cat_path;
                } else {
                    $new_category_path = $resMother['path'] . "/" . $cat_path;
                }
                $serial_name = serialize(system::getInstance()->altaddslashes($cat_name));
                $serial_desc = serialize(system::getInstance()->altaddslashes($cat_desc));
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_video_category SET `path` = ?, `name` = ?, `desc` = ? WHERE `category_id` = ?");
                $stmt->bindParam(1, $new_category_path, PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_name, PDO::PARAM_STR);
                $stmt->bindParam(3, $serial_desc, PDO::PARAM_STR);
                $stmt->bindParam(4, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                if($_FILES['posterimage']['size'] > 0) {
                    $save_name = 'poster_' . $cat_id . '.jpg';
                    $dx = extension::getInstance()->getConfig('poster_dx', 'video', extension::TYPE_COMPONENT, 'int');
                    $dy = extension::getInstance()->getConfig('poster_dy', 'video', extension::TYPE_COMPONENT, 'int');
                    extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/video/catposter/', $_FILES['posterimage'], $dx, $dy, $save_name);
                }
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=video&make=category');
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE category_id = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if($res = $stmt->fetch()) {
            $stmt = null;
            $path_array = system::getInstance()->altexplode('/', $res['path']);
            $last_path_name = array_pop($path_array);
            $owner_path = system::getInstance()->altimplode('/', $path_array);
            $poster = null;
            if(file_exists(root . '/upload/video/catposter/poster_' . $cat_id . '.jpg'))
                $poster = property::getInstance()->get('script_url') . '/upload/video/catposter/poster_' . $cat_id . '.jpg';
            $params['cat'] = array(
                'id' => $cat_id,
                'name' => unserialize($res['name']),
                'desc' => unserialize($res['desc']),
                'path' => $last_path_name,
                'poster' => $poster
            );
            $stmt = database::getInstance()->con()->prepare("SELECT category_id FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE path = ?");
            $stmt->bindParam(1, $owner_path, PDO::PARAM_STR);
            $stmt->execute();
            if($resOwner = $stmt->fetch()) {
                $params['video']['selected_category'] = $resOwner['category_id'];
            }
            $stmt = null;
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=video&make=category');
        }


        return template::getInstance()->twigRender('components/video/category_add.tpl', $params);
    }

    private function viewVideoDelCategory() {
        csrf::getInstance()->buildToken();
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['video']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->getCategoryArray();

        $cat_id = (int)system::getInstance()->get('id');

        $params['video']['selected_category'] = $cat_id;

        if($cat_id < 1)
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=video&make=category");

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM " . property::getInstance()->get('db_prefix') . "_com_video_category WHERE category_id = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($res = $stmt->fetch()) {
            $cat_serial_name = unserialize($res['name']);
            $params['cat']['name'] = $cat_serial_name[property::getInstance()->get('lang')];
            $params['cat']['path'] = $res['path'];
        }
        $stmt = null;
        if($params['cat']['path'] != null) {
            $notify = null;
            if (system::getInstance()->post('deletecategory') && csrf::getInstance()->check()) {
                $move_to_cat = (int)system::getInstance()->post('move_to_category');
                if($move_to_cat < 1) {
                    $params['notify']['nomoveto'] = true;
                } else {
                    $like_path = $params['cat']['path'] . "%";
                    $stmt = database::getInstance()->con()->prepare("SELECT category_id FROM " . property::getInstance()->get('db_prefix') . "_com_video_category WHERE path like ?");
                    $stmt->bindParam(1, $like_path, PDO::PARAM_STR);
                    $stmt->execute();
                    $cat_to_remove_array = array();
                    while ($result = $stmt->fetch()) {
                        $cat_to_remove_array[] = $result['category_id'];
                    }
                    $stmt = null;
                    $cat_remove_list = system::getInstance()->altimplode(',', $cat_to_remove_array); // is safefull, cuz id's defined in db like INT with autoincrement
                    $stmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_com_video_entery SET category = ? WHERE category in({$cat_remove_list})");
                    $stmt->bindParam(1, $move_to_cat, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $stmt = database::getInstance()->con()->prepare("DELETE FROM " . property::getInstance()->get('db_prefix') . "_com_video_category WHERE category_id in ({$cat_remove_list})");
                    $stmt->execute();
                    $stmt = null;
                    @unlink(root . '/upload/video/catposter/poster_' . $cat_id . '.jpg');
                    system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=video&make=category");;
                }
            }
        } else {
            $params['notify']['unpos_delete'] = true;
        }
        return template::getInstance()->twigRender('components/video/category_del.tpl', $params);
    }

    private function viewVideoAddCategory() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');
        $params['video']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->getCategoryArray();
        $params['video']['selected_category'] = (int)system::getInstance()->get('id');

        if (system::getInstance()->post('submit')) {
            $cat_id = system::getInstance()->post('category_owner');
            $cat_name = system::getInstance()->nohtml(system::getInstance()->post('category_name'));
            $cat_desc = system::getInstance()->post('category_desc');
            $cat_serial_name = serialize(system::getInstance()->altaddslashes($cat_name));
            $cat_serial_desc = serialize(system::getInstance()->altaddslashes($cat_desc));
            $cat_path = system::getInstance()->post('category_path');

            if(!system::getInstance()->isInt($cat_id) || $cat_id < 1) {
                $params['notify']['owner_notselect'] = true;
            }
            if(strlen($cat_name[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['noname'] = true;
            }
            if (!$this->checkCategoryWay($cat_path, $cat_id)) {
                $params['notify']['wrongpath'] = true;
            }
            if (sizeof($params['notify']) == 0) {
                $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE category_id  = ?");
                $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                if ($res = $stmt->fetch()) {
                    $new_category_path = null;
                    if ($res['path'] == null) {
                        $new_category_path = $cat_path;
                    } else {
                        $new_category_path = $res['path'] . "/" . $cat_path;
                    }
                    $stmt = null;

                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_video_category (`name`, `desc`, `path`) VALUES (?, ?, ?)");
                    $stmt->bindParam(1, $cat_serial_name, PDO::PARAM_STR);
                    $stmt->bindParam(2, $cat_serial_desc, PDO::PARAM_STR);
                    $stmt->bindParam(3, $new_category_path, PDO::PARAM_STR);
                    $stmt->execute();
                    if($_FILES['posterimage']['size'] > 0) {
                        $video_cat_id = database::getInstance()->con()->lastInsertId();
                        $save_name = 'poster_' . $video_cat_id . '.jpg';
                        $dx = extension::getInstance()->getConfig('poster_dx', 'video', extension::TYPE_COMPONENT, 'int');
                        $dy = extension::getInstance()->getConfig('poster_dy', 'video', extension::TYPE_COMPONENT, 'int');
                        extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/video/catposter/', $_FILES['posterimage'], $dx, $dy, $save_name);
                    }
                    system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=video&make=category");
                }
            }
        }

        return template::getInstance()->twigRender('components/video/category_add.tpl', $params);
    }

    private function viewVideoCategory() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['video']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->getCategoryArray();

        return template::getInstance()->twigRender('components/video/category_list.tpl', $params);
    }

    private function viewVideoSettings() {
        csrf::getInstance()->buildToken();
        $params = array();
        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs() && csrf::getInstance()->check()) {
                $params['notify']['save_success'] = true;
            }
        }
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['config']['count_video_page'] = extension::getInstance()->getConfig('count_video_page', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['multi_category'] = extension::getInstance()->getConfig('multi_category', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['count_similar_item'] = extension::getInstance()->getConfig('count_similar_item', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_tags'] = extension::getInstance()->getConfig('enable_tags', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['poster_dx'] = extension::getInstance()->getConfig('poster_dx', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['poster_dy'] = extension::getInstance()->getConfig('poster_dy', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_rss'] = extension::getInstance()->getConfig('enable_rss', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['rss_count'] = extension::getInstance()->getConfig('rss_count', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_full_rss'] = extension::getInstance()->getConfig('enable_full_rss', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_soc_rss'] = extension::getInstance()->getConfig('enable_soc_rss', 'video', extension::TYPE_COMPONENT, 'int');
        $params['config']['rss_hash'] = extension::getInstance()->getConfig('rss_hash', 'video', extension::TYPE_COMPONENT, 'str');
        $params['config']['rss_soc_linkshort'] = extension::getInstance()->getConfig('rss_soc_linkshort', 'video', extension::TYPE_COMPONENT, 'int');

        return template::getInstance()->twigRender('components/video/settings.tpl', $params);
    }

    private function viewVideoDelete() {
        csrf::getInstance()->buildToken();
        $video_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE id = ?");
            $stmt->bindParam(1, $video_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE object_type = 'video' AND object_id = ?");
            $stmt->bindParam(1, $video_id, PDO::PARAM_INT);
            $stmt->execute();
            // delete image poster and gallery images
            if(file_exists(root . '/upload/video/poster_' . $video_id . '.jpg'))
                @unlink(root . '/upload/video/poster_' . $video_id . '.jpg');
            if(file_exists(root . '/upload/video/gallery/' . $video_id . '/'))
                system::getInstance()->removeDirectory(root . '/upload/video/gallery/' . $video_id . '/');
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=video");
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE id = ?");
        $stmt->bindParam(1, $video_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $title = unserialize($result['title']);

            $params['video'] = array(
                'id' => $video_id,
                'title' => $title[language::getInstance()->getUseLanguage()],
                'pathway' => $result['link']
            );
        }

        return template::getInstance()->twigRender('components/video/delete.tpl', $params);
    }

    private function viewVideoAdd() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');
        $params['video']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->getCategoryArray();
        $params['video']['id'] = $this->searchFreeId(); // for jquery ajax gallery images
        $params['video']['action_add'] = true;
        if(system::getInstance()->post('save')) {
            $editor_id = user::getInstance()->get('id');
            $params['video']['title'] = system::getInstance()->nohtml(system::getInstance()->post('title'));
            $params['video']['cat_id'] = system::getInstance()->post('category');
            $params['video']['pathway'] = system::getInstance()->nohtml(system::getInstance()->post('pathway'));
            $pathway = $params['video']['pathway'] . ".html";
            $params['video']['display'] = system::getInstance()->post('display_content') == "on" ? 1 : 0;
            $params['video']['important'] = system::getInstance()->post('important_content') == "on" ? 1 : 0;
            $params['video']['text'] = system::getInstance()->post('text');
            $params['video']['description'] = system::getInstance()->nohtml(system::getInstance()->post('description'));
            $params['video']['keywords'] = system::getInstance()->nohtml(system::getInstance()->post('keywords'));
            $params['video']['code'] = system::getInstance()->post('videocode');
            $date = system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            if(system::getInstance()->post('current_date') == "on" || $date < 1)
                $date = time();
            $params['video']['date'] = system::getInstance()->toDate($date, 'h');
            if(strlen($params['video']['code']) < 1)
                $params['notify']['nocode'] = true;
            if (strlen($params['video']['title'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notitle'] = true;
            }
            if (!system::getInstance()->isInt($params['video']['cat_id'])) {
                $params['notify']['nocat'] = true;
            }
            if (strlen($pathway) < 1 || !extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->checkVideoWay($pathway, 0, $params['video']['cat_id'])) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($params['video']['text'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if (sizeof($params['notify']) == 0) {
                $serial_title = serialize(system::getInstance()->altaddslashes($params['video']['title']));
                $serial_text = serialize(system::getInstance()->altaddslashes($params['video']['text']));
                $serial_description = serialize(system::getInstance()->altaddslashes($params['video']['description']));
                $serial_keywords = serialize(system::getInstance()->altaddslashes($params['video']['keywords']));
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_video_entery
					(`code`, `title`, `text`, `link`, `category`, `date`, `author`, `description`, `keywords`, `display`, `important`) VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $params['video']['code'], PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_title, PDO::PARAM_STR);
                $stmt->bindParam(3, $serial_text, PDO::PARAM_STR);
                $stmt->bindParam(4, $pathway, PDO::PARAM_STR);
                $stmt->bindParam(5, $params['video']['cat_id'], PDO::PARAM_INT);
                $stmt->bindParam(6, $date, PDO::PARAM_STR);
                $stmt->bindParam(7, $editor_id, PDO::PARAM_INT);
                $stmt->bindParam(8, $serial_description, PDO::PARAM_STR);
                $stmt->bindParam(9, $serial_keywords, PDO::PARAM_STR);
                $stmt->bindParam(10, $params['video']['display'], PDO::PARAM_INT);
                $stmt->bindParam(11, $params['video']['important'], PDO::PARAM_INT);
                $stmt->execute();
                $new_video_id = database::getInstance()->con()->lastInsertId();
                $stmt = null;
                foreach($params['video']['keywords'] as $keyrow) {
                    $keyrow_array = system::getInstance()->altexplode(',', $keyrow);
                    foreach($keyrow_array as $objectkey) {
                        $objectkey = system::getInstance()->altlower(trim($objectkey));
                        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'video', ?)");
                        $stmt->bindParam(1, $new_video_id, PDO::PARAM_INT);
                        $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = null;
                    }
                }
                // image poster for video
                if($_FILES['videoimage']['size'] > 0) {
                    $save_name = 'poster_' . $new_video_id . '.jpg';
                    $dx = extension::getInstance()->getConfig('poster_dx', 'video', extension::TYPE_COMPONENT, 'int');
                    $dy = extension::getInstance()->getConfig('poster_dy', 'video', extension::TYPE_COMPONENT, 'int');
                    extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/video/', $_FILES['videoimage'], $dx, $dy, $save_name);
                }
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=video");
            }
        }
        return template::getInstance()->twigRender('components/video/edit.tpl', $params);
    }

    private function viewVideoEdit() {
        $params = array();
        $video_id = (int)system::getInstance()->get('id');
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');
        $params['video']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->getCategoryArray();

        if(system::getInstance()->post('save')) {
            $editor_id = user::getInstance()->get('id');
            $title = system::getInstance()->nohtml(system::getInstance()->post('title'));
            $category_id = system::getInstance()->post('category');
            $pathway = system::getInstance()->nohtml(system::getInstance()->post('pathway')) . ".html";
            $display = system::getInstance()->post('display_content') == "on" ? 1 : 0;
            $important = system::getInstance()->post('important_content') == "on" ? 1 : 0;
            $text = system::getInstance()->post('text');
            $description = system::getInstance()->nohtml(system::getInstance()->post('description'));
            $keywords = system::getInstance()->nohtml(system::getInstance()->post('keywords'));
            $video_code = system::getInstance()->post('videocode');
            $date = system::getInstance()->post('current_date') == "on" ? time() : system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            if(strlen($video_code) < 1)
                $params['notify']['nocode'] = true;
            if (strlen($title[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notitle'] = true;
            }
            if (!system::getInstance()->isInt($category_id)) {
                $params['notify']['nocat'] = true;
            }
            if (strlen($pathway) < 1 || !extension::getInstance()->call(extension::TYPE_COMPONENT, 'video')->checkVideoWay($pathway, $video_id, $category_id)) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($text[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if(sizeof($params['notify']) == 0) {
                $serial_title = serialize(system::getInstance()->altaddslashes($title));
                $serial_text = serialize(system::getInstance()->altaddslashes($text));
                $serial_description = serialize(system::getInstance()->altaddslashes($description));
                $serial_keywords = serialize(system::getInstance()->altaddslashes($keywords));
                $stmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_com_video_entery SET code = ?, title = ?, text = ?, link = ?,
						category = ?, date = ?, description = ?, keywords = ?, display = ?, important = ? WHERE id = ?");
                $stmt->bindParam(1, $video_code, PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_title, PDO::PARAM_STR);
                $stmt->bindParam(3, $serial_text, PDO::PARAM_STR);
                $stmt->bindParam(4, $pathway, PDO::PARAM_STR);
                $stmt->bindParam(5, $category_id, PDO::PARAM_INT);
                $stmt->bindParam(6, $date, PDO::PARAM_INT);
                $stmt->bindParam(7, $serial_description, PDO::PARAM_STR);
                $stmt->bindParam(8, $serial_keywords, PDO::PARAM_STR);
                $stmt->bindParam(9, $display, PDO::PARAM_INT);
                $stmt->bindParam(10, $important, PDO::PARAM_INT);
                $stmt->bindParam(11, $video_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE `object_type` = 'video' AND `object_id` = ?");
                $stmt->bindParam(1, $video_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                foreach($keywords as $keyrow) {
                    $keyrow_array = system::getInstance()->altexplode(',', $keyrow);
                    foreach($keyrow_array as $objectkey) {
                        $objectkey = system::getInstance()->altlower(trim($objectkey));
                        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'video', ?)");
                        $stmt->bindParam(1, $video_id, PDO::PARAM_INT);
                        $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = null;
                    }
                }
                $params['notify']['success'] = true;
                if($_FILES['videoimage']['size'] > 0) {
                    $dx = extension::getInstance()->getConfig('poster_dx', 'video', extension::TYPE_COMPONENT, 'int');
                    $dy = extension::getInstance()->getConfig('poster_dy', 'video', extension::TYPE_COMPONENT, 'int');
                    $save_name = 'poster_' . $video_id . '.jpg';
                    extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/video/', $_FILES['videoimage'], $dx, $dy, $save_name);
                }
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE id = ?");
        $stmt->bindParam(1, $video_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $params['video']['id'] = $video_id;
            $params['video']['title'] = system::getInstance()->altstripslashes(unserialize($result['title']));
            $params['video']['text'] = system::getInstance()->altstripslashes(unserialize($result['text']));
            $params['video']['pathway'] = system::getInstance()->noextention($result['link']);
            $params['video']['cat_id'] = $result['category'];
            $params['video']['date'] = system::getInstance()->toDate($result['date'], 'h');
            $params['video']['description'] = system::getInstance()->altstripslashes(unserialize($result['description']));
            $params['video']['keywords'] = system::getInstance()->altstripslashes(unserialize($result['keywords']));
            $params['video']['display'] = $result['display'];
            $params['video']['important'] = $result['important'];
            $params['video']['code'] = $result['code'];
            if(file_exists(root . '/upload/video/poster_' . $video_id . '.jpg')) {
                $params['video']['poster_path'] = '/upload/video/poster_' . $video_id . '.jpg';
                $params['video']['poster_name'] = 'poster_' . $video_id . '.jpg';
            }
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=static');
        }


        return template::getInstance()->twigRender('components/video/edit.tpl', $params);
    }

    private function viewVideoList() {
        csrf::getInstance()->buildToken();
        $params = array();

        if(system::getInstance()->post('deleteSelected') && csrf::getInstance()->check()) {
            if(permission::getInstance()->have('global/owner') || permission::getInstance()->have('admin/components/video/delete')) {
                $toDelete = system::getInstance()->post('check_array');
                if(is_array($toDelete) && sizeof($toDelete) > 0) {
                    foreach($toDelete as $video_single_id) { // remove posible poster files and gallery images
                        if(file_exists(root . '/upload/video/poster_' . $video_single_id . '.jpg'))
                            @unlink(root . '/upload/video/poster_' . $video_single_id . '.jpg');
                        if(file_exists(root . '/upload/video/gallery/' . $video_single_id . '/'))
                            system::getInstance()->removeDirectory(root . '/upload/video/gallery/' . $video_single_id . '/');
                    }
                    $listDelete = system::getInstance()->altimplode(',', $toDelete);
                    if(system::getInstance()->isIntList($listDelete)) {
                        database::getInstance()->con()->query("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE id IN (".$listDelete.")");
                        // drop tags
                        database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE object_type = 'video' AND object_id IN (".$listDelete.")");
                    }
                }
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['search']['value'] = system::getInstance()->nohtml(system::getInstance()->get('search'));
        $index_start = (int)system::getInstance()->get('index');
        $db_index = $index_start * self::ITEM_PER_PAGE;
        $stmt = null;
        $filter = (int)system::getInstance()->get('filter');
        if($filter === self::FILTER_MODERATE) { // 1
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,a.date,b.category_id,a.important,a.display,b.path FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a, ".
                property::getInstance()->get('db_prefix')."_com_video_category b WHERE a.category = b.category_id AND a.display = 0 ORDER BY a.id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        } elseif($filter === self::FILTER_IMPORTANT) { // 2
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,a.date,a.important,a.display,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a, ".
                property::getInstance()->get('db_prefix')."_com_video_category b WHERE a.category = b.category_id AND a.important = 1 ORDER BY a.id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        } elseif($filter === self::FILTER_SEARCH) { // 3
            $search_string = "%".$params['search']['value']."%";
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,a.date,a.important,a.display,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a, ".
                property::getInstance()->get('db_prefix')."_com_video_category b WHERE a.category = b.category_id AND (a.title like ? OR a.text like ?) ORDER BY a.id DESC LIMIT 0,".self::SEARCH_PER_PAGE);
            $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
            $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
            $stmt->execute();
        } else { // 0 || > 3
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,a.date,b.category_id,a.important,a.display,b.path FROM ".property::getInstance()->get('db_prefix')."_com_video_entery a, ".
                property::getInstance()->get('db_prefix')."_com_video_category b WHERE a.category = b.category_id ORDER BY a.important DESC, a.id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
            $filter = 0;
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $data) {
            $title = unserialize($data['title']);
            $link = $data['path'];
            if($link != null)
                $link .= "/";
            $link .= $data['link'];
            $params['video'][] = array(
                'id' => $data['id'],
                'title' => $title[language::getInstance()->getUseLanguage()],
                'link' => $link,
                'date' => system::getInstance()->toDate($data['date'], 'h'),
                'important' => (int)$data['important'],
                'moderate' => !(bool)$data['display'] // in db 0 = hide, 1 = show
            );
        }
        $params['pagination'] = template::getInstance()->showFastPagination($index_start, self::ITEM_PER_PAGE, $this->getTotalVideoCount($filter), '?object=components&action=video&filter='.$filter.'&index=');

        return template::getInstance()->twigRender('components/video/list.tpl', $params);
    }

    private function checkCategoryWay($way, $cat_id)
    {
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way) || system::getInstance()->length($way) < 1) {
            return false;
        }
        $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE category_id  = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($result = $stmt->fetch()) {
            $stmt = null;
            $mother_path = $result['path'];
            $new_path_query = $result['path'] == null ? $way . "%" : $mother_path . "/" . $way . "%";
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_video_category WHERE path like ?");
            $stmt->bindParam(1, $new_path_query, PDO::PARAM_STR);
            $stmt->execute();
            if($res = $stmt->fetch()) {
                return $res[0] == 0 ? true : false;
            }
        }
        return false;
    }

    public function getTotalVideoCount($filter = 0) {
        $query = null;
        switch($filter) {
            case 1:
                $query = "SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE display = 0";
                break;
            case 2:
                $query = "SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_video_entery WHERE important = 1";
                break;
            default:
                $query = "SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_video_entery";
                break;
        }
        $stmt = database::getInstance()->con()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }

    public function searchFreeId($i = 10) {
        $video_id = system::getInstance()->randomInt($i);
        $folder = root . '/upload/video/gallery/' . $video_id . '/';
        if(file_exists($folder)) {
            return $this->searchFreeId(++$i);
        }
        return $video_id;
    }
}