{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_video_category_del_title }}</small></h1>
<hr />
{% include 'components/video/menu_include.tpl' %}
{{ notifytpl.warning(language.admin_components_video_category_del_notify_warning) }}
{% if notify.nomoveto %}
    {{ notifytpl.error(language.admin_components_video_category_del_notify_norecepient) }}
{% endif %}
{% if notify.unpos_delete %}
    {{ notifytpl.error(language.admin_components_video_category_del_notify_nodeletable) }}
{% endif %}
<form class="form-horizontal" method="post">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_components_video_category_del_catsource }}</label>

        <div class="col-lg-9">
            <input type="text" value="{{ cat.name }}[/{{ cat.path }}]" disabled class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_components_video_category_del_moveto }}</label>

        <div class="col-lg-9">
            <select name="move_to_category" class="form-control">
                {% for category in video.categorys %}
                {% if category.id != video.selected_category %}
                    <option value="{{ category.id }}">{{ category.name }}</option>
                {% endif %}
                {% endfor %}
            </select>
        </div>
    </div>
    <input type="submit" name="deletecategory" value="{{ language.admin_components_video_category_del_button_del }}" class="btn btn-danger"/>
</form>