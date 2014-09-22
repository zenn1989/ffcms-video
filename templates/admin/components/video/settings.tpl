{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_video_settings_title }}</small></h1>
<hr />
{% include 'components/video/menu_include.tpl' %}
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
    <h2>{{ language.admin_components_video_settings_block_main }}</h2>
    <hr />
        {{ settingstpl.textgroup('count_video_page', config.count_video_page, language.admin_components_video_settings_itempage_title, language.admin_components_video_settings_itempage_desc) }}
        {{ settingstpl.selectYNgroup('multi_category', config.multi_category, language.admin_components_video_settings_multicat_title, language.admin_components_video_settings_multicat_desc, _context) }}
        {{ settingstpl.textgroup('count_similar_item', config.count_similar_item, language.admin_components_video_settings_similarcount_title, language.admin_components_video_settings_similarcount_desc) }}
    <h2>{{ language.admin_components_video_settings_block_poster }}</h2>
        {{ settingstpl.textgroup('poster_dx', config.poster_dx, language.admin_components_video_settings_posterdx_title, language.admin_components_video_settings_posterdx_desc) }}
        {{ settingstpl.textgroup('poster_dy', config.poster_dy, language.admin_components_video_settings_posterdy_title, language.admin_components_video_settings_posterdy_desc) }}
    <h2>{{ language.admin_components_video_settings_block_rss }}</h2>
        {{ settingstpl.selectYNgroup('enable_rss', config.enable_rss, language.admin_components_video_settings_enablerss_title, language.admin_components_video_settings_enablerss_desc, _context) }}
        {{ settingstpl.textgroup('rss_count', config.rss_count, language.admin_components_video_settings_rsscount_title, language.admin_components_video_settings_rsscount_desc) }}
        {{ settingstpl.selectYNgroup('enable_soc_rss', config.enable_soc_rss, language.admin_components_video_settings_enablesoc_title, language.admin_components_video_settings_enablesoc_desc, _context) }}
        {{ settingstpl.languagegroup('rss_hash', config.rss_hash, language.admin_components_video_settings_hashtag_title, language.admin_components_video_settings_hashtag_desc, _context) }}
        {{ settingstpl.selectYNgroup('rss_soc_linkshort', config.rss_soc_linkshort, language.admin_components_video_settings_shortlink_title, language.admin_components_video_settings_shortlink_desc, _context) }}

    <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>