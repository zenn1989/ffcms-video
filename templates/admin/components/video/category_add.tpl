<script>
    function videoCatPosterDelete(video_id) {
        $.get(ffcms_host+'/api.php?iface='+loader+'&object=videoposterdelete&type=1&id='+video_id, function(){
            $('#posterobject').remove();
        });
    }
</script>
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_video_category_edit_title }}</small></h1>
<hr />
{% include 'components/video/menu_include.tpl' %}
{% if notify.owner_notselect %}
    {{ notifytpl.error(language.admin_components_video_category_edit_notify_noowner) }}
{% endif %}
{% if notify.noname %}
    {{ notifytpl.error(language.admin_components_video_category_edit_notify_notitle) }}
{% endif %}
{% if notify.wrongpath %}
    {{ notifytpl.error(language.admin_components_video_category_edit_notify_wrongpath) }}
{% endif %}
<form class="form-horizontal" method="post" role="form" enctype="multipart/form-data">
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_components_video_category_edit_label_owner }}</label>

        <div class="col-lg-9">
            <select name="category_owner" class="form-control">
                {% for category in video.categorys %}
                <option value="{{ category.id }}"{% if category.id == news.selected_category %} selected{% endif %}>{{ category.name }}</option>
                {% endfor %}
            </select>
        </div>
    </div>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in system.languages %}
                <li{% if itemlang == system.lang %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
            {% endfor %}
        </ul>
        <div class="tab-content">
            {% for itemlang in system.languages %}
            <div class="tab-pane fade{% if itemlang == system.lang %} in active{% endif %}" id="{{ itemlang }}">
                <br />
                <div class="form-group">
                    <label class="control-label col-lg-3">{{ language.admin_components_video_category_edit_label_name }}[{{ itemlang }}]</label>

                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="category_name[{{ itemlang }}]" value="{{ cat.name[itemlang] }}" maxlength="100">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">{{ language.admin_components_video_category_edit_label_catdesc_title }}[{{ itemlang }}]</label>

                    <div class="col-lg-9">
                        <textarea name="category_desc[{{ itemlang }}]" class="form-control" maxlength="250">{{ cat.desc[itemlang] }}</textarea>
                        <span class="help-block">{{ language.admin_components_video_category_edit_label_catdesc_desc }}</span>
                    </div>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <hr />
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_components_video_category_edit_label_path }}</label>

        <div class="col-lg-9">
            <input type="text" class="form-control" name="category_path" value="{{ cat.path }}" maxlength="128" placeholder="cats">
        </div>
    </div>
    <div class="form-group" id="posterdiv">
        <label class="control-label col-lg-3">{{ language.admin_components_video_category_edit_label_poster }}</label>

        <div class="col-lg-9">
            {% if cat.poster != null %}
            <p class="alert alert-success" id="posterobject"><i class="fa fa-picture-o"></i> poster_{{ cat.id }}.jpg
                <a href="#posterdiv" data-toggle="modal" data-target="#posterview" class="label label-info" target="_blank">{{ language.admin_components_video_category_edit_poster_view }}</a>
                <a href="#posterdiv" onclick="return videoCatPosterDelete({{ cat.id }});" class="label label-danger">{{ language.admin_components_video_category_edit_poster_del }}</a></p>
            {% endif %}
            <input type="file" name="posterimage">
        </div>
    </div>
    <input type="submit" name="submit" value="{{ language.admin_components_video_category_edit_button_save }}" class="btn btn-success"/>
</form>
{% if cat.poster != null %}
<div class="modal fade" tabindex="-1" role="dialog" id="posterview" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ language.admin_components_video_category_edit_popup_preview }}</h4>
            </div>
            <div class="modal-body">
                <div class="text-center">

                    <script>
                        document.write('<img src="{{ cat.poster }}?rnd='+Math.random()+'" class="img-responsive" />');
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
{% endif %}